<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Catalogue planning only: no product setters, saves, stock writes or media downloads. */
class SSZ_Loyverse_Preview {
    public function run( SSZ_Loyverse_Client $client, $store_id ) {
        if ( ! class_exists( 'WooCommerce' ) || ! $store_id ) {
            return new WP_Error( 'preview_settings', 'Activate WooCommerce and save a Loyverse store first.' );
        }
        $stores = $client->get_stores();
        if ( is_wp_error( $stores ) ) { return $stores; }
        $store_name = '';
        foreach ( $stores as $store ) {
            if ( ( $store['id'] ?? '' ) === $store_id ) { $store_name = $store['name'] ?? $store_id; }
        }
        if ( '' === $store_name ) { return new WP_Error( 'preview_store', 'The selected store no longer exists. Refresh the connection and choose a valid store.' ); }
        $items = $client->get_all_items();
        if ( is_wp_error( $items ) ) { return $items; }
        $inventory = $client->get_inventory_for_store( $store_id );
        if ( is_wp_error( $inventory ) ) { return $inventory; }
        $existing = array( 'skus' => array(), 'items' => array(), 'variants' => array() );
        $ids = get_posts( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => array( 'publish', 'private', 'draft', 'pending', 'future', 'trash' ), 'posts_per_page' => -1, 'fields' => 'ids' ) );
        foreach ( $ids as $id ) {
            $product = wc_get_product( $id );
            if ( ! $product ) { continue; }
            foreach ( array( 'skus' => $product->get_sku(), 'items' => $product->get_meta( '_ssz_loyverse_item_id' ), 'variants' => $product->get_meta( '_ssz_loyverse_variant_id' ) ) as $kind => $value ) {
                if ( 'skus' === $kind ) { $value = strtolower( (string) $value ); }
                if ( '' !== (string) $value ) { $existing[ $kind ][ (string) $value ][] = $id; }
            }
        }
        $report = $this->build( $items, $inventory, $store_id, $existing );
        if ( is_wp_error( $report ) ) { return $report; }
        $report['store_id'] = $store_id;
        $report['store_name'] = $store_name;
        $report['currency'] = get_woocommerce_currency();
        $report['ran_at'] = current_time( 'mysql', true );
        return $report;
    }

    public function build( $items, $inventory, $store_id, $existing ) {
        $levels = $sku_counts = $variant_counts = $item_counts = array();
        foreach ( $inventory as $level ) {
            if ( ! is_array( $level ) || ( $level['store_id'] ?? '' ) !== $store_id ) { continue; }
            $levels[ $level['variant_id'] ?? '' ][] = $level['in_stock'] ?? null;
        }
        foreach ( $items as $item ) {
            if ( ! is_array( $item ) || ! isset( $item['id'], $item['item_name'], $item['variants'] ) || ! is_array( $item['variants'] ) ) {
                return new WP_Error( 'preview_schema', 'An item is missing its ID, name or variants. Preview discarded; no products changed.' );
            }
            if ( ! empty( $item['deleted_at'] ) ) { continue; }
            $item_counts[ $item['id'] ] = ( $item_counts[ $item['id'] ] ?? 0 ) + 1;
            foreach ( $item['variants'] as $variant ) {
                if ( ! is_array( $variant ) ) { return new WP_Error( 'preview_schema', 'Invalid variant response; preview discarded.' ); }
                if ( ! empty( $variant['deleted_at'] ) ) { continue; }
                $sku = (string) ( $variant['sku'] ?? '' );
                $id = (string) ( $variant['variant_id'] ?? '' );
                $sku_key = strtolower( $sku );
                $sku_counts[ $sku_key ] = ( $sku_counts[ $sku_key ] ?? 0 ) + 1;
                $variant_counts[ $id ] = ( $variant_counts[ $id ] ?? 0 ) + 1;
            }
        }
        $report = array( 'items' => 0, 'simple' => 0, 'variable' => 0, 'variants' => 0, 'ready_items' => 0, 'blocked_items' => 0, 'rows' => array() );
        foreach ( $items as $item ) {
            if ( ! empty( $item['deleted_at'] ) ) { continue; }
            $variants = array_values( array_filter( $item['variants'], static function ( $v ) { return empty( $v['deleted_at'] ); } ) );
            $options = array();
            for ( $n = 1; $n <= 3; $n++ ) {
                if ( ! empty( $item[ 'option' . $n . '_name' ] ) ) { $options[ $n ] = $item[ 'option' . $n . '_name' ]; }
            }
            $type = count( $variants ) > 1 || $options ? 'Variable' : 'Simple';
            $report['items']++;
            $report[ strtolower( $type ) ]++;
            $item_rows = array();
            $combinations = array();
            foreach ( $variants as $variant ) {
                $sku = (string) ( $variant['sku'] ?? '' );
                $id = (string) ( $variant['variant_id'] ?? '' );
                $issues = array();
                if ( '' === $id || ( $variant_counts[ $id ] ?? 0 ) > 1 ) { $issues[] = 'Missing or duplicate variant ID'; }
                if ( '' === trim( $sku ) || $sku !== trim( $sku ) || ( $sku_counts[ strtolower( $sku ) ] ?? 0 ) > 1 ) { $issues[] = 'Missing, whitespace-padded or duplicate Loyverse SKU'; }
                if ( empty( $item['id'] ) || $item_counts[ $item['id'] ] > 1 || '' === trim( $item['item_name'] ) ) { $issues[] = 'Missing name or missing/duplicate item ID'; }
                if ( ! empty( $existing['items'][ $item['id'] ] ) || ! empty( $existing['variants'][ $id ] ) ) { $issues[] = 'Already linked: update planning required'; }
                if ( ! empty( $existing['skus'][ strtolower( $sku ) ] ) ) { $issues[] = 'SKU belongs to an existing WooCommerce record; no automatic adoption'; }
                if ( ! empty( $item['sold_by_weight'] ) || ! empty( $item['is_composite'] ) || ! empty( $item['use_production'] ) ) { $issues[] = 'Weight/composite/production item needs a policy'; }
                $store_rows = array_values( array_filter( $variant['stores'] ?? array(), static function ( $s ) use ( $store_id ) { return ( $s['store_id'] ?? '' ) === $store_id; } ) );
                $store = count( $store_rows ) === 1 ? $store_rows[0] : array();
                if ( count( $store_rows ) !== 1 || true !== ( $store['available_for_sale'] ?? null ) ) { $issues[] = 'Not confirmed available at selected store'; }
                $price = $store['price'] ?? ( $variant['default_price'] ?? null );
                $pricing = $store['pricing_type'] ?? ( $variant['default_pricing_type'] ?? '' );
                if ( 'FIXED' !== $pricing || ! $this->valid_number( $price ) || $price < 0 ) { $issues[] = 'Missing/invalid fixed price'; }
                $stock = 'Not tracked';
                if ( ! array_key_exists( 'track_stock', $item ) || ! is_bool( $item['track_stock'] ) ) { $issues[] = 'Unknown stock tracking policy'; }
                if ( true === ( $item['track_stock'] ?? null ) ) {
                    $records = $levels[ $id ] ?? array();
                    $stock = count( $records ) === 1 ? $records[0] : null;
                    if ( count( $records ) !== 1 || ! $this->valid_number( $stock ) ) { $issues[] = 'Missing, duplicate or invalid inventory'; }
                    elseif ( $stock < 0 || floor( (float) $stock ) !== (float) $stock ) { $issues[] = 'Negative/fractional stock needs a policy'; }
                }
                $attributes = array();
                foreach ( $options as $n => $name ) {
                    $value = $variant[ 'option' . $n . '_value' ] ?? '';
                    if ( '' === trim( (string) $value ) ) { $issues[] = 'Missing option value'; }
                    $attributes[] = $name . ': ' . $value;
                }
                if ( 'Variable' === $type && ! $options ) { $issues[] = 'Multiple variants without option names'; }
                $combination = implode( ' | ', $attributes );
                $combinations[ $combination ] = ( $combinations[ $combination ] ?? 0 ) + 1;
                $item_rows[] = array( 'name' => $item['item_name'], 'item_id' => $item['id'], 'variant_id' => $id, 'type' => $type, 'sku' => $sku, 'options' => $combination, 'price' => $this->valid_number( $price ) ? (string) $price : 'Unknown', 'stock' => null === $stock ? 'Unknown' : (string) $stock, 'image' => empty( $item['image_url'] ) ? 'None' : 'Available (not downloaded)', 'issues' => $issues );
            }
            $blocked = ! $item_rows;
            foreach ( $item_rows as &$row ) {
                if ( 'Variable' === $type && $combinations[ $row['options'] ] > 1 ) { $row['issues'][] = 'Duplicate variation options'; }
                if ( $row['issues'] ) { $blocked = true; }
            }
            unset( $row );
            if ( ! $item_rows ) {
                $item_rows[] = array( 'name' => $item['item_name'], 'item_id' => $item['id'], 'variant_id' => '', 'type' => $type, 'sku' => '', 'options' => '', 'price' => 'Unknown', 'stock' => 'Unknown', 'image' => '—', 'issues' => array( 'No active variants' ) );
            }
            $report[ $blocked ? 'blocked_items' : 'ready_items' ]++;
            $report['variants'] += count( $variants );
            foreach ( $item_rows as $row ) {
                $row['action'] = $blocked ? 'Blocked — review item' : 'Would create and publish';
                if ( $blocked && ! $row['issues'] ) { $row['issues'][] = 'Another variant blocks this item'; }
                $report['rows'][] = $row;
            }
        }
        return $report;
    }

    private function valid_number( $value ) {
        return ( is_int( $value ) || is_float( $value ) || is_string( $value ) ) && is_numeric( $value ) && is_finite( (float) $value );
    }
}
