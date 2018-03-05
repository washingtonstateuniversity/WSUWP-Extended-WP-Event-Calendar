<?php

namespace WSU\Events\Taxonomies;

add_action( 'init', 'WSU\Events\Taxonomies\unregister_taxonomies', 11 );
add_action( 'init', 'WSU\Events\Taxonomies\register_university_taxonomies', 12 );
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Events\Taxonomies\taxonomy_meta_box', 10 );
add_filter( 'register_taxonomy_args', 'WSU\Events\Taxonomies\make_public', 10, 2 );

add_filter( 'register_taxonomy_args', 'WSU\Events\Taxonomies\hierarchical_types', 10, 2 );
add_filter( 'wsuwp_taxonomy_metabox_disable_new_term_adding', 'WSU\Events\Taxonomies\disable_new_types' );
add_filter( 'pre_insert_term', 'WSU\Events\Taxonomies\prevent_type_term_creation', 10, 2 );

/**
 * Unregisters Event Categories and Event Tags.
 *
 * @since 0.0.2
 */
function unregister_taxonomies() {
	unregister_taxonomy_for_object_type( 'event-category', 'event' );
	unregister_taxonomy_for_object_type( 'event-tag', 'event' );
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

/**
 * Make the Types taxonomy hierarchical.
 *
 * @since 0.0.2
 *
 * @param array  $args     Arguments for registering a taxonomy.
 * @param string $taxonomy Taxonomy key.
 *
 * @return array
 */
function hierarchical_types( $args, $taxonomy ) {
	if ( 'event-type' === $taxonomy ) {
		$args['hierarchical'] = true;
	}

	return $args;
}

/**
 * Disables the interface for adding new terms to the Types taxonomy.
 *
 * @since 0.0.2
 *
 * @param array $taxonomies
 *
 * @return array
 */
function disable_new_types( $taxonomies ) {
	$taxonomies[] = 'event-type';

	return $taxonomies;
}

/**
 * Prevent new terms being created for the Types taxonomy in normal term entry situations.
 *
 * @since 0.0.2
 *
 * @param string $term     Term being added.
 * @param string $taxonomy Taxonomy of the term being added.
 *
 * @return string|WP_Error The untouched term if not the Types taxonomy, WP_Error otherwise.
 */
function prevent_type_term_creation( $term, $taxonomy ) {
	if ( 'event-type' === get_current_screen()->taxonomy ) {
		$term = new WP_Error( 'invalid_term', 'These terms cannot be modified.' );
	}

	return $term;
}
