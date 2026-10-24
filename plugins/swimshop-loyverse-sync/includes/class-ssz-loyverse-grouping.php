<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Name-based suggestions only. Never authorizes an import or replaces source identity. */
class SSZ_Loyverse_Grouping {
    public function annotate( $report ) {
        $buckets = array();
        foreach ( $report['rows'] as $index => $row ) {
            if ( 'Variable' === $row['type'] ) {
                // Preserve structured Loyverse variations; never merge these by title.
                $parsed = array( 'parent' => $row['name'], 'options' => $row['options'], 'note' => 'Structured Loyverse variation; source item grouping retained.' );
                $key = 'source:' . $row['item_id'];
            } else {
                $parsed = $this->parse_name( $row['name'] );
                $key = $parsed['options'] ? 'name:' . $this->normalize( $parsed['parent'] ) : 'separate:' . $index;
            }
            $report['rows'][ $index ]['proposed_parent'] = $parsed['parent'];
            $report['rows'][ $index ]['proposed_options'] = $parsed['options'];
            $report['rows'][ $index ]['grouping_notes'] = $parsed['note'];
            $buckets[ $key ][] = $index;
        }

        $summary = array( 'proposed_groups' => 0, 'grouped_rows' => 0, 'separate_rows' => 0, 'unresolved_rows' => 0, 'blocked_groups' => 0, 'price_warning_groups' => 0 );
        $report['groups'] = array();
        $ordered_rows = array();
        foreach ( $buckets as $key => $indices ) {
            $first = $report['rows'][ $indices[0] ];
            $structured = 0 === strpos( $key, 'source:' );
            $grouped = $structured || count( $indices ) > 1;
            $warnings = array();
            $blocked = false;
            $options = $dimensions = $prices = $tracking = array();
            foreach ( $indices as $index ) {
                $row = $report['rows'][ $index ];
                $combination = $this->normalize( $row['proposed_options'] );
                $options[ $combination ] = ( $options[ $combination ] ?? 0 ) + 1;
                $dimension = preg_replace( '/: [^|]*(?:\| |$)/', '|', $row['proposed_options'] );
                $dimensions[ $dimension ] = true;
                if ( is_numeric( $row['price'] ) ) { $prices[ (string) (float) $row['price'] ] = true; }
                $tracking[ 'Not tracked' === $row['stock'] ? 'untracked' : 'tracked' ] = true;
                if ( ! empty( $row['issues'] ) ) { $blocked = true; }
            }
            if ( $grouped && ! $structured && preg_match( '/^(?:HXBY|YF|ARENA|SPURT|SPEEDO|TYR)\s+(?:BOYS|GIRLS|MEN|WOMEN|MENS|WOMENS|JUNIOR|KIDS)$/', $this->normalize( $first['proposed_parent'] ) ) ) {
                $warnings[] = 'Generic brand/gender name lacks a product model; verify family boundaries';
                $blocked = true;
            }
            if ( $grouped && max( $options ) > 1 ) { $warnings[] = 'Duplicate size/colour combination across source records'; $blocked = true; }
            if ( $grouped && count( $dimensions ) > 1 ) { $warnings[] = 'Some variations are missing a size or colour dimension'; $blocked = true; }
            if ( $grouped && count( $prices ) > 1 ) { $warnings[] = 'Prices differ between variations; confirm per-variation pricing'; $summary['price_warning_groups']++; }
            if ( $grouped && count( $tracking ) > 1 ) { $warnings[] = 'Mixed tracked/untracked stock; availability policy needs review'; }
            if ( $blocked ) { $warnings[] = 'Source or variation issues must be resolved before import'; }
            if ( $grouped ) {
                $summary['proposed_groups']++;
                $summary['grouped_rows'] += count( $indices );
                if ( $blocked ) { $summary['blocked_groups']++; }
                $status = $blocked ? 'Blocked group — review' : ( $structured ? 'Source group — review' : 'Proposed group — approval required' );
            } else {
                $summary['separate_rows']++;
                $unresolved = '' !== $first['proposed_options'] || '' !== $first['grouping_notes'];
                if ( $unresolved ) { $summary['unresolved_rows']++; }
                $status = $unresolved ? 'Unresolved — keep separate pending review' : 'Keep as separate simple product';
            }
            // This key is only a review reference, not an approved future parent mapping.
            $reference = 'proposal-' . substr( hash( 'sha256', $key ), 0, 12 );
            $report['groups'][] = array( 'reference' => $reference, 'parent' => $first['proposed_parent'], 'rows' => count( $indices ), 'status' => $status, 'notes' => implode( '; ', $warnings ) );
            foreach ( $indices as $index ) {
                $report['rows'][ $index ]['group_reference'] = $reference;
                $report['rows'][ $index ]['grouping_status'] = $status;
                $report['rows'][ $index ]['grouping_notes'] = implode( '; ', array_filter( array( $report['rows'][ $index ]['grouping_notes'], implode( '; ', $warnings ) ) ) );
                if ( empty( $report['rows'][ $index ]['issues'] ) ) {
                    $report['rows'][ $index ]['action'] = 'Passes source checks — import pending review';
                }
                $ordered_rows[] = $report['rows'][ $index ];
            }
        }
        $report['grouping'] = $summary;
        $report['rows'] = $ordered_rows;
        return $report;
    }

