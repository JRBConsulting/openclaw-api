<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentSupport module for JRB Remote Site API.
 */
class JRB_Remote_FluentSupport_Module {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'jrb-remote/v1', '/fluentsupport/tickets', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_tickets' ),
				'permission_callback' => array( 'JRB_Remote_Module_Loader', 'verify_token' ),
			),
		) );
	}

	public static function list_tickets( $request ) {
		global $wpdb;

		$per_page = (int) ( $request->get_param( 'per_page' ) ?: 20 );
		$page     = (int) ( $request->get_param( 'page' ) ?: 1 );
		$offset   = ( $page - 1 ) * $per_page;
		$status   = sanitize_text_field( $request->get_param( 'status' ) );

		// Check table existence with caching.
		$table_exists = wp_cache_get( 'fs_tickets_exists', 'jrb_remote_api' );
		if ( false === $table_exists ) {
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . 'fs_tickets' ) );
			wp_cache_set( 'fs_tickets_exists', $table_exists, 'jrb_remote_api', 3600 );
		}

		if ( ! $table_exists ) {
			return new WP_REST_Response( array( 'error' => 'FluentSupport table not found' ), 404 );
		}

		if ( ! empty( $status ) ) {
			$total = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}fs_tickets WHERE status = %s",
				$status
			) );
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fs_tickets WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$status,
				$per_page,
				$offset
			) );
		} else {
			$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}fs_tickets" );
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fs_tickets ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			) );
		}

		return new WP_REST_Response( array(
			'data' => $results,
			'meta' => array(
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
			),
		), 200 );
	}
}
