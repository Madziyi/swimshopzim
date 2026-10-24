<?php
define( 'ABSPATH', __DIR__ );
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-grouping.php';
function check_group( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
$grouper = new SSZ_Loyverse_Grouping();
foreach ( array(
    'Team T-shirt White M' => array( 'Team T-shirt', 'Size: M | Colour: WHITE' ),
    'Team T-shirt Navy 14-15' => array( 'Team T-shirt', 'Size: 14-15 | Colour: NAVY' ),
    'Arena bikini S26 Black' => array( 'Arena bikini', 'Size: 26 | Colour: BLACK' ),
    'Team Hooded Jacket (BLACK) 2XL' => array( 'Team Hooded Jacket', 'Size: 2XL | Colour: BLACK' ),
    'ARENA TEAM SWIM BRIEFS BRANDED ZIMBABWE S(32)' => array( 'ARENA TEAM SWIM BRIEFS BRANDED ZIMBABWE', 'Size: 32' ),
    'BOYS BOLD SWIM BRIEFS NEON/BLUE S26JR' => array( 'BOYS BOLD SWIM BRIEFS NEON/BLUE', 'Size: 26JR' ),
    'W ARENA ONE GRAPHIC PRO SWIMSUIT (BLUECOSMO)S40' => array( 'W ARENA ONE GRAPHIC PRO SWIMSUIT (BLUECOSMO)', 'Size: 40' ),
    'TRAINING FINS (35/36)' => array( 'TRAINING FINS', 'Size: 35/36' ),
    'Parka Black (11-12)' => array( 'Parka', 'Size: 11-12 | Colour: BLACK' ),
    'ARENA PYTHON SILVER/BLACK' => array( 'ARENA PYTHON', 'Colour: SILVER/BLACK' ),
    'SPURT CAP ROYAL BLUE' => array( 'SPURT CAP', 'Colour: ROYAL BLUE' ),
    'HXBY Boys Grey/Royal 3XL' => array( 'HXBY Boys', 'Size: 3XL | Colour: GREY/ROYAL' ),
    'Plain Towel (Medium)' => array( 'Plain Towel', 'Size: M' ),
    'SPURT JUNIOR' => array( 'SPURT JUNIOR', '' ),
    'ARENA SWIM SNORKEL PRO III' => array( 'ARENA SWIM SNORKEL PRO III', '' ),
    'TAUPOC girls Blue S(L/30)' => array( 'TAUPOC girls Blue S(L/30)', '' ),
    'Arena Parka Meduim' => array( 'Arena Parka Meduim', '' ),
) as $name => $expected ) {
    $parsed = $grouper->parse_name( $name );
    check_group( $parsed['parent'] === $expected[0] && $parsed['options'] === $expected[1], 'Wrong parse: ' . $name . ' => ' . json_encode( $parsed ) );
}
function source_row( $name, $sku, $price = '30', $issues = array() ) {
    return array( 'name' => $name, 'sku' => $sku, 'item_id' => 'item-' . $sku, 'variant_id' => 'variant-' . $sku, 'type' => 'Simple', 'options' => '', 'price' => $price, 'stock' => '2', 'issues' => $issues, 'action' => $issues ? 'Blocked' : 'Would create and publish' );
}
$source = array( 'rows' => array(
    source_row( 'Team T-shirt White M', '001' ),
    source_row( 'TEAM T-SHIRT Navy L', '002', '35' ),
    source_row( 'Women Team T-shirt White M', '003' ),
    source_row( 'Team T-shirt White M', '004' ),
    source_row( 'ARENA SWIM SNORKEL III', '005' ),
    source_row( 'ARENA SWIM SNORKEL PRO III', '006' ),
    source_row( 'Parka Black S', '007' ),
    source_row( 'Parka Navy M', '008', '40', array( 'Negative stock' ) ),
) );
$before = serialize( $source );
$result = $grouper->annotate( $source );
check_group( serialize( $source ) === $before, 'Grouping mutated source report' );
check_group( count( $result['rows'] ) === 8 && $result['grouping']['proposed_groups'] === 2, 'Wrong grouping count or lost rows' );
check_group( $result['grouping']['blocked_groups'] === 2, 'Duplicate options and source blockers must block entire groups' );
check_group( $result['grouping']['price_warning_groups'] === 2, 'Price differences need review' );
$actual = array_column( $result['rows'], 'sku' );
sort( $actual );
check_group( $actual === array( '001', '002', '003', '004', '005', '006', '007', '008' ), 'Lost or duplicated source SKU' );
check_group( $result['rows'][0]['grouping_status'] === 'Blocked group — review' && str_contains( $result['rows'][0]['grouping_notes'], 'Duplicate size/colour' ), 'Duplicate options not surfaced' );
$valid = $grouper->annotate( array( 'rows' => array( source_row( 'Parka Black S', '01' ), source_row( 'Parka Navy M', '02' ) ) ) );
check_group( $valid['grouping']['proposed_groups'] === 1 && $valid['grouping']['blocked_groups'] === 0, 'Valid proposal should await review' );
check_group( $valid['rows'][0]['grouping_status'] === 'Proposed group — approval required', 'Inferred group must not be silently approved' );
$dimensions = $grouper->annotate( array( 'rows' => array( source_row( 'Parka Black S', '01' ), source_row( 'Parka Navy', '02' ) ) ) );
check_group( $dimensions['grouping']['blocked_groups'] === 1, 'Missing dimension must block group' );
$generic = $grouper->annotate( array( 'rows' => array( source_row( 'HXBY Boys Black S', '01' ), source_row( 'HXBY Boys Navy M', '02' ) ) ) );
check_group( $generic['grouping']['blocked_groups'] === 1 && str_contains( $generic['rows'][0]['grouping_notes'], 'lacks a product model' ), 'Generic names must need family review' );
$singleton = $grouper->annotate( array( 'rows' => array( source_row( 'Arena Parka Meduim', '01' ) ) ) );
check_group( $singleton['grouping']['unresolved_rows'] === 1 && str_contains( $singleton['rows'][0]['proposed_parent'], 'Meduim' ), 'Do not silently correct misspelled names' );
$structured = source_row( 'Native item', 'native' );
$structured['type'] = 'Variable';
$structured['options'] = 'Size: Large';
$native = $grouper->annotate( array( 'rows' => array( $structured, source_row( 'Native item Large', 'separate' ) ) ) );
check_group( $native['rows'][0]['grouping_status'] === 'Source group — review', 'Preserve native source group' );
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-admin.php';
$admin_class = new ReflectionClass( 'SSZ_Loyverse_Admin' );
$formatter = $admin_class->getMethod( 'catalogue_copy_text' );
$admin = $admin_class->newInstanceWithoutConstructor();
$result += array( 'store_name' => 'Fixture', 'ran_at' => 'Fixture time', 'currency' => 'USD', 'items' => 8, 'ready_items' => 7, 'blocked_items' => 1, 'variants' => 8 );
$copied = $formatter->invoke( $admin, $result, true );
check_group( str_contains( $copied, '5 variant rows included' ), 'Blocked copy must include every member of blocked groups' );
check_group( str_contains( $copied, 'Duplicate size/colour' ) && str_contains( $copied, 'variant-001' ) && ! str_contains( $copied, 'variant-005' ), 'Copy must expose grouping blockers and source identity, while excluding unrelated rows' );
echo "PASS: name parsing, conservative family boundaries, source identity, complete row coverage, native variants, duplicate options, price review, source blockers and missing dimensions.\n";
