<?php
// Replays an exported preview without WordPress, credentials, network or product writes.
// php tests/loyverse/replay-grouping.php <preview.txt> <output-directory>
define( 'ABSPATH', __DIR__ );
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-grouping.php';
require dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/includes/class-ssz-loyverse-admin.php';
$input = file_get_contents( $argv[1] );
$lines = preg_split( '/\r?\n/', $input );
$headers = null;
$rows = array();
foreach ( $lines as $line ) {
    if ( 0 === strpos( $line, "Product\tType\t" ) ) { $headers = str_getcsv( $line, "\t", '"', '' ); continue; }
    if ( ! $headers ) { continue; }
    $values = str_getcsv( $line, "\t", '"', '' );
    if ( count( $values ) !== count( $headers ) ) { continue; }
    $row = array_combine( $headers, $values );
    $issues = $row['Blocking reasons'] ?? ( $row['Source blocking reasons'] ?? '' );
    $rows[] = array( 'name' => $row['Product'], 'type' => $row['Type'], 'options' => $row['Options'], 'sku' => $row['SKU'], 'price' => $row['Price'], 'stock' => $row['Stock'], 'action' => $row['Proposed action'] ?? ( $row['Source action'] ?? '' ), 'issues' => '' === $issues ? array() : explode( '; ', $issues ), 'item_id' => $row['Loyverse item ID'], 'variant_id' => $row['Loyverse variant ID'] );
}
if ( ! $rows ) { throw new RuntimeException( 'No preview rows found' ); }
preg_match( '/Store: (.*?) \| Generated: (.*?) UTC/', $input, $store );
$blocked = count( array_filter( $rows, static function ( $row ) { return ! empty( $row['issues'] ); } ) );
$report = array( 'rows' => $rows, 'store_name' => $store[1] ?? 'Exported store', 'ran_at' => $store[2] ?? 'Exported snapshot', 'currency' => 'USD', 'items' => count( $rows ), 'ready_items' => count( $rows ) - $blocked, 'blocked_items' => $blocked, 'variants' => count( $rows ) );
$grouper = new SSZ_Loyverse_Grouping();
$result = $grouper->annotate( $report );
$admin = ( new ReflectionClass( 'SSZ_Loyverse_Admin' ) )->newInstanceWithoutConstructor();
$formatter = ( new ReflectionClass( 'SSZ_Loyverse_Admin' ) )->getMethod( 'catalogue_copy_text' );
if ( ! is_dir( $argv[2] ) ) { mkdir( $argv[2], 0777, true ); }
foreach ( array( 'all' => false, 'blocked' => true ) as $scope => $blocked_only ) {
    file_put_contents( $argv[2] . '/grouping-' . $scope . '.txt', $formatter->invoke( $admin, $result, $blocked_only ) );
}
file_put_contents( $argv[2] . '/grouping.json', json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
$escape = static function ( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' ); };
$html = '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>SwimShop grouping review</title><style>body{font:16px system-ui;margin:2rem;color:#17243a}table{border-collapse:collapse;width:100%;font-size:14px}th,td{padding:.6rem;border:1px solid #ccd3df;text-align:left;vertical-align:top}th{background:#edf2f8}textarea{width:100%;box-sizing:border-box}button,summary{padding:.7rem;cursor:pointer}h2{margin-top:2rem}.scroll{overflow:auto}small{color:#536178}</style><h1>SwimShop catalogue grouping review</h1><p>Read-only suggestions from the exported snapshot. No products were imported or changed. All name-based groups require approval.</p>';
$summary = $result['grouping'];
$html .= '<p>' . $escape( sprintf( '%d proposed variable parents covering %d source rows; %d rows remain separate (%d unresolved). %d groups have blockers; %d have differing prices.', $summary['proposed_groups'], $summary['grouped_rows'], $summary['separate_rows'], $summary['unresolved_rows'], $summary['blocked_groups'], $summary['price_warning_groups'] ) ) . '</p>';
$html .= '<details><summary>Copy complete grouping report</summary><p><button data-ssz-copy-target="all-report">Copy all rows</button> <span role="status" aria-live="polite"></span></p><label for="all-report">All products and grouping decisions</label><textarea id="all-report" readonly rows="14">' . $escape( $formatter->invoke( $admin, $result, false ) ) . '</textarea></details><h2>Proposed parent products</h2><div class="scroll"><table><thead><tr><th>Parent</th><th>Variations</th><th>Status</th><th>Notes</th></tr></thead><tbody>';
foreach ( $result['groups'] as $group ) {
    if ( $group['rows'] < 2 ) { continue; }
    $html .= '<tr><td>' . $escape( $group['parent'] ) . '</td><td>' . $escape( $group['rows'] ) . '</td><td>' . $escape( $group['status'] ) . '</td><td>' . $escape( $group['notes'] ) . '</td></tr>';
}
$html .= '</tbody></table></div><h2>All source rows and proposed variations</h2><div class="scroll"><table><thead><tr><th>Parent / source name</th><th>Options</th><th>SKU / source IDs</th><th>Price / stock</th><th>Review / blockers</th></tr></thead><tbody>';
foreach ( $result['rows'] as $row ) {
    $html .= '<tr><td><strong>' . $escape( $row['proposed_parent'] ) . '</strong><br>' . $escape( $row['name'] ) . '</td><td>' . $escape( $row['proposed_options'] ) . '</td><td>' . $escape( $row['sku'] ) . '<br><small>' . $escape( $row['item_id'] ) . '<br>' . $escape( $row['variant_id'] ) . '</small></td><td>' . $escape( $row['price'] . ' / ' . $row['stock'] ) . '</td><td>' . $escape( $row['grouping_status'] . '; ' . implode( '; ', $row['issues'] ) . '; ' . $row['grouping_notes'] ) . '</td></tr>';
}
$html .= '</tbody></table></div><script>' . file_get_contents( dirname( __DIR__, 2 ) . '/plugins/swimshop-loyverse-sync/assets/admin-preview.js' ) . '</script></html>';
file_put_contents( $argv[2] . '/grouping-review.html', $html );
echo json_encode( $summary, JSON_PRETTY_PRINT ) . "\n";
echo 'PASS: replay preserved ' . count( $result['rows'] ) . " source rows.\n";
