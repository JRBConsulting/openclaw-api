<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCommunity module for JRB Remote Site API.
 */
class JRB_Remote_FluentCommunity_Module {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'jrb-remote/v1', '/fluentcommunity/members', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_members' ),
				'permission_callback' => array( 'JRB_Remote_Module_Loader', 'verify_token' ),
			),
		) );
	}

	public static function list_members( $request ) {
		global $wpdb;

		$per_page = (int) ( $request->get_param( 'per_page' ) ?: 20 );
		$page     = (int) ( $request->get_param( 'page' ) ?: 1 );
		$offset   = ( $page - 1 ) * $per_page;

		// Check table existence with caching.
		$table_exists = wp_cache_get( 'fcom_members_exists', 'jrb_remote_api' );
		if ( false === $table_exists ) {
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . 'fcom_members' ) );
			wp_cache_set( 'fcom_members_exists', $table_exists, 'jrb_remote_api', 3600 );
		}

		if ( ! $table_exists ) {
			return new WP_REST_Response( array( 'error' => 'FluentCommunity table not found' ), 404 );
		}

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}fcom_members" );

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT id, user_id, name, created_at FROM {$wpdb->prefix}fcom_members ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		) );

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
