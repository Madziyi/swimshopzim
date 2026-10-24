<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSZ_Loyverse_Client {
    const BASE_URL = 'https://api.loyverse.com/v1.0';

    public function has_token() {
        return '' !== $this->get_token();
    }

    public function get_token() {
        $token = get_option( SSZ_Loyverse_Admin::OPTION_TOKEN, '' );
        return is_string( $token ) ? trim( $token ) : '';
    }

    public function get_stores() {
        $response = $this->request( '/stores' );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return isset( $response['stores'] ) && is_array( $response['stores'] ) ? $response['stores'] : array();
    }

    public function get_all_variants() {
        return $this->get_all_pages( '/variants', 'variants', array( 'limit' => 50 ) );
    }

    public function get_all_items() {
        return $this->get_all_pages( '/items', 'items', array( 'limit' => 50 ) );
    }

    public function get_inventory_for_store( $store_id ) {
        return $this->get_all_pages(
            '/inventory',
            'inventory_levels',
            array(
                'limit'     => 50,
                'store_ids' => $store_id,
            )
        );
    }

    /**
     * Check both read endpoints independently, without changing any stock.
     * Keep only the status/result count in the diagnostic report.
     */
    public function diagnose_reads( $store_id ) {
        $checks = array(
            'variants'  => array( '/variants', array( 'limit' => 1 ), 'variants' ),
            'inventory' => array( '/inventory', array( 'limit' => 1, 'store_ids' => $store_id ), 'inventory_levels' ),
        );
        $results = array();
        foreach ( $checks as $key => $check ) {
            $response = $this->request( $check[0], $check[1] );
            if ( is_wp_error( $response ) ) {
                $results[ $key ] = array( 'ok' => false, 'message' => $response->get_error_message() );
            } elseif ( ! isset( $response[ $check[2] ] ) || ! is_array( $response[ $check[2] ] ) ) {
                $results[ $key ] = array( 'ok' => false, 'message' => 'Unexpected API response: missing ' . $check[2] . ' array.' );
            } else {
                $results[ $key ] = array( 'ok' => true, 'message' => 'Read succeeded.' );
            }
        }
        return $results;
    }

    private function get_all_pages( $path, $collection_key, $query ) {
        $items       = array();
        $cursor      = '';
        $seen_cursor = array();

        for ( $page = 0; $page < 100; $page++ ) {
            $page_query = $query;
            if ( $cursor ) {
                $page_query['cursor'] = $cursor;
            }

            $response = $this->request( $path, $page_query );
            if ( is_wp_error( $response ) ) {
                return $response;
            }
            if ( ! isset( $response[ $collection_key ] ) || ! is_array( $response[ $collection_key ] ) ) {
                return new WP_Error( 'ssz_loyverse_unexpected_schema',
                    sprintf( 'GET %s: expected the %s response array but Loyverse did not return it.', $path, $collection_key )
                );
            }
            $items = array_merge( $items, $response[ $collection_key ] );
            $next_cursor = isset( $response['cursor'] ) && is_string( $response['cursor'] ) ? $response['cursor'] : '';
            if ( ! $next_cursor ) {
                return $items;
            }
            if ( isset( $seen_cursor[ $next_cursor ] ) || $next_cursor === $cursor ) {
                return new WP_Error( 'ssz_loyverse_cursor_loop', sprintf( 'GET %s: pagination returned a repeated cursor; no stock changed.', $path ) );
            }
            $seen_cursor[ $next_cursor ] = true;
            $cursor = $next_cursor;
        }

        return new WP_Error( 'ssz_loyverse_pagination_limit', sprintf( 'GET %s: exceeded 100 pages; refusing to sync incomplete inventory.', $path ) );
    }

    private function request( $path, $query = array() ) {
        $token = $this->get_token();
        if ( ! $token ) {
            return new WP_Error( 'ssz_loyverse_missing_token', __( 'No Loyverse API access token is configured.', 'swimshop-loyverse-sync' ) );
        }

        $url = self::BASE_URL . '/' . ltrim( $path, '/' );
        if ( $query ) {
            // Cursors are opaque. Encode once so +, / and = survive URL parsing.
            // add_query_arg expects newly supplied values to be URL encoded.
            $url .= '?' . http_build_query( $query, '', '&', PHP_QUERY_RFC3986 );
        }

        $response = wp_remote_get(
            $url,
            array(
                'headers'     => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ),
                'timeout'     => 25,
                'redirection' => 3,
                'user-agent'  => 'SwimShop-Loyverse-Sync/' . SSZ_LOYVERSE_SYNC_VERSION . '; ' . home_url( '/' ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'ssz_loyverse_transport', __( 'Could not reach Loyverse.', 'swimshop-loyverse-sync' ) . ' ' . $response->get_error_message() );
        }

        $status = (int) wp_remote_retrieve_response_code( $response );
        $body   = wp_remote_retrieve_body( $response );
        $data   = json_decode( $body, true );

        if ( $status < 200 || $status >= 300 ) {
            $message = __( 'Loyverse returned an API error.', 'swimshop-loyverse-sync' );

            if ( is_array( $data ) ) {
                if ( ! empty( $data['message'] ) && is_string( $data['message'] ) ) {
                    $message = sanitize_text_field( $data['message'] );
                } elseif ( ! empty( $data['error'] ) && is_string( $data['error'] ) ) {
                    $message = sanitize_text_field( $data['error'] );
                }
            }

            // Loyverse documents an errors[] list with code, details and field.
            // The v0.1.0 client ignored this format and hid actionable HTTP 400 reasons.
            if ( is_array( $data ) && ! empty( $data['errors'] ) && is_array( $data['errors'] ) ) {
                $parts = array();
                foreach ( array_slice( $data['errors'], 0, 3 ) as $error ) {
                    if ( ! is_array( $error ) ) {
                        continue;
                    }
                    $code   = isset( $error['code'] ) && is_scalar( $error['code'] ) ? (string) $error['code'] : '';
                    $detail = isset( $error['details'] ) && is_scalar( $error['details'] ) ? (string) $error['details'] : '';
                    $field  = isset( $error['field'] ) && is_scalar( $error['field'] ) ? (string) $error['field'] : '';
                    $part = trim( $code . ( $field ? ' [' . $field . ']' : '' ) . ( $detail ? ': ' . $detail : '' ) );
                    if ( $part ) {
                        $parts[] = $part;
                    }
                }
                if ( $parts ) {
                    $message = implode( '; ', $parts );
                }
            }

            // Ensure credentials never appear in notices, redirect URLs or logs.
            $message = sanitize_text_field( $message );
            $message = str_replace( $token, '[redacted]', $message );
            $message = preg_replace( '/Bearer\s+\S+/i', 'Bearer [redacted]', $message );
            $message = substr( $message, 0, 450 );
            return new WP_Error( 'ssz_loyverse_http_' . $status, sprintf( 'GET %s — Loyverse HTTP %d: %s', $path, $status, $message ) );
        }

        if ( ! is_array( $data ) ) {
            return new WP_Error( 'ssz_loyverse_invalid_json', __( 'Loyverse returned an unreadable response.', 'swimshop-loyverse-sync' ) );
        }

        return $data;
    }
}
