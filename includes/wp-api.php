<?php

namespace WSU\Events\WP_API;

add_filter( 'register_post_type_args', 'WSU\Events\WP_API\register_endpoint', 10, 2 );
add_action( 'rest_api_init', 'WSU\Events\WP_API\register_api_fields' );
add_filter( 'rest_prepare_event', 'WSU\Events\WP_API\event_content', 10, 2 );
add_action( 'rest_event_query', 'WSU\Events\WP_API\filter_rest_query' );

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

/**
 * Register the custom meta fields attached to a REST API response containing event data.
 *
 * @since 0.1.1
 */
function register_api_fields() {
	$args = array(
		'get_callback' => 'WSU\Events\WP_API\get_api_meta_data',
		'update_callback' => 'esc_html',
		'schema' => null,
	);

	register_rest_field( 'event', 'start_date', $args );
	register_rest_field( 'event', 'end_date', $args );
	register_rest_field( 'event', 'event_venue', $args );
	register_rest_field( 'event', 'event_city', $args );
	register_rest_field( 'event', 'event_state', $args );
}

/**
 * Retrieve the data to use when returning an event through the REST API.
 *
 * @since 0.1.1
 *
 * @param array           $object  The current post being processed.
 * @param string          $field   Name of the field being retrieved.
 * @param WP_Rest_Request $request The full current REST request.
 *
 * @return string
 */
function get_api_meta_data( $object, $field, $request ) {
	if ( 'start_date' === $field ) {
		$start_date = get_post_meta( $object['id'], 'wp_event_calendar_date_time', true );
		$start_date = date( 'Y-m-d H:i:s', strtotime( $start_date ) );

		return esc_html( $start_date );
	}

	if ( 'end_date' === $field ) {
		$end_date = get_post_meta( $object['id'], 'wp_event_calendar_end_date_time', true );
		$end_date = date( 'Y-m-d H:i:s', strtotime( $end_date ) );

		return esc_html( $end_date );
	}

	if ( 'event_venue' !== $field && 'event_city' !== $field && 'event_state' !== $field ) {
		return '';
	}

	$event_venue = \WSU\Events\Venues\get_venue( $object['id'] );

	if ( 'event_venue' === $field ) {
		return esc_html( $event_venue['raw']['name'] );
	}

	if ( 'event_city' === $field ) {
		return esc_html( $event_venue['raw']['city'] );
	}

	if ( 'event_state' === $field ) {
		return esc_html( $event_venue['raw']['state'] );
	}

	return '';
}

/**
 * Filter post data to include content in the REST API response.
 *
 * @since 0.1.1
 *
 * @param WP_REST_Response $response
 * @param WP_Post $args    $post
 *
 * @return WP_REST_Response
 */
function event_content( $response, $post ) {
	$response->data['content']['rendered'] = wp_kses_post( wpautop( $post->post_content ) );

	return $response;
}

/**
 * Filter the events REST API query before it fires.
 *
 * @since 0.1.1
 *
 * @param array $args
 *
 * @return array
 */
function filter_rest_query( $args ) {
	$today = current_time( 'Y-m-d' ) . ' 00:00:00';
	$current_time = current_time( 'mysql' );

	if ( isset( $_REQUEST['tribe_event_display'] ) && 'past' === $_REQUEST['tribe_event_display'] ) { // WPCS: CSRF Ok.
		$args['meta_query'] = array(
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $today,
				'compare' => '<=',
				'type' => 'DATETIME',
			),
			'wsuwp_event_end_date' => array(
				'key' => 'wp_event_calendar_end_date_time',
				'value' => $current_time,
				'compare' => '<',
				'type' => 'DATETIME',
			),
		);

		$args['orderby'] = 'wsuwp_event_end_date';
		$args['order'] = 'DESC';
	} else {
		$args['meta_query'] = array(
			'wsuwp_event_start_date' => array(
				'key' => 'wp_event_calendar_date_time',
				'value' => $today,
				'compare' => '>=',
				'type' => 'DATETIME',
			),
			'wsuwp_event_end_date' => array(
				'key' => 'wp_event_calendar_end_date_time',
				'value' => $current_time,
				'compare' => '>',
				'type' => 'DATETIME',
			),
		);

		$args['orderby'] = 'wsuwp_event_start_date';
		$args['order'] = 'ASC';
	}

	return $args;
}
