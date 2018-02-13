<?php

namespace WSU\Events\Taxonomies;

add_action( 'init', 'WSU\Events\Taxonomies\register_university_taxonomies', 12 );
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Events\Taxonomies\taxonomy_meta_box', 10 );
add_filter( 'register_taxonomy_args', 'WSU\Events\Taxonomies\make_public', 10, 2 );

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

/**
 * Allows public views of taxonomy pages.
 *
 * @since 0.0.1
 *
 * @param array  $args     Arguments for registering a taxonomy.
 * @param string $taxonomy Taxonomy key.
 *
 * @return array
 */
function make_public( $args, $taxonomy ) {
	if ( 'event-type' === $taxonomy || 'event-category' === $taxonomy || 'event-tag' === $taxonomy ) {
		$args['public'] = true;
		$args['show_in_rest'] = true;
	}

	if ( 'wsuwp_university_category' === $taxonomy || 'wsuwp_university_location' === $taxonomy || 'wsuwp_university_org' === $taxonomy ) {
		$args['show_in_rest'] = true;
	}

	return $args;
}
