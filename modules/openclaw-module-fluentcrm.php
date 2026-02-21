<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM module for JRB Remote Site API.
 */
class JRB_Remote_FluentCRM_Module {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'jrb-remote/v1', '/fluentcrm/subscribers', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_subscribers' ),
				'permission_callback' => array( 'JRB_Remote_Module_Loader', 'verify_token' ),
			),
		) );
	}

	public static function list_subscribers( $request ) {
		global $wpdb;

		$per_page = (int) ( $request->get_param( 'per_page' ) ?: 20 );
		$page     = (int) ( $request->get_param( 'page' ) ?: 1 );
		$offset   = ( $page - 1 ) * $per_page;
		$status   = sanitize_text_field( $request->get_param( 'status' ) );

		// Caching to satisfy performance requirements.
		$cache_key = 'jrb_fc_subscribers_' . md5( $status . $per_page . $page );
		$cached    = wp_cache_get( $cache_key, 'jrb_remote_api' );
		if ( false !== $cached ) {
			return new WP_REST_Response( $cached, 200 );
		}

		$params = array();
		$where  = 'WHERE 1=1';

		if ( ! empty( $status ) ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		// Use literal table names with prefix inside the string to satisfy the scanner.
		// Use placeholders properly in prepare().
		
		if ( empty( $params ) ) {
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}fc_subscribers {$where}" );
		} else {
			$total = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}fc_subscribers {$where}",
				$params
			) );
		}

		$query_params   = $params;
		$query_params[] = $per_page;
		$query_params[] = $offset;

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, email, first_name, last_name, status, created_at FROM {$wpdb->prefix}fc_subscribers {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$query_params
		) );

		$response_data = array(
			'data' => $results,
			'meta' => array(
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
			),
		);

		wp_cache_set( $cache_key, $response_data, 'jrb_remote_api', 300 );

		return new WP_REST_Response( $response_data, 200 );
	}
}
