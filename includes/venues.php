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
	\register_post_type( 'venue', array(
		'labels'            => array(
			'name'                => __( 'Venues', 'wsuwp-extended-wp-event-calendar' ),
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
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'supports'          => array( 'title' ),
		'has_archive'       => true,
		'rewrite'           => array(
			'slug' => 'venue',
		),
		'query_var'         => true,
		'menu_icon'         => 'dashicons-admin-post',
		'show_in_rest'      => true,
		'rest_base'         => 'venue',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );
}

/**
 * Register a taxonomy to mirror the venue post type so that venue
 * assignment to events is easier.
 *
 * @since 0.1.0
 */
function register_taxonomy() {
	\register_taxonomy( 'venue-tax', array(
		'venue',
		'event',
	), array(
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
	) );
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
		$term = wp_insert_term( $post->post_title, 'venue-tax', array(
			'slug' => $post->post_name,
		) );

		if ( ! is_wp_error( $term ) ) {
			wp_set_object_terms( $post_id, $term['term_id'], 'venue-tax' );
		}
	} else {

		// Ensure the relationship is maintained.
		wp_set_object_terms( $post_id, $term->term_id, 'venue-tax' );
	}
}

/**
 * Manage the venue meta captured by CMB2.
 *
 * @sine 0.1.0
 */
function add_location_metabox() {
	$cmb = new_cmb2_box( array(
		'id'           => 'location_data',
		'title'        => 'Venue Location',
		'object_types' => array(
			'venue',
		),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
	) );

	$cmb->add_field( array(
		'name' => 'Venue Address',
		'desc' => 'Enter the physical address for the venue.',
		'id' => 'venue_address',
		'type' => 'address',
	) );

	$cmb->add_field( array(
		'name'       => 'Venue URL',
		'desc'       => 'Enter the URL where a visitor can find more information about the venue.',
		'id'         => 'venue_url',
		'type'       => 'text_url',
	) );

	$cmb->add_field( array(
		'name' => 'Venue email',
		'desc' => 'Enter the email at which a visitor may contact the venue for more information.',
		'id' => 'venue_email',
		'type' => 'text_email',
	) );
}

/**
 * Template tag for displaying a formatted address from the custom CMB2 address field type
 * on the front-end.
 *
 * This was forked from CMB2's snippet library and can be adapted to our needs.
 *
 * Source: https://github.com/CMB2/CMB2-Snippet-Library/tree/master/custom-field-types/address-field-type
 *
 * @since  0.1.0
 *
 * @param  string $metakey The 'id' of the 'address' field (the metakey for get_post_meta)
 * @param  integer $post_id (optional) post ID. If using in the loop, it is not necessary
 */
function display_address_field( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$address = get_post_meta( $post_id, 'venue_address', true );

	// Set default values for each address key
	$address = wp_parse_args( $address, array(
		'address-1' => '',
		'address-2' => '',
		'city'      => '',
		'state'     => '',
		'zip'       => '',
		'country'   => '',
	) );

	$output = '<div class="cmb2-address">';
	$output .= '<p><strong>Address:</strong> ' . esc_html( $address['address-1'] ) . '</p>';
	if ( $address['address-2'] ) {
		$output .= '<p>' . esc_html( $address['address-2'] ) . '</p>';
	}
	$output .= '<p><strong>City:</strong> ' . esc_html( $address['city'] ) . '</p>';
	$output .= '<p><strong>State:</strong> ' . esc_html( $address['state'] ) . '</p>';
	$output .= '<p><strong>Zip:</strong> ' . esc_html( $address['zip'] ) . '</p>';
	$output .= '</div><!-- .cmb2-address -->';

	echo $output; // WCPS: XSS Ok.
}

/**
 * Initialize the custom address field for CMB2.
 */
function cmb2_init_address_field() {
	require_once dirname( __FILE__ ) . '/class-cmb2-render-address-field.php';
	\CMB2_Render_Address_Field::init();
}
