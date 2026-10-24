<?php
/**
 * Plugin Name: SwimShop Loyverse Sync
 * Description: One-way Loyverse to WooCommerce inventory synchronization for SwimShop Zimbabwe.
 * Version: 0.1.4
 * Author: SwimShop Zimbabwe
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * Text Domain: swimshop-loyverse-sync
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SSZ_LOYVERSE_SYNC_VERSION', '0.1.4' );
define( 'SSZ_LOYVERSE_SYNC_FILE', __FILE__ );
define( 'SSZ_LOYVERSE_SYNC_DIR', plugin_dir_path( __FILE__ ) );

require_once SSZ_LOYVERSE_SYNC_DIR . 'includes/class-ssz-loyverse-client.php';
require_once SSZ_LOYVERSE_SYNC_DIR . 'includes/class-ssz-loyverse-preview.php';
require_once SSZ_LOYVERSE_SYNC_DIR . 'includes/class-ssz-loyverse-sync.php';
require_once SSZ_LOYVERSE_SYNC_DIR . 'includes/class-ssz-loyverse-admin.php';

final class SSZ_Loyverse_Plugin {
    const CRON_HOOK = 'ssz_loyverse_inventory_sync_event';

    /** @var SSZ_Loyverse_Plugin|null */
    private static $instance = null;

    /** @var SSZ_Loyverse_Client */
    public $client;

    /** @var SSZ_Loyverse_Sync */
    public $sync;

    /** @var SSZ_Loyverse_Admin */
    public $admin;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->client = new SSZ_Loyverse_Client();
        $this->sync   = new SSZ_Loyverse_Sync( $this->client );
        $this->admin  = new SSZ_Loyverse_Admin( $this->client, $this->sync );

        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
        add_action( self::CRON_HOOK, array( $this, 'run_scheduled_sync' ) );
        add_action( 'admin_init', array( $this, 'ensure_schedule_matches_setting' ) );
        add_action( 'admin_notices', array( $this, 'woocommerce_notice' ) );
    }

    public function add_cron_schedules( $schedules ) {
        $schedules['ssz_every_15_minutes'] = array(
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 15 minutes', 'swimshop-loyverse-sync' ),
        );

        $schedules['ssz_every_30_minutes'] = array(
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 30 minutes', 'swimshop-loyverse-sync' ),
        );

        return $schedules;
    }

    public static function schedule_key_for_interval( $interval ) {
        $map = array(
            '15' => 'ssz_every_15_minutes',
            '30' => 'ssz_every_30_minutes',
            '60' => 'hourly',
        );

        return isset( $map[ (string) $interval ] ) ? $map[ (string) $interval ] : '';
    }

    public static function reschedule( $interval ) {
        wp_clear_scheduled_hook( self::CRON_HOOK );

        $schedule = self::schedule_key_for_interval( $interval );
        if ( $schedule ) {
            wp_schedule_event( time() + MINUTE_IN_SECONDS, $schedule, self::CRON_HOOK );
        }
    }

    public function ensure_schedule_matches_setting() {
        $interval = (string) get_option( SSZ_Loyverse_Admin::OPTION_INTERVAL, 'disabled' );
        $schedule = self::schedule_key_for_interval( $interval );
        $event    = wp_get_scheduled_event( self::CRON_HOOK );

        if ( ! $schedule && $event ) {
            wp_clear_scheduled_hook( self::CRON_HOOK );
            return;
        }

        if ( $schedule && ( ! $event || $event->schedule !== $schedule ) ) {
            self::reschedule( $interval );
        }
    }

    public function run_scheduled_sync() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            $this->sync->log( 'error', 'Scheduled inventory sync skipped because WooCommerce is inactive.' );
            return;
        }

        $result = $this->sync->run();
        if ( is_wp_error( $result ) ) {
            $this->sync->log( 'error', 'Scheduled inventory sync failed: ' . $result->get_error_message() );
        }
    }

    public function woocommerce_notice() {
        if ( ! current_user_can( 'activate_plugins' ) || class_exists( 'WooCommerce' ) ) {
            return;
        }

        echo '<div class="notice notice-warning"><p>';
        echo esc_html__( 'SwimShop Loyverse Sync requires WooCommerce for inventory synchronization.', 'swimshop-loyverse-sync' );
        echo '</p></div>';
    }

    public static function activate() {
        if ( false === get_option( SSZ_Loyverse_Admin::OPTION_INTERVAL, false ) ) {
            add_option( SSZ_Loyverse_Admin::OPTION_INTERVAL, 'disabled', '', 'no' );
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook( self::CRON_HOOK );
    }
}

register_activation_hook( __FILE__, array( 'SSZ_Loyverse_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SSZ_Loyverse_Plugin', 'deactivate' ) );

add_action(
    'plugins_loaded',
    static function () {
        SSZ_Loyverse_Plugin::instance();
    }
);
