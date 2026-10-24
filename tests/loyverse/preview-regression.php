<?php
define( 'ABSPATH', __DIR__ );
class WP_Error {
    public $code;
    public function __construct( $code, $message ) { $this->code = $code; }
}
function is_wp_error( $v ) { return $v instanceof WP_Error; }
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-preview.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$planner = new SSZ_Loyverse_Preview();
$existing = array( 'skus' => array(), 'items' => array(), 'variants' => array() );
$variant = array( 'variant_id' => 'v1', 'sku' => '001', 'stores' => array( array( 'store_id' => 's1', 'available_for_sale' => true, 'pricing_type' => 'FIXED', 'price' => 0 ) ) );
$item = array( 'id' => 'i1', 'item_name' => 'Swim goggles', 'track_stock' => true, 'variants' => array( $variant ) );
$levels = array( array( 'store_id' => 's1', 'variant_id' => 'v1', 'in_stock' => 0 ) );
$before = serialize( array( $item, $levels, $existing ) );
$result = $planner->build( array( $item ), $levels, 's1', $existing );
check( $result['ready_items'] === 1 && $result['simple'] === 1, 'Valid zero-price zero-stock item must be ready' );
check( $result['rows'][0]['sku'] === '001', 'Preserve leading SKU zeroes' );
check( serialize( array( $item, $levels, $existing ) ) === $before, 'Preview mutated input' );
foreach ( array( null, 'bad', -1, 1.5, true, INF ) as $quantity ) {
    $bad = $levels;
    $bad[0]['in_stock'] = $quantity;
    check( $planner->build( array( $item ), $bad, 's1', $existing )['blocked_items'] === 1, 'Unsafe quantity must block' );
}
check( $planner->build( array( $item ), array(), 's1', $existing )['blocked_items'] === 1, 'Missing stock must not become zero' );
check( $planner->build( array( $item ), array_merge( $levels, $levels ), 's1', $existing )['blocked_items'] === 1, 'Duplicate stock rows must block' );
$collision = $existing;
$collision['skus']['001'] = array( 22 );
check( $planner->build( array( $item ), $levels, 's1', $collision )['blocked_items'] === 1, 'Existing SKU must not be adopted' );
$linked = $existing;
$linked['items']['i1'] = array( 22 );
check( $planner->build( array( $item ), $levels, 's1', $linked )['blocked_items'] === 1, 'Linked item must not be created again' );
$variable = $item;
$variable['option1_name'] = 'Size';
$variable['variants'][0]['option1_value'] = 'Small';
$second = $variant;
$second['variant_id'] = 'v2';
$second['sku'] = '002';
$second['option1_value'] = 'Large';
$variable['variants'][] = $second;
$both_levels = array_merge( $levels, array( array( 'store_id' => 's1', 'variant_id' => 'v2', 'in_stock' => 7 ) ) );
$result = $planner->build( array( $variable ), $both_levels, 's1', $existing );
check( $result['variable'] === 1 && $result['variants'] === 2 && $result['ready_items'] === 1, 'Group variations under one item' );
$variable['variants'][1]['option1_value'] = 'Small';
check( $planner->build( array( $variable ), $both_levels, 's1', $existing )['blocked_items'] === 1, 'Duplicate variation options must block parent' );
$variable['variants'][1]['option1_value'] = 'Large';
$variable['variants'][1]['sku'] = '001';
check( $planner->build( array( $variable ), $both_levels, 's1', $existing )['blocked_items'] === 1, 'Duplicate source SKU must block parent' );
$untracked = $item;
$untracked['track_stock'] = false;
check( $planner->build( array( $untracked ), array(), 's1', $existing )['ready_items'] === 1, 'Untracked product must not require stock rows' );
$unavailable = $item;
$unavailable['variants'][0]['stores'][0]['store_id'] = 'other';
check( $planner->build( array( $unavailable ), $levels, 's1', $existing )['blocked_items'] === 1, 'Wrong store availability must block' );
check( is_wp_error( $planner->build( array( array( 'id' => 'broken' ) ), $levels, 's1', $existing ) ), 'Invalid schema must fail whole preview' );
echo "PASS: read-only planner, grouping, zero values, IDs, SKU conflicts, duplicate options, store filtering and invalid/missing quantities.\n";