    public function parse_name( $name ) {
        $parent = trim( preg_replace( '/\s+/u', ' ', $name ) );
        $attributes = array();
        $notes = array();
        // Two passes handle both "Bikini S26 Black" and "Bikini Black S26".
        for ( $pass = 0; $pass < 2; $pass++ ) {
            if ( ! isset( $attributes['Size'] ) ) {
                $size = $this->extract_size( $parent );
                if ( $size ) { $parent = $size[0]; $attributes['Size'] = $size[1]; }
            }
            if ( ! isset( $attributes['Colour'] ) ) {
                $colour = $this->extract_colour( $parent );
                if ( $colour ) { $parent = $colour[0]; $attributes['Colour'] = $colour[1]; }
            }
        }
        if ( '' === $parent ) { return array( 'parent' => $name, 'options' => '', 'note' => 'Name could not be split safely' ); }
        if ( preg_match( '/(?:\bS?\d{1,3}(?:JR)?\b|\b(?:MEDUIM|SMALL|MEDIUM|LARGE)\b|\([^)]*\))$/i', $parent ) ) {
            $notes[] = 'Unparsed size, model number or parenthesized text retained in parent name';
        }
        $options = array();
        foreach ( array( 'Size', 'Colour' ) as $key ) {
            if ( isset( $attributes[ $key ] ) ) { $options[] = $key . ': ' . $attributes[ $key ]; }
        }
        return array( 'parent' => $parent, 'options' => implode( ' | ', $options ), 'note' => implode( '; ', $notes ) );
    }

    private function extract_size( $name ) {
        $apparel = preg_match( '/(?:SWIM|SKIN|BRIEF|BIKINI|WAIST|PARKA|SHIRT|JACKET|PANT|SHORT|SUIT|VEST|PONCHO|TOWEL)/i', $name );
        $footwear = preg_match( '/(?:FINS?|FLIP\s*FLOPS?|SHOES?|SANDALS?)/i', $name );
        $patterns = array(
            '/^(.*?)\s*(?:\bS\s*\(\s*(\d{1,2}(?:JR|JNR)?)\s*\)|\(\s*(S?\d{1,2}(?:JR|JNR)?)\s*\)|\b(S\d{1,2}(?:JR|JNR)?))$/i',
            '/^(.*?)\s+(?:\(\s*)?((?:[2-5]X[SL]|XXXS|XXS|XXXL|XXL|XS|XL|S|M|L))(?:\s*\))?$/i',
        );
        if ( $footwear ) { array_unshift( $patterns, '/^(.*?)\s*(?:\(\s*)?(\d{2}\s*\/\s*\d{2})(?:\s*\))?$/i' ); }
        if ( $apparel ) {
            $patterns[] = '/^(.*?)\s+(?:\(\s*)?(\d{1,2}\s*-\s*\d{1,2})(?:\s*\))?$/i';
            $patterns[] = '/^(.*?)\s+(\d{2}(?:JR|JNR)?)$/i';
            $patterns[] = '/^(.*?)\s+(?:\(\s*)?(SMALL|MEDIUM|LARGE)(?:\s*\))?$/i';
        }
        foreach ( $patterns as $pattern ) {
            if ( preg_match( $pattern, $name, $match ) && '' !== trim( $match[1] ) ) {
                $value = '';
                foreach ( array_slice( $match, 2 ) as $part ) { if ( '' !== $part ) { $value = $part; break; } }
                $value = strtoupper( preg_replace( '/\s+/', '', $value ) );
                $value = preg_replace( '/^S(?=\d)/', '', $value );
                $value = str_replace( 'JNR', 'JR', $value );
                $aliases = array( 'SMALL' => 'S', 'MEDIUM' => 'M', 'LARGE' => 'L', 'XXS' => '2XS', 'XXXS' => '3XS', 'XXL' => '2XL', 'XXXL' => '3XL' );
                return array( trim( $match[1] ), $aliases[ $value ] ?? $value );
            }
        }
        return null;
    }

    private function extract_colour( $name ) {
        $colours = array( 'SHOCKING PINK', 'ROYAL BLUE', 'DARK NAVY', 'LIGHT PINK', 'FLUO YELLOW', 'BLUE COSMO', 'NAVY WATER', 'RED ONION', 'SKY BLUE', 'NEON BLUE', 'ANTHRACITE', 'TURQUOISE', 'FUCHSIA', 'FUSCHIA', 'EMERALD', 'PEACOCK', 'PURPLE', 'SILVER', 'ORANGE', 'YELLOW', 'VIOLET', 'COPPER', 'BLACK', 'WHITE', 'GREEN', 'ROYAL', 'SMOKE', 'CLEAR', 'CORAL', 'GREY', 'GRAY', 'NAVY', 'BLUE', 'PINK', 'GOLD', 'AQUA', 'SAGE', 'LIME', 'RED' );
        $token = '(?:' . implode( '|', $colours ) . ')';
        $sequence = $token . '(?:(?:\s*[/\-]\s*|\s+)' . $token . ')*';
        if ( preg_match( '~^(.*?)(?:\s*\(\s*(' . $sequence . ')\s*\)|\s+(' . $sequence . '))$~i', $name, $match ) && '' !== trim( $match[1] ) ) {
            $colour = ! empty( $match[2] ) ? $match[2] : $match[3];
            return array( trim( $match[1] ), $this->normalize( preg_replace( '/\s*([\/\-])\s*/', '$1', $colour ) ) );
        }
        return null;
    }

    private function normalize( $value ) {
        return strtoupper( trim( preg_replace( '/\s+/u', ' ', $value ) ) );
    }
}
