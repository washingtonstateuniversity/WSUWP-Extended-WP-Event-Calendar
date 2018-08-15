<?php

namespace WSU\Events\Venues;

add_action( 'init', 'WSU\Events\Venues\register_post_type', 11 );
add_action( 'init', 'WSU\Events\Venues\register_taxonomy', 11 );
add_action( 'save_post', 'WSU\Events\Venues\mirror_taxonomy_post_type', 10, 2 );
add_action( 'cmb2_admin_init', 'WSU\Events\Venues\add_location_metabox' );
add_action( 'cmb2_init', 'WSU\Events\Venues\cmb2_init_address_field' );

/**
 * Register a venue post type to track information about event venues.
 *
 * @since 0.1.0
 */
function register_post_type() {
	\register_post_type(
		'venue', array(
			'labels'            => array(
				'name'                => __( 'Venue', 'wsuwp-extended-wp-event-calendar' ),
				'singular_name'       => __( 'Venues', 'wsuwp-extended-wp-event-calendar' ),
				'all_items'           => __( 'All Venues', 'wsuwp-extended-wp-event-calendar' ),
				'new_item'            => __( 'New Venues', 'wsuwp-extended-wp-event-calendar' ),
				'add_new'             => __( 'Add New', 'wsuwp-extended-wp-event-calendar' ),
				'add_new_item'        => __( 'Add New Venues', 'wsuwp-extended-wp-event-calendar' ),
				'edit_item'           => __( 'Edit Venues', 'wsuwp-extended-wp-event-calendar' ),
				'view_item'           => __( 'View Venues', 'wsuwp-extended-wp-event-calendar' ),
				'search_items'        => __( 'Search Venues', 'wsuwp-extended-wp-event-calendar' ),
				'not_found'           => __( 'No Venues found', 'wsuwp-extended-wp-event-calendar' ),
				'not_found_in_trash'  => __( 'No Venues found in trash', 'wsuwp-extended-wp-event-calendar' ),
				'parent_item_colon'   => __( 'Parent Venues', 'wsuwp-extended-wp-event-calendar' ),
				'menu_name'           => __( 'Venues', 'wsuwp-extended-wp-event-calendar' ),
			),
			'public'            => false,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_nav_menus' => true,
			'supports'          => array( 'title' ),
			'has_archive'       => false,
			'rewrite'           => array(
				'slug' => 'venue',
			),
			'query_var'         => false,
			'menu_icon'         => 'dashicons-admin-post',
			'show_in_rest'      => true,
			'rest_base'         => 'venue',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		)
	);
}

/**
 * Register a taxonomy to mirror the venue post type so that venue
 * assignment to events is easier.
 *
 * @since 0.1.0
 */
function register_taxonomy() {
	\register_taxonomy(
		'venue-tax', array(
			'venue',
			'event',
		),
		array(
			'hierarchical'      => false,
			'public'            => false,
			'show_in_nav_menus' => false,
			'show_ui'           => false,
			'show_admin_column' => false,
			'query_var'         => false,
			'rewrite'           => false,
			'capabilities'      => array(
				'manage_terms'  => 'edit_posts',
				'edit_terms'    => 'edit_posts',
				'delete_terms'  => 'edit_posts',
				'assign_terms'  => 'edit_posts',
			),
			'labels'            => array(
				'name'                       => __( 'Venues', 'wsuwp-extended-wp-event-calendar' ),
			),
			'show_in_rest'      => false,
		)
	);
}

/**
 * Provide basic mirroring functionality between the venue post type and
 * venue taxonomy.
 *
 * @since 0.1.0
 *
 * @param int      $post_id
 * @param \WP_Post $post
 */
