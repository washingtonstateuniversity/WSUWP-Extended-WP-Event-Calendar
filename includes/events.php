<?php

namespace WSU\Events;

add_filter( 'register_post_type_args', 'WSU\Events\make_public', 10, 2 );
add_filter( 'wp_event_calendar_manage_posts_columns', 'WSU\Events\filter_posts_columns', 10, 2 );

remove_filter( 'pre_get_posts', 'wp_event_calendar_maybe_sort_by_fields' );
add_filter( 'pre_get_posts', __NAMESPACE__ . '\\filter_list_table_query' );

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
		$args['supports'][] = 'author';
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

/**
 * Set the relevant query vars for sorting the events list table view.
 *
 * @since 0.1.10
 *
 * @param WP_Query $wp_query The current WP_Query object.
 */
function filter_list_table_query( \WP_Query $wp_query ) {

	// Bail if this is not an admin view.
	if ( ! is_admin() ) {
		return;
	}

	// Bail if no post_type
	if ( empty( $wp_query->get( 'post_type' ) ) ) {
		return;
	}

	// Bail if not the event post type.
	if ( 'event' !== $wp_query->get( 'post_type' ) ) {
		return;
	}

	// Bail in AJAX for now
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	// Bail the if the `get_current_screen()` function doesn't exist.
	if ( ! function_exists( 'get_current_screen' ) ) {
		return;
	}

	// Bail if not the even list table page.
	if ( 'edit-event' !== get_current_screen()->id ) {
		return;
	}

	// Bail if the orderby argument isn't one we're interested in.
	if ( ! in_array( $wp_query->query['orderby'], array( 'menu_order title', 'start_date', 'end_date' ), true ) ) {
		return;
	}

	if ( in_array( $wp_query->query['orderby'], array( 'menu_order title', 'start_date' ), true ) ) {
		$meta_key = 'wp_event_calendar_date_time';
	} else {
		$meta_key = 'wp_event_calendar_end_date_time';
	}

	$order = 'asc';

	if ( in_array( $wp_query->query['orderby'], array( 'start_date', 'end_date' ), true ) ) {
		$order = $wp_query->get( 'order' );
	}

	$wp_query->set( 'order', $order );
	$wp_query->set( 'orderby', 'meta_value' );
	$wp_query->set( 'meta_key', $meta_key );
	$wp_query->set( 'meta_type', 'DATETIME' );
}
