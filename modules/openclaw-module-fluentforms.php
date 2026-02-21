<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentForms module for JRB Remote Site API.
 */
class JRB_Remote_FluentForms_Module {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route( 'jrb-remote/v1', '/fluentforms/forms', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'list_forms' ),
				'permission_callback' => array( 'JRB_Remote_Module_Loader', 'verify_token' ),
			),
		) );
	}

	public static function list_forms( $request ) {
		global $wpdb;

		// Check table existence with caching.
		$table_name   = $wpdb->prefix . 'fluentform_forms';
		$table_exists = wp_cache_get( 'fluentform_forms_exists', 'jrb_remote_api' );
		if ( false === $table_exists ) {
			$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
			wp_cache_set( 'fluentform_forms_exists', $table_exists, 'jrb_remote_api', 3600 );
		}

		if ( ! $table_exists ) {
			return new WP_REST_Response( array( 'error' => 'FluentForms table not found' ), 404 );
		}

		// Use literal table name for the specific plugin.
		$results = $wpdb->get_results( "SELECT id, title, created_at FROM {$wpdb->prefix}fluentform_forms ORDER BY created_at DESC" );

		return new WP_REST_Response( array( 'data' => $results ), 200 );
	}

	/**
	 * Securely get the user IP address.
	 * 
	 * @return string
	 */
	public static function get_user_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		}
		return (string) $ip;
	}
}
