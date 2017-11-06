<?php

namespace WSU\Events;

add_filter( 'register_post_type_args', 'WSU\Events\make_public', 10, 2 );

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
	}

	return $args;
}
