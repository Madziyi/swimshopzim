<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSZ_Loyverse_Admin {
    const OPTION_TOKEN      = 'ssz_loyverse_access_token';
    const OPTION_STORE      = 'ssz_loyverse_store_id';
    const OPTION_INTERVAL   = 'ssz_loyverse_sync_interval';
    const OPTION_CONNECTION = 'ssz_loyverse_connection_status';
    const OPTION_REPORT     = 'ssz_loyverse_last_report';
    const OPTION_LOGS       = 'ssz_loyverse_sync_logs';
    const OPTION_DIAGNOSTIC = 'ssz_loyverse_api_diagnostics';
    const STORES_TRANSIENT  = 'ssz_loyverse_stores';

    /** @var SSZ_Loyverse_Client */
    private $client;

    /** @var SSZ_Loyverse_Sync */
    private $sync;

    public function __construct( SSZ_Loyverse_Client $client, SSZ_Loyverse_Sync $sync ) {
        $this->client = $client;
        $this->sync   = $sync;

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_post_ssz_loyverse_save_settings', array( $this, 'save_settings' ) );
        add_action( 'admin_post_ssz_loyverse_mapping_check', array( $this, 'mapping_check' ) );
        add_action( 'admin_post_ssz_loyverse_diagnose_reads', array( $this, 'diagnose_reads' ) );
        add_action( 'admin_post_ssz_loyverse_manual_sync', array( $this, 'manual_sync' ) );
        add_action( 'admin_post_ssz_loyverse_catalogue_preview', array( $this, 'catalogue_preview' ) );
    }

    public function admin_menu() {
        $parent = class_exists( 'WooCommerce' ) ? 'woocommerce' : 'options-general.php';

        add_submenu_page(
            $parent,
            __( 'Loyverse Sync', 'swimshop-loyverse-sync' ),
            __( 'Loyverse Sync', 'swimshop-loyverse-sync' ),
            $this->required_capability(),
            'swimshop-loyverse-sync',
            array( $this, 'render_page' )
        );
    }

    public function render_page() {
        if ( ! current_user_can( $this->required_capability() ) ) {
            wp_die( esc_html__( 'You do not have permission to manage Loyverse Sync.', 'swimshop-loyverse-sync' ) );
        }

        $token_saved = $this->client->has_token();
        $store_id    = (string) get_option( self::OPTION_STORE, '' );
        $interval    = (string) get_option( self::OPTION_INTERVAL, 'disabled' );
        $connection  = get_option( self::OPTION_CONNECTION, array() );
        $stores      = get_transient( self::STORES_TRANSIENT );
        $report      = get_option( self::OPTION_REPORT, array() );
        $logs        = get_option( self::OPTION_LOGS, array() );
        $diagnostic  = get_option( self::OPTION_DIAGNOSTIC, array() );

        if ( ! is_array( $stores ) ) {
            $stores = array();
        }
        if ( ! is_array( $connection ) ) {
            $connection = array();
        }
        if ( ! is_array( $report ) ) {
            $report = array();
        }
        if ( ! is_array( $logs ) ) {
            $logs = array();
        }

        $notice = isset( $_GET['ssz_notice'] ) ? sanitize_key( wp_unslash( $_GET['ssz_notice'] ) ) : '';
        $message = isset( $_GET['ssz_message'] ) ? sanitize_text_field( wp_unslash( $_GET['ssz_message'] ) ) : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'SwimShop Loyverse Sync', 'swimshop-loyverse-sync' ); ?></h1>
            <p><?php esc_html_e( 'Loyverse is the catalogue and inventory authority. Preview its products before importing. This version provides a read-only catalogue preview and the existing inventory tools.', 'swimshop-loyverse-sync' ); ?></p>

            <?php if ( $notice ) : ?>
                <div class="notice <?php echo 'error' === $notice ? 'notice-error' : ( 'warning' === $notice ? 'notice-warning' : 'notice-success' ); ?> is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
            <?php endif; ?>

            <?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
                <div class="notice notice-error"><p><?php esc_html_e( 'WooCommerce is inactive. Connection settings can be saved, but inventory sync cannot run.', 'swimshop-loyverse-sync' ); ?></p></div>
            <?php endif; ?>

            <hr>
            <h2><?php esc_html_e( 'Connection', 'swimshop-loyverse-sync' ); ?></h2>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="ssz_loyverse_save_settings">
                <?php wp_nonce_field( 'ssz_loyverse_save_settings' ); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="ssz_loyverse_token"><?php esc_html_e( 'Loyverse API access token', 'swimshop-loyverse-sync' ); ?></label></th>
                        <td>
                            <input type="password" id="ssz_loyverse_token" name="access_token" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo esc_attr( $token_saved ? __( 'Saved — enter a new token to replace it', 'swimshop-loyverse-sync' ) : __( 'Paste access token', 'swimshop-loyverse-sync' ) ); ?>">
                            <p class="description"><?php esc_html_e( 'The saved token is never printed back into this page. Leave this field blank to keep the current token.', 'swimshop-loyverse-sync' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Connection status', 'swimshop-loyverse-sync' ); ?></th>
                        <td><?php echo $this->connection_status_html( $token_saved, $connection ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ssz_loyverse_store"><?php esc_html_e( 'Inventory store', 'swimshop-loyverse-sync' ); ?></label></th>
                        <td>
                            <select id="ssz_loyverse_store" name="store_id">
                                <option value=""><?php esc_html_e( 'Choose a store', 'swimshop-loyverse-sync' ); ?></option>
                                <?php foreach ( $stores as $store ) : ?>
                                    <?php
                                    $id   = isset( $store['id'] ) ? (string) $store['id'] : '';
                                    $name = isset( $store['name'] ) ? (string) $store['name'] : $id;
                                    if ( ! $id ) {
                                        continue;
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $store_id, $id ); ?>><?php echo esc_html( $name ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Use Save & Test Connection to refresh the available Loyverse stores.', 'swimshop-loyverse-sync' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ssz_loyverse_interval"><?php esc_html_e( 'Automatic inventory sync', 'swimshop-loyverse-sync' ); ?></label></th>
                        <td>
                            <select id="ssz_loyverse_interval" name="sync_interval">
                                <option value="disabled" <?php selected( $interval, 'disabled' ); ?>><?php esc_html_e( 'Disabled', 'swimshop-loyverse-sync' ); ?></option>
                                <option value="15" <?php selected( $interval, '15' ); ?>><?php esc_html_e( 'Every 15 minutes', 'swimshop-loyverse-sync' ); ?></option>
                                <option value="30" <?php selected( $interval, '30' ); ?>><?php esc_html_e( 'Every 30 minutes', 'swimshop-loyverse-sync' ); ?></option>
                                <option value="60" <?php selected( $interval, '60' ); ?>><?php esc_html_e( 'Every hour', 'swimshop-loyverse-sync' ); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e( 'Automatic sync uses WP-Cron, so exact timing depends on site traffic.', 'swimshop-loyverse-sync' ); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary" name="submit_mode" value="save"><?php esc_html_e( 'Save Settings', 'swimshop-loyverse-sync' ); ?></button>
                    <button type="submit" class="button" name="submit_mode" value="save_test"><?php esc_html_e( 'Save & Test Connection', 'swimshop-loyverse-sync' ); ?></button>
                </p>
            </form>

            <hr>
            <h2><?php esc_html_e( 'Catalogue Import Preview', 'swimshop-loyverse-sync' ); ?></h2>
            <p><?php esc_html_e( 'Read-only: shows proposed published products, variations, prices and stock for the saved store. No products, media or stock are created or changed. Existing WooCommerce products are left alone.', 'swimshop-loyverse-sync' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="ssz_loyverse_catalogue_preview">
                <?php wp_nonce_field( 'ssz_loyverse_catalogue_preview' ); ?>
                <button type="submit" class="button button-primary" <?php disabled( ! $token_saved || ! $store_id || ! class_exists( 'WooCommerce' ) ); ?>><?php esc_html_e( 'Preview Loyverse Product Import (read-only)', 'swimshop-loyverse-sync' ); ?></button>
            </form>
            <?php $this->render_catalogue_preview( $store_id ); ?>
            <hr>
            <h2><?php esc_html_e( 'Inventory Sync', 'swimshop-loyverse-sync' ); ?></h2>
            <p><?php esc_html_e( 'Test API Data Access is read-only. Use it before mapping or syncing if Loyverse returns HTTP 400.', 'swimshop-loyverse-sync' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="ssz_loyverse_diagnose_reads">
                <?php wp_nonce_field( 'ssz_loyverse_diagnose_reads' ); ?>
                <button type="submit" class="button" <?php disabled( ! $token_saved || ! $store_id ); ?>><?php esc_html_e( 'Test API Data Access (read-only)', 'swimshop-loyverse-sync' ); ?></button>
            </form>
            <?php $this->render_diagnostics( $diagnostic ); ?>
            <p><strong><?php esc_html_e( 'Direction:', 'swimshop-loyverse-sync' ); ?></strong> <?php esc_html_e( 'Loyverse → WooCommerce', 'swimshop-loyverse-sync' ); ?></p>
            <p><strong><?php esc_html_e( 'Product matching:', 'swimshop-loyverse-sync' ); ?></strong> <?php esc_html_e( 'Exact SKU match', 'swimshop-loyverse-sync' ); ?></p>

            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                    <input type="hidden" name="action" value="ssz_loyverse_mapping_check">
                    <?php wp_nonce_field( 'ssz_loyverse_mapping_check' ); ?>
                    <button type="submit" class="button" <?php disabled( ! $token_saved || ! $store_id || ! class_exists( 'WooCommerce' ) ); ?>><?php esc_html_e( 'Check SKU Mapping', 'swimshop-loyverse-sync' ); ?></button>
                </form>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                    <input type="hidden" name="action" value="ssz_loyverse_manual_sync">
                    <?php wp_nonce_field( 'ssz_loyverse_manual_sync' ); ?>
                    <button type="submit" class="button button-primary" <?php disabled( ! $token_saved || ! $store_id || ! class_exists( 'WooCommerce' ) ); ?>><?php esc_html_e( 'Sync Inventory Now', 'swimshop-loyverse-sync' ); ?></button>
                </form>
            </div>

            <?php $this->render_report( $report ); ?>
            <?php $this->render_logs( $logs ); ?>
        </div>
        <?php
    }

    public function save_settings() {
        $this->authorize_action( 'ssz_loyverse_save_settings' );

        $old_token = (string) get_option( self::OPTION_TOKEN, '' );
        $new_token = isset( $_POST['access_token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['access_token'] ) ) ) : '';
        $token_changed = false;

        if ( $new_token ) {
            $this->set_noautoload_option( self::OPTION_TOKEN, $new_token );
            $token_changed = ! hash_equals( $old_token, $new_token );
        }

        $store_id = isset( $_POST['store_id'] ) ? sanitize_text_field( wp_unslash( $_POST['store_id'] ) ) : '';
        $interval = isset( $_POST['sync_interval'] ) ? sanitize_key( wp_unslash( $_POST['sync_interval'] ) ) : 'disabled';
        if ( ! in_array( $interval, array( 'disabled', '15', '30', '60' ), true ) ) {
            $interval = 'disabled';
        }

        if ( $token_changed ) {
            delete_transient( $this->preview_key() );
            delete_transient( self::STORES_TRANSIENT );
            $store_id = '';
            delete_option( self::OPTION_CONNECTION );
            delete_option( self::OPTION_DIAGNOSTIC );
        }

        if ( $store_id !== (string) get_option( self::OPTION_STORE, '' ) ) {
            delete_transient( $this->preview_key() );
            delete_option( self::OPTION_DIAGNOSTIC );
        }
        $this->set_noautoload_option( self::OPTION_STORE, $store_id );
        $this->set_noautoload_option( self::OPTION_INTERVAL, $interval );
        SSZ_Loyverse_Plugin::reschedule( $interval );

        $mode = isset( $_POST['submit_mode'] ) ? sanitize_key( wp_unslash( $_POST['submit_mode'] ) ) : 'save';
        if ( 'save_test' === $mode ) {
            $stores = $this->client->get_stores();
            if ( is_wp_error( $stores ) ) {
                $this->set_connection_status( false, $stores->get_error_message() );
                $this->redirect_with_notice( 'error', $stores->get_error_message() );
            }

            set_transient( self::STORES_TRANSIENT, $stores, 12 * HOUR_IN_SECONDS );
            $this->set_connection_status( true, sprintf( _n( '%d store found.', '%d stores found.', count( $stores ), 'swimshop-loyverse-sync' ), count( $stores ) ) );
            $this->redirect_with_notice( 'success', __( 'Connection successful. Choose the inventory store, then save settings.', 'swimshop-loyverse-sync' ) );
        }

        $this->redirect_with_notice( 'success', __( 'Loyverse Sync settings saved.', 'swimshop-loyverse-sync' ) );
    }


    private function preview_key() {
        return 'ssz_loyverse_preview_' . get_current_user_id();
    }

    public function catalogue_preview() {
        $this->authorize_action( 'ssz_loyverse_catalogue_preview' );
        // Remove stale results before fetching; failed fetches must never show a partial plan.
        delete_transient( $this->preview_key() );
        $preview = new SSZ_Loyverse_Preview();
        $result = $preview->run( $this->client, (string) get_option( self::OPTION_STORE, '' ) );
        if ( is_wp_error( $result ) ) {
            $this->redirect_with_notice( 'error', $result->get_error_message() );
        }
        set_transient( $this->preview_key(), $result, 30 * MINUTE_IN_SECONDS );
        $this->redirect_with_notice( $result['blocked_items'] ? 'warning' : 'success', sprintf(
            'Read-only preview complete: %d items, %d ready to create, %d blocked. No products or stock changed.',
            $result['items'], $result['ready_items'], $result['blocked_items']
        ) );
    }

    private function render_catalogue_preview( $store_id ) {
        $preview = get_transient( $this->preview_key() );
        if ( ! is_array( $preview ) || ( $preview['store_id'] ?? '' ) !== $store_id ) { return; }
        echo '<h3>' . esc_html__( 'Latest Catalogue Preview', 'swimshop-loyverse-sync' ) . '</h3>';
        echo '<p>' . esc_html( $preview['store_name'] . ' — ' . $preview['ran_at'] . ' UTC. Preview expires after 30 minutes.' ) . '</p>';
        echo '<p>' . esc_html( sprintf( '%d items: %d simple, %d variable; %d active variants. %d ready items, %d blocked items.', $preview['items'], $preview['simple'], $preview['variable'], $preview['variants'], $preview['ready_items'], $preview['blocked_items'] ) ) . '</p>';
        echo '<p>' . esc_html( 'Prices are raw Loyverse amounts. WooCommerce currency: ' . $preview['currency'] . '. Confirm currency and tax treatment before import. Images are only flagged as available; none are downloaded. Categories, brands and descriptions are outside this preview.' ) . '</p>';
        wp_enqueue_script( 'ssz-loyverse-preview-copy', plugins_url( 'assets/admin-preview.js', SSZ_LOYVERSE_SYNC_FILE ), array(), SSZ_LOYVERSE_SYNC_VERSION, true );
        echo '<p>' . esc_html__( 'Copy reports include every preview page. Each row represents a variant; blocked rows include the reason. You can also select and copy the text manually.', 'swimshop-loyverse-sync' ) . '</p>';
        foreach ( array( 'all' => 'All products', 'blocked' => 'Blocked products only' ) as $scope => $label ) {
            $id = 'ssz-preview-copy-' . $scope;
            echo '<details class="ssz-preview-copy"><summary>' . esc_html( $label ) . '</summary>';
            echo '<p><button type="button" class="button" data-ssz-copy-target="' . esc_attr( $id ) . '">' . esc_html( 'Copy ' . strtolower( $label ) ) . '</button> <span role="status" aria-live="polite"></span></p>';
            echo '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html( $label . ' report' ) . '</label>';
            echo '<textarea readonly id="' . esc_attr( $id ) . '" rows="12" style="width:100%;font-family:monospace" spellcheck="false">' . esc_textarea( $this->catalogue_copy_text( $preview, 'blocked' === $scope ) ) . '</textarea>';
            echo '</details>';
        }
        $pages = max( 1, (int) ceil( count( $preview['rows'] ) / 50 ) );
        $page = isset( $_GET['preview_page'] ) ? min( $pages, max( 1, absint( $_GET['preview_page'] ) ) ) : 1;
        echo '<div style="overflow-x:auto"><table class="widefat striped"><thead><tr>';
        foreach ( array( 'Product / source IDs', 'Type / options', 'SKU', 'Price', 'Stock', 'Image', 'Proposed action / issues' ) as $heading ) { echo '<th scope="col">' . esc_html( $heading ) . '</th>'; }
        echo '</tr></thead><tbody>';
        foreach ( array_slice( $preview['rows'], ( $page - 1 ) * 50, 50 ) as $row ) {
            echo '<tr><td><strong>' . esc_html( $row['name'] ) . '</strong><br><small>' . esc_html( 'Item: ' . $row['item_id'] ) . '<br>' . esc_html( 'Variant: ' . $row['variant_id'] ) . '</small></td>';
            foreach ( array( $row['type'] . ( $row['options'] ? ' — ' . $row['options'] : '' ), $row['sku'], $row['price'], $row['stock'], $row['image'], $row['action'] . ( $row['issues'] ? ': ' . implode( '; ', $row['issues'] ) : '' ) ) as $value ) { echo '<td>' . esc_html( $value ) . '</td>'; }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
        echo '<p>' . esc_html( sprintf( 'Page %d of %d — all %d rows are included in the preview.', $page, $pages, count( $preview['rows'] ) ) ) . ' ';
        foreach ( array( 'Previous' => $page - 1, 'Next' => $page + 1 ) as $label => $target ) {
            if ( $target >= 1 && $target <= $pages ) {
                echo '<a class="button" href="' . esc_url( add_query_arg( array( 'page' => 'swimshop-loyverse-sync', 'preview_page' => $target ), admin_url( 'admin.php' ) ) ) . '">' . esc_html( $label ) . '</a> ';
            }
        }
        echo '</p>';
    }

    private function catalogue_copy_text( $preview, $blocked_only ) {
        $lines = array(
            'SwimShop Loyverse catalogue preview — READ ONLY (no products changed)',
            'Store: ' . $preview['store_name'] . ' | Generated: ' . $preview['ran_at'] . ' UTC',
            sprintf( 'Items: %d | Ready: %d | Blocked: %d | Active variants: %d', $preview['items'], $preview['ready_items'], $preview['blocked_items'], $preview['variants'] ),
            'Scope: ' . ( $blocked_only ? 'Blocked products only' : 'All products, across all preview pages' ),
            'Prices: raw Loyverse amounts; WooCommerce currency ' . $preview['currency'] . '. Currency and tax treatment require confirmation.',
            '',
            implode( "\t", array( 'Product', 'Type', 'Options', 'SKU', 'Price', 'Stock', 'Proposed action', 'Blocking reasons', 'Loyverse item ID', 'Loyverse variant ID' ) ),
        );
        $count = 0;
        foreach ( $preview['rows'] as $row ) {
            if ( $blocked_only && empty( $row['issues'] ) ) { continue; }
            $values = array( $row['name'], $row['type'], $row['options'], $row['sku'], $row['price'], $row['stock'], $row['action'], implode( '; ', $row['issues'] ), $row['item_id'], $row['variant_id'] );
            $values = array_map( static function ( $value ) { return str_replace( array( "\r", "\n", "\t" ), ' ', (string) $value ); }, $values );
            $lines[] = implode( "\t", $values );
            $count++;
        }
        $lines[] = '';
        $lines[] = sprintf( 'End of report — %d variant rows included.', $count );
        return implode( "\n", $lines );
    }

    public function diagnose_reads() {
        $this->authorize_action( 'ssz_loyverse_diagnose_reads' );
        $store_id = trim( (string) get_option( self::OPTION_STORE, '' ) );
        if ( ! $store_id ) {
            $this->redirect_with_notice( 'error', 'Choose and save a Loyverse inventory store first.' );
        }
        $checks = $this->client->diagnose_reads( $store_id );
        $this->set_noautoload_option(
            self::OPTION_DIAGNOSTIC,
            array( 'checked_at' => current_time( 'mysql', true ), 'checks' => $checks )
        );
        $all_ok = ! empty( $checks['variants']['ok'] ) && ! empty( $checks['inventory']['ok'] );
        $this->redirect_with_notice( $all_ok ? 'success' : 'error',
            $all_ok ? 'Both Loyverse inventory read endpoints passed.' : 'At least one Loyverse read failed. See API Diagnostics below for the exact error.'
        );
    }

    private function render_diagnostics( $diagnostic ) {
        if ( ! is_array( $diagnostic ) || empty( $diagnostic['checks'] ) || ! is_array( $diagnostic['checks'] ) ) {
            return;
        }
        echo '<h3>' . esc_html__( 'API Diagnostics (read-only)', 'swimshop-loyverse-sync' ) . '</h3>';
        if ( ! empty( $diagnostic['checked_at'] ) ) {
            echo '<p>' . esc_html( $diagnostic['checked_at'] . ' UTC' ) . '</p>';
        }
        echo '<table class="widefat striped" style="max-width:1000px"><thead><tr><th>Endpoint</th><th>Status</th><th>Result / Error</th></tr></thead><tbody>';
        foreach ( array( 'variants' => 'GET /variants', 'inventory' => 'GET /inventory' ) as $key => $label ) {
            $check = isset( $diagnostic['checks'][ $key ] ) && is_array( $diagnostic['checks'][ $key ] ) ? $diagnostic['checks'][ $key ] : array();
            echo '<tr><td>' . esc_html( $label ) . '</td><td>' . esc_html( ! empty( $check['ok'] ) ? 'PASS' : 'FAIL' ) . '</td><td>' . esc_html( isset( $check['message'] ) ? (string) $check['message'] : 'Not tested' ) . '</td></tr>';
        }
        echo '</tbody></table>';
    }

    public function mapping_check() {
        $this->authorize_action( 'ssz_loyverse_mapping_check' );

        $result = $this->sync->run( true );
        if ( is_wp_error( $result ) ) {
            $this->sync->log( 'error', 'Inventory mapping check failed: ' . $result->get_error_message() );
            $this->redirect_with_notice( 'error', $result->get_error_message() );
        }

        $message = sprintf(
            __( 'Mapping check complete: %1$d matched, %2$d would update, %3$d Woo SKUs unmatched.', 'swimshop-loyverse-sync' ),
            (int) $result['matched'],
            (int) $result['updated'],
            (int) $result['woo_unmatched_sku']
        );
        $this->redirect_with_notice( $result['errors'] ? 'warning' : 'success', $message );
    }

    public function manual_sync() {
        $this->authorize_action( 'ssz_loyverse_manual_sync' );

        $result = $this->sync->run();
        if ( is_wp_error( $result ) ) {
            $this->sync->log( 'error', 'Manual inventory sync failed: ' . $result->get_error_message() );
            $this->redirect_with_notice( 'error', $result->get_error_message() );
        }

        $message = sprintf(
            __( 'Sync complete: %1$d matched, %2$d updated, %3$d unchanged.', 'swimshop-loyverse-sync' ),
            (int) $result['matched'],
            (int) $result['updated'],
            (int) $result['unchanged']
        );
        $this->redirect_with_notice( $result['errors'] ? 'warning' : 'success', $message );
    }

    private function render_report( $report ) {
        if ( ! $report ) {
            echo '<p><em>' . esc_html__( 'No inventory sync has run yet.', 'swimshop-loyverse-sync' ) . '</em></p>';
            return;
        }

        $rows = array(
            __( 'Report type', 'swimshop-loyverse-sync' ) => isset( $report['mode'] ) && 'check' === $report['mode'] ? __( 'Mapping check (no stock changed)', 'swimshop-loyverse-sync' ) : __( 'Inventory sync', 'swimshop-loyverse-sync' ),
            __( 'Last run (UTC)', 'swimshop-loyverse-sync' ) => isset( $report['ran_at'] ) ? $report['ran_at'] : '—',
            __( 'Loyverse variants with SKU', 'swimshop-loyverse-sync' ) => isset( $report['loyverse_variants'] ) ? $report['loyverse_variants'] : 0,
            __( 'WooCommerce inventory records', 'swimshop-loyverse-sync' ) => isset( $report['woo_inventory_records'] ) ? $report['woo_inventory_records'] : 0,
            __( 'Matched SKUs', 'swimshop-loyverse-sync' ) => isset( $report['matched'] ) ? $report['matched'] : 0,
            ( isset( $report['mode'] ) && 'check' === $report['mode'] ? __( 'Would update', 'swimshop-loyverse-sync' ) : __( 'Updated', 'swimshop-loyverse-sync' ) ) => isset( $report['updated'] ) ? $report['updated'] : 0,
            __( 'Unchanged', 'swimshop-loyverse-sync' ) => isset( $report['unchanged'] ) ? $report['unchanged'] : 0,
            __( 'Woo products/variations missing SKU', 'swimshop-loyverse-sync' ) => isset( $report['woo_missing_sku'] ) ? $report['woo_missing_sku'] : 0,
            __( 'Woo SKUs not found in Loyverse', 'swimshop-loyverse-sync' ) => isset( $report['woo_unmatched_sku'] ) ? $report['woo_unmatched_sku'] : 0,
            __( 'Loyverse SKUs not found in WooCommerce', 'swimshop-loyverse-sync' ) => isset( $report['loyverse_unmatched_sku'] ) ? $report['loyverse_unmatched_sku'] : 0,
            __( 'Matched variants without inventory at selected store', 'swimshop-loyverse-sync' ) => isset( $report['loyverse_missing_level'] ) ? $report['loyverse_missing_level'] : 0,
            __( 'Duplicate Woo SKUs skipped', 'swimshop-loyverse-sync' ) => isset( $report['duplicate_woo_sku'] ) ? $report['duplicate_woo_sku'] : 0,
            __( 'Duplicate Loyverse SKUs skipped', 'swimshop-loyverse-sync' ) => isset( $report['duplicate_loyverse_sku'] ) ? $report['duplicate_loyverse_sku'] : 0,
        );

        echo '<h3>' . esc_html__( 'Last Sync Report', 'swimshop-loyverse-sync' ) . '</h3>';
        echo '<table class="widefat striped" style="max-width:900px"><tbody>';
        foreach ( $rows as $label => $value ) {
            echo '<tr><th style="width:55%">' . esc_html( $label ) . '</th><td>' . esc_html( (string) $value ) . '</td></tr>';
        }
        echo '</tbody></table>';

        if ( ! empty( $report['errors'] ) && is_array( $report['errors'] ) ) {
            echo '<h4>' . esc_html__( 'Errors', 'swimshop-loyverse-sync' ) . '</h4><ul>';
            foreach ( array_slice( $report['errors'], 0, 20 ) as $error ) {
                echo '<li>' . esc_html( $error ) . '</li>';
            }
            echo '</ul>';
        }
    }

    private function render_logs( $logs ) {
        echo '<h3>' . esc_html__( 'Recent Sync Log', 'swimshop-loyverse-sync' ) . '</h3>';
        if ( ! $logs ) {
            echo '<p><em>' . esc_html__( 'No log entries yet.', 'swimshop-loyverse-sync' ) . '</em></p>';
            return;
        }

        echo '<table class="widefat striped" style="max-width:1100px"><thead><tr><th>' . esc_html__( 'UTC time', 'swimshop-loyverse-sync' ) . '</th><th>' . esc_html__( 'Level', 'swimshop-loyverse-sync' ) . '</th><th>' . esc_html__( 'Message', 'swimshop-loyverse-sync' ) . '</th></tr></thead><tbody>';
        foreach ( array_slice( $logs, 0, 20 ) as $entry ) {
            echo '<tr>';
            echo '<td>' . esc_html( isset( $entry['time'] ) ? $entry['time'] : '' ) . '</td>';
            echo '<td>' . esc_html( strtoupper( isset( $entry['level'] ) ? $entry['level'] : '' ) ) . '</td>';
            echo '<td>' . esc_html( isset( $entry['message'] ) ? $entry['message'] : '' ) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    private function connection_status_html( $token_saved, $connection ) {
        if ( ! $token_saved ) {
            return '<span style="color:#b32d2e">●</span> ' . esc_html__( 'No token saved', 'swimshop-loyverse-sync' );
        }

        if ( ! isset( $connection['ok'] ) ) {
            return '<span style="color:#996800">●</span> ' . esc_html__( 'Token saved — connection not tested', 'swimshop-loyverse-sync' );
        }

        $ok      = (bool) $connection['ok'];
        $message = isset( $connection['message'] ) ? $connection['message'] : '';
        $checked = isset( $connection['checked_at'] ) ? $connection['checked_at'] : '';
        $color   = $ok ? '#008a20' : '#b32d2e';
        $label   = $ok ? __( 'Connected', 'swimshop-loyverse-sync' ) : __( 'Connection failed', 'swimshop-loyverse-sync' );

        return sprintf(
            '<span style="color:%1$s">●</span> %2$s%3$s%4$s',
            esc_attr( $color ),
            esc_html( $label ),
            $message ? ' — ' . esc_html( $message ) : '',
            $checked ? ' <small>(' . esc_html( $checked . ' UTC' ) . ')</small>' : ''
        );
    }

    private function set_connection_status( $ok, $message ) {
        $this->set_noautoload_option(
            self::OPTION_CONNECTION,
            array(
                'ok'         => (bool) $ok,
                'message'    => sanitize_text_field( $message ),
                'checked_at' => current_time( 'mysql', true ),
            )
        );
    }

    private function authorize_action( $nonce_action ) {
        if ( ! current_user_can( $this->required_capability() ) ) {
            wp_die( esc_html__( 'You do not have permission to manage Loyverse Sync.', 'swimshop-loyverse-sync' ) );
        }

        check_admin_referer( $nonce_action );
    }

    private function required_capability() {
        return class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options';
    }

    private function redirect_with_notice( $type, $message ) {
        $url = add_query_arg(
            array(
                'page'        => 'swimshop-loyverse-sync',
                'ssz_notice'  => sanitize_key( $type ),
                'ssz_message' => sanitize_text_field( $message ),
            ),
            class_exists( 'WooCommerce' ) ? admin_url( 'admin.php' ) : admin_url( 'options-general.php' )
        );

        wp_safe_redirect( $url );
        exit;
    }

    private function set_noautoload_option( $name, $value ) {
        if ( false === get_option( $name, false ) ) {
            add_option( $name, $value, '', 'no' );
        } else {
            update_option( $name, $value, false );
        }
    }
}
