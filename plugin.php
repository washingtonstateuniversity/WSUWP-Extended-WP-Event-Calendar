<?php
/*
Plugin Name: WSUWP Extended WP Event Calendar
Version: 0.0.1
Description: WSU extensions of WP Event Calendar
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu/
Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Extended-WP-Event-Calendar
*/

namespace WSU\WPEventCalendar;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'plugins_loaded', 'WSU\WPEventCalendar\bootstrap' );
/**
 * Loads the rest of the WSUWP Extended WP Event Calendar.
 *
 * @since 0.0.1
 */
function bootstrap() {}
