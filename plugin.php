<?php
/*
Plugin Name: WSUWP Extended WP Event Calendar
Version: 0.1.9
Description: WSU extensions of WP Event Calendar
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu/
Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Extended-WP-Event-Calendar
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin uses namespaces and requires PHP 5.3 or greater.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>' . esc_html__( 'WSUWP Extended WP Event Calendar requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.', 'wsuwp-extended-wp-event-calendar' ) . '</p></div>';
	} );
	return;
} else {
	// Load WSUWP's implementation of CMB2.
	if ( function_exists( 'WSUWP\CMB2\init' ) ) {
		WSUWP\CMB2\init();
	}

	// Bail if CMB2 is not yet available.
	if ( ! class_exists( 'CMB2_Bootstrap_230', false ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>' . esc_html__( 'WSUWP Extended WP Event Calendar requires CMB2 to function properly. Please ensure this plugin is activated.', 'wsuwp-extended-wp-event-calendar' ) . '</p></div>';
		} );
		return;
	}

	include_once __DIR__ . '/includes/events.php';
	include_once __DIR__ . '/includes/venues.php';
	include_once __DIR__ . '/includes/taxonomies.php';
	include_once __DIR__ . '/includes/meta-data.php';
	include_once __DIR__ . '/includes/wp-api.php';
}
