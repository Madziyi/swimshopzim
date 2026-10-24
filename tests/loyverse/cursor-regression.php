<?php
// Run with Local PHP: php cursor-regression.php <WordPress public directory>
define( 'ABSPATH', rtrim( $argv[1], '/\\' ) . '/' );
define( 'WPINC', 'wp-includes' );
function apply_filters( $hook, $value ) { return $value; }
function do_action( $hook, ...$args ) {}
require ABSPATH . WPINC . '/functions.php';
require ABSPATH . WPINC . '/formatting.php';
require ABSPATH . WPINC . '/class-wp-error.php';
define( 'SSZ_LOYVERSE_SYNC_VERSION', 'test' );
class SSZ_Loyverse_Admin { const OPTION_TOKEN = 'test'; }
function home_url( $path ) { return 'http://example.test/'; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_remote_retrieve_response_code( $response ) { return $response['status']; }
function wp_remote_retrieve_body( $response ) { return json_encode( $response['body'] ); }
function wp_remote_get( $url, $args ) {
    global $requests, $cursor, $mode;
    parse_str( parse_url( $url, PHP_URL_QUERY ) ?? '', $query );
    $requests++;
    if ( $requests > 1 && ( $query['cursor'] ?? null ) !== $cursor ) {
        throw new RuntimeException( 'Cursor changed in transit' );
    }
    return array( 'status' => 200, 'body' => array(
        'variants' => array( array( 'variant_id' => 'page-' . $requests ) ),
        'cursor' => ( 1 === $requests || 'loop' === $mode ) ? $cursor : '',
    ) );
}
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-client.php';
class Test_Client extends SSZ_Loyverse_Client {
    public function get_token() { return 'test-token'; }
}
$client = new Test_Client();
foreach ( array( 'QDJIhumT5QFYCQ=', 'opaque+/==', 'opaque%2B&value=?' ) as $cursor ) {
    $requests = 0;
    $mode = 'normal';
    $result = $client->get_all_variants();
    if ( is_wp_error( $result ) || count( $result ) !== 2 || $requests !== 2 ) {
        throw new RuntimeException( 'Complete pagination failed' );
    }
}
$cursor = 'loop+/=';
$requests = 0;
$mode = 'loop';
$result = $client->get_all_variants();
if ( ! is_wp_error( $result ) || $result->get_error_code() !== 'ssz_loyverse_cursor_loop' ) {
    throw new RuntimeException( 'Repeated cursor was not rejected' );
}
$original = 'QDJIhumT5QFYCQ==';
parse_str( parse_url( add_query_arg( array( 'cursor' => $original ), 'https://example.test/' ), PHP_URL_QUERY ), $old );
if ( $old['cursor'] === $original ) {
    throw new RuntimeException( 'Expected old WordPress URL construction to corrupt padding' );
}
echo "PASS: old URL builder removes cursor padding; corrected requests preserve opaque cursors, fetch all pages and reject loops.\n";
