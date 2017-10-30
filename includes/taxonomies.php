<?php

namespace WSU\Events\Taxonomies;

add_action( 'init', 'WSU\Events\Taxonomies\unregister_types', 11 );

/**
 * Removes the Types taxonomy from the Events post type.
 *
 * @since 0.0.1
 */
function unregister_types() {
	unregister_taxonomy_for_object_type( 'event-type', 'event' );
}
