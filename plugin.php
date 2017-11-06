<?php
/*
Plugin Name: WSUWP Extended WP Event Calendar
Version: 0.0.1
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
	include_once __DIR__ . '/includes/events.php';
}
