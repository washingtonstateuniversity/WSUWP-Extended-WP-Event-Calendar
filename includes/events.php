<?php

namespace WSU\Events;

add_filter( 'register_post_type_args', 'WSU\Events\make_public', 10, 2 );
add_filter( 'wp_event_calendar_manage_posts_columns', 'WSU\Events\filter_posts_columns', 10, 2 );

/**
 * Allows public views of events.
 *
 * @since 0.0.1
 *
 * @param array  $args      Arguments for registering a post type.
 * @param string $post_type Post type key.
 *
 * @return array
 */
function make_public( $args, $post_type ) {
	if ( 'event' === $post_type ) {
		$args['exclude_from_search'] = false;
		$args['publicly_queryable'] = true;
		$args['has_archive'] = true;
		$args['show_in_rest'] = true;
	}

	return $args;
}

/**
 * Unsets the "Repeats" column from the events list table.
 *
 * @since 0.0.1
 *
 * @param array $new_columns Columns as modified by WP Event Calendar.
 * @param array $old_columns Default columns.
 *
 * @return array
 */
function filter_posts_columns( $new_columns, $old_columns ) {
	unset( $new_columns['repeat'] );

	return $new_columns;
}
