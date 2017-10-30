<?php

namespace WSU\Events\Taxonomies;

add_action( 'init', 'WSU\Events\Taxonomies\unregister_types', 11 );
add_action( 'init', 'WSU\Events\Taxonomies\register_university_taxonomies', 12 );

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
