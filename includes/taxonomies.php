<?php

namespace WSU\Events\Taxonomies;

add_action( 'init', 'WSU\Events\Taxonomies\unregister_types', 11 );
add_action( 'init', 'WSU\Events\Taxonomies\register_university_taxonomies', 12 );
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Events\Taxonomies\taxonomy_meta_box', 10 );

/**
 * Removes the Types taxonomy from the Events post type.
 *
 * @since 0.0.1
 */
function unregister_types() {
	unregister_taxonomy_for_object_type( 'event-type', 'event' );
}

/**
 * Registers University Taxonomies for the Events post type.
 *
 * @since 0.0.1
 */
function register_university_taxonomies() {
	register_taxonomy_for_object_type( 'wsuwp_university_category', 'event' );
	register_taxonomy_for_object_type( 'wsuwp_university_location', 'event' );
	register_taxonomy_for_object_type( 'wsuwp_university_org', 'event' );
}

/**
 * Displays a meta box for selecting taxonomy terms.
 *
 * @since 0.0.1
 *
 * @param array $post_types Post types and their associated taxonomies.
 */
function taxonomy_meta_box( $post_types ) {
	$post_types['event'] = get_object_taxonomies( 'event' );

	return $post_types;
}