function mirror_taxonomy_post_type( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'venue' !== $post->post_type ) {
		return;
	}

	if ( 'auto-draft' === $post->post_name || empty( $post->post_name ) ) {
		return;
	}

	// Check for an existing venue with the same slug as this post.
	$term = get_term_by( 'slug', $post->post_name, 'venue-tax' );

	if ( ! is_object( $term ) ) {
		$term = wp_insert_term(
			$post->post_title, 'venue-tax', array(
				'slug' => $post->post_name,
			)
		);

		if ( ! is_wp_error( $term ) ) {
			wp_set_object_terms( $post_id, $term['term_id'], 'venue-tax' );
		}
	} else {

		// Ensure the relationship is maintained.
		wp_set_object_terms( $post_id, $term->term_id, 'venue-tax' );

		// Update the term title and slug.
		wp_update_term( $term->term_id, 'venue-tax', array(
			'name' => $post->post_title,
			'slug' => sanitize_title( $post->post_title ),
		) );
	}
}

/**
 * Manage the venue meta captured by CMB2.
 *
 * @since 0.1.0
 */
function add_location_metabox() {
	$cmb = new_cmb2_box(
		array(
			'id'           => 'location_data',
			'title'        => 'Venue Location',
			'object_types' => array(
				'venue',
			),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
		)
	);

	$cmb->add_field(
		array(
			'name' => 'Venue Address',
			'desc' => 'Enter the physical address for the venue.',
			'id' => 'venue_address',
			'type' => 'address',
		)
	);

	$cmb->add_field(
		array(
			'name'       => 'Venue URL',
			'desc'       => 'Enter the URL where a visitor can find more information about the venue.',
			'id'         => 'venue_url',
			'type'       => 'text_url',
		)
	);

	$cmb->add_field(
		array(
			'name' => 'Venue email',
			'desc' => 'Enter the email at which a visitor may contact the venue for more information.',
			'id' => 'venue_email',
			'type' => 'text_email',
		)
	);
}

/**
 * Initialize the custom address field for CMB2.
 */
function cmb2_init_address_field() {
	require_once dirname( __FILE__ ) . '/class-cmb2-render-address-field.php';
	\CMB2_Render_Address_Field::init();
}

/**
 * Return the venue for an event.
 *
 * @since 0.1.1
 */
function get_venue( $post_id = false ) {
	$post_id = ( $post_id ) ? $post_id : get_the_ID();
	$venue_term = wp_get_post_terms( $post_id, 'venue-tax' );

	if ( ! $venue_term ) {
		return false;
	}

	$venue_post = get_posts( array(
		'posts_per_page' => 1,
		'post_type'      => 'venue',
		'tax_query'      => array(
			array(
				'taxonomy' => 'venue-tax',
				'terms' => $venue_term[0]->term_id,
			),
		),
	) );

	if ( ! $venue_post ) {
		return false;
	}

	$venue_post_id = $venue_post[0]->ID;

	$address_1 = get_post_meta( $venue_post_id, 'venue_address_address-1', true );
	$address_2 = get_post_meta( $venue_post_id, 'venue_address_address-2', true );
	$city = get_post_meta( $venue_post_id, 'venue_address_city', true );
	$state = get_post_meta( $venue_post_id, 'venue_address_state', true );
	$zip = get_post_meta( $venue_post_id, 'venue_address_zip', true );

	$address = $venue_post[0]->post_title;
	$address .= ( $address_1 ) ? ', ' . $address_1 : '';
	$address .= ( $address_2 ) ? ', ' . $address_2 : '';
	$address .= ( $city ) ? ', ' . $city : '';
	$address .= ( $state ) ? ', ' . $state : '';
	$address .= ( $zip ) ? ' ' . $zip : '';

	$latitude = get_post_meta( $venue_post_id, 'venue_address_latitude', true );
	$longitude = get_post_meta( $venue_post_id, 'venue_address_longitude', true );

	if ( $latitude && $longitude ) {
		$link = 'https://www.google.com/maps/search/?api=1&query=';
		$link .= $latitude . ',' . $longitude;
	} else {
		$link = false;
	}

	return array(
		'address' => $address,
		'link' => $link,
		'raw' => array(
			'name' => $venue_post[0]->post_title,
			'city' => $city,
			'state' => $state,
		),
	);
}
