<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSZ_Loyverse_Sync {
    /** @var SSZ_Loyverse_Client */
    private $client;

    public function __construct( SSZ_Loyverse_Client $client ) {
        $this->client = $client;
    }

    public function run( $dry_run = false ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return new WP_Error( 'ssz_loyverse_no_woocommerce', __( 'WooCommerce is not active.', 'swimshop-loyverse-sync' ) );
        }

        if ( ! $this->client->has_token() ) {
            return new WP_Error( 'ssz_loyverse_missing_token', __( 'Add a Loyverse API access token before syncing.', 'swimshop-loyverse-sync' ) );
        }

        $store_id = trim( (string) get_option( SSZ_Loyverse_Admin::OPTION_STORE, '' ) );
        if ( ! $store_id ) {
            return new WP_Error( 'ssz_loyverse_missing_store', __( 'Choose a Loyverse store before syncing.', 'swimshop-loyverse-sync' ) );
        }

        $variants = $this->client->get_all_variants();
        if ( is_wp_error( $variants ) ) {
            return $variants;
        }

        $inventory = $this->client->get_inventory_for_store( $store_id );
        if ( is_wp_error( $inventory ) ) {
            return $inventory;
        }

        $loyverse = $this->index_loyverse( $variants, $inventory, $store_id );
        $woo      = $this->index_woocommerce();

        $report = array(
            'ran_at'                    => current_time( 'mysql', true ),
            'mode'                      => $dry_run ? 'check' : 'sync',
            'store_id'                  => $store_id,
            'loyverse_variants'         => count( $loyverse['sku_to_variant'] ),
            'loyverse_inventory_levels' => count( $loyverse['inventory_by_variant'] ),
            'woo_inventory_records'     => count( $woo['by_sku'] ),
            'matched'                   => 0,
            'updated'                   => 0,
            'unchanged'                 => 0,
            'woo_missing_sku'           => $woo['missing_sku'],
            'woo_unmatched_sku'         => 0,
            'loyverse_unmatched_sku'    => 0,
            'loyverse_missing_level'    => 0,
            'duplicate_woo_sku'         => count( $woo['duplicates'] ),
            'duplicate_loyverse_sku'    => count( $loyverse['duplicates'] ),
            'errors'                    => array(),
        );

        $matched_loyverse_skus = array();
        $parents_to_sync       = array();

        foreach ( $woo['by_sku'] as $sku => $product_id ) {
            if ( isset( $woo['duplicates'][ $sku ] ) || isset( $loyverse['duplicates'][ $sku ] ) ) {
                continue;
            }

            if ( ! isset( $loyverse['sku_to_variant'][ $sku ] ) ) {
                $report['woo_unmatched_sku']++;
                continue;
            }

            $variant_id = $loyverse['sku_to_variant'][ $sku ];
            if ( ! array_key_exists( $variant_id, $loyverse['inventory_by_variant'] ) ) {
                $report['loyverse_missing_level']++;
                continue;
            }

            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                $report['errors'][] = sprintf( 'WooCommerce product %d could not be loaded.', $product_id );
                continue;
            }

            $report['matched']++;
            $matched_loyverse_skus[ $sku ] = true;
            $quantity = wc_stock_amount( $loyverse['inventory_by_variant'][ $variant_id ] );

            $target_status = $quantity > 0 ? 'instock' : ( $product->backorders_allowed() ? 'onbackorder' : 'outofstock' );
            $current_qty   = $product->get_stock_quantity();
            $needs_update  = ! $product->get_manage_stock()
                || null === $current_qty
                || (float) $current_qty !== (float) $quantity
                || $product->get_stock_status() !== $target_status;

            if ( ! $needs_update ) {
                $report['unchanged']++;
                continue;
            }

            if ( $dry_run ) {
                $report['updated']++;
                continue;
            }

            try {
                $product->set_manage_stock( true );
                $product->set_stock_quantity( $quantity );
                $product->set_stock_status( $target_status );
                $product->save();
                $report['updated']++;

                if ( $product->is_type( 'variation' ) && $product->get_parent_id() ) {
                    $parents_to_sync[ $product->get_parent_id() ] = true;
                }
            } catch ( Exception $e ) {
                $report['errors'][] = sprintf( 'SKU %s: %s', $sku, $e->getMessage() );
            }
        }

        foreach ( $loyverse['sku_to_variant'] as $sku => $variant_id ) {
            if ( isset( $loyverse['duplicates'][ $sku ] ) ) {
                continue;
            }

            if ( ! isset( $woo['by_sku'][ $sku ] ) ) {
                $report['loyverse_unmatched_sku']++;
            }
        }

        foreach ( array_keys( $parents_to_sync ) as $parent_id ) {
            if ( class_exists( 'WC_Product_Variable' ) ) {
                WC_Product_Variable::sync_stock_status( $parent_id );
            }
        }

        $this->store_report( $report );

        $summary = sprintf(
            $dry_run ? 'Inventory mapping check complete: %d matched, %d would update, %d unchanged, %d Woo SKUs unmatched.' : 'Inventory sync complete: %d matched, %d updated, %d unchanged, %d Woo SKUs unmatched.',
            $report['matched'],
            $report['updated'],
            $report['unchanged'],
            $report['woo_unmatched_sku']
        );

        $this->log( $report['errors'] ? 'warning' : 'success', $summary );

        return $report;
    }

    private function index_loyverse( $variants, $inventory, $store_id ) {
        $sku_to_variant = array();
        $duplicates     = array();

        foreach ( $variants as $variant ) {
            if ( ! is_array( $variant ) ) {
                continue;
            }

            $sku        = isset( $variant['sku'] ) ? trim( (string) $variant['sku'] ) : '';
            $variant_id = isset( $variant['variant_id'] ) ? trim( (string) $variant['variant_id'] ) : '';

            if ( ! $sku || ! $variant_id ) {
                continue;
            }

            if ( isset( $sku_to_variant[ $sku ] ) ) {
                $duplicates[ $sku ] = true;
                continue;
            }

            $sku_to_variant[ $sku ] = $variant_id;
        }

        $inventory_by_variant = array();
        foreach ( $inventory as $level ) {
            if ( ! is_array( $level ) ) {
                continue;
            }

            if ( isset( $level['store_id'] ) && (string) $level['store_id'] !== (string) $store_id ) {
                continue;
            }

            $variant_id = isset( $level['variant_id'] ) ? trim( (string) $level['variant_id'] ) : '';
            if ( ! $variant_id || ! array_key_exists( 'in_stock', $level ) ) {
                continue;
            }

            $inventory_by_variant[ $variant_id ] = (float) $level['in_stock'];
        }

        return array(
            'sku_to_variant'       => $sku_to_variant,
            'duplicates'           => $duplicates,
            'inventory_by_variant' => $inventory_by_variant,
        );
    }

    private function index_woocommerce() {
        $ids = get_posts(
            array(
                'post_type'      => array( 'product', 'product_variation' ),
                'post_status'    => array( 'publish', 'private', 'draft', 'pending' ),
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'orderby'        => 'ID',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            )
        );

        $by_sku      = array();
        $duplicates  = array();
        $missing_sku = 0;

        foreach ( $ids as $id ) {
            $product = wc_get_product( $id );
            if ( ! $product ) {
                continue;
            }

            if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) || $product->is_type( 'external' ) ) {
                continue;
            }

            $sku = trim( (string) $product->get_sku() );
            if ( ! $sku ) {
                $missing_sku++;
                continue;
            }

            if ( isset( $by_sku[ $sku ] ) ) {
                $duplicates[ $sku ] = true;
                continue;
            }

            $by_sku[ $sku ] = (int) $id;
        }

        return array(
            'by_sku'      => $by_sku,
            'duplicates'  => $duplicates,
            'missing_sku' => $missing_sku,
        );
    }

    private function store_report( $report ) {
        $this->set_noautoload_option( SSZ_Loyverse_Admin::OPTION_REPORT, $report );
    }

    public function log( $level, $message ) {
        $logs = get_option( SSZ_Loyverse_Admin::OPTION_LOGS, array() );
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        array_unshift(
            $logs,
            array(
                'time'    => current_time( 'mysql', true ),
                'level'   => sanitize_key( $level ),
                'message' => sanitize_text_field( $message ),
            )
        );

        $logs = array_slice( $logs, 0, 50 );
        $this->set_noautoload_option( SSZ_Loyverse_Admin::OPTION_LOGS, $logs );
    }

    private function set_noautoload_option( $name, $value ) {
        if ( false === get_option( $name, false ) ) {
            add_option( $name, $value, '', 'no' );
        } else {
            update_option( $name, $value, false );
        }
    }
}
