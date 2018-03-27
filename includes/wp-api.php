<?php

namespace WSU\Events\WP_API;

add_filter( 'register_post_type_args', 'WSU\Events\WP_API\register_endpoint', 10, 2 );

/**
 * Update the `register_post_type()` arguments for Events to support an /events/ endpoint.
 *
 * @since 0.1.1
 *
 * @param array  $args      Arguments for registering a post type.
 * @param string $post_type Post type key.
 *
 * @return array
 */
function register_endpoint( $args, $post_type ) {
	if ( 'event' === $post_type ) {
		$args['show_in_rest'] = true;
		$args['rest_base'] = 'events';
	}

	return $args;
}
