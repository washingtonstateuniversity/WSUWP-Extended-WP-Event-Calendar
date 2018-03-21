<?php

namespace WSU\Events\Taxonomies;

/**
 * Maintain a record of the Types taxonomy schema. This should be changed whenever
 * a schema change should be initiated on any site using the taxonomy.
 *
 * @since 0.0.2
 *
 * @return string Current version of the Types taxonomy schema.
 */
function types_schema_version() {
	return '20180321-001';
}

/**
 * Returns the slug for the Types taxonomy.
 *
 * @since 0.0.2
 *
 * @return string
 */
function types_slug() {
	return 'event-type';
}

add_action( 'init', 'WSU\Events\Taxonomies\unregister_taxonomies', 11 );
add_action( 'manage_event_posts_columns', 'WSU\Events\Taxonomies\manage_columns', 11 );
add_action( 'init', 'WSU\Events\Taxonomies\register_university_taxonomies', 12 );
add_filter( 'wsuwp_taxonomy_metabox_post_types', 'WSU\Events\Taxonomies\taxonomy_meta_box', 10 );

add_filter( 'register_taxonomy_args', 'WSU\Events\Taxonomies\types_arguments', 10, 2 );
add_filter( 'wsuwp_taxonomy_metabox_disable_new_term_adding', 'WSU\Events\Taxonomies\disable_new_types' );
add_filter( 'pre_insert_term', 'WSU\Events\Taxonomies\prevent_type_term_creation', 10, 2 );
add_action( 'init', 'WSU\Events\Taxonomies\types_checklist_args' );

add_action( 'wpmu_new_blog', 'WSU\Events\Taxonomies\pre_load_types', 10 );
add_action( 'admin_init', 'WSU\Events\Taxonomies\check_types_schema', 10 );
add_action( 'wsuwp_event_types_update_schema', 'WSU\Events\Taxonomies\update_types_schema' );
add_action( 'load-edit-tags.php', 'WSU\Events\Taxonomies\display_type_terms', 11 );
add_filter( 'parent_file', 'WSU\Events\Taxonomies\types_parent_file' );
add_filter( 'submenu_file', 'WSU\Events\Taxonomies\types_submenu_file', 10, 2 );

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
 * Unsets the Event Categories column from the Events list table.
 *
 * @since 0.0.2
 *
 * @param array $columns
 *
 * @return array
 */
function manage_columns( $columns ) {
	unset( $columns['event-categories'] );

	return $columns;
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
 * Modifies arguments for registering the Types taxonomy.
 *
 * @since 0.0.2
 *
 * @param array  $args     Arguments for registering a taxonomy.
 * @param string $taxonomy Taxonomy key.
 *
 * @return array
 */
function types_arguments( $args, $taxonomy ) {
	if ( types_slug() === $taxonomy ) {
		$args['hierarchical'] = true;
		$args['public'] = true;
		$args['show_in_rest'] = true;
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
	$taxonomies[] = types_slug();

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
	if ( types_slug() === get_current_screen()->taxonomy ) {
		$term = new WP_Error( 'invalid_term', 'These terms cannot be modified.' );
	}

	return $term;
}

/**
 * Disables the custom WP Event Calendar taxonomy checklist arguments.
 *
 * It replaces checkboxes with radio buttons, but outputs the term name
 * instead of the ID as the input value, which results in quick/bulk
 * edit changes not saving properly. We're not particularly concerned
 * with limiting events to a single `type` designation, so we can safely
 * disable this custom output.
 *
 * @since 0.0.3
 *
 */
function types_checklist_args() {
	remove_filter( 'wp_terms_checklist_args', 'wp_event_calendar_checklist_args', 10, 1 );
}

/**
 * Maintain an array of current Type terms.
 *
 * @since 0.0.2
 *
 * @return array Event Types
 */
function get_event_types() {
	$types = array(
		'Academic Calendar',
		'Athletics / Sports',
		'Careers / Jobs',
		'Ceremony / Service',
		'Class / Instruction',
		'Community Service',
		'Conference / Symposium',
		'Exercise / Fitness',
		'Exhibition',
		'Film Screening',
		'Lecture',
		'Meeting',
		'Performance',
		'Presentation',
		'Reception / Open House',
		'Recreational / Games',
		'Social',
		'Workshop / Seminar',
	);

	return $types;
}

/**
 * Clears all cache for the Types taxonomy.
 *
 * @since 0.0.2
 */
function clear_types_cache() {
	wp_cache_delete( 'all_ids', types_slug() );
	wp_cache_delete( 'get', types_slug() );
	delete_option( types_slug() . '_children' );
	_get_term_hierarchy( types_slug() );
}

/**
 * Ensure all the pre-configured Types terms are loaded.
 *
 * @since 0.0.2
 */
function load_type_terms() {
	clear_types_cache();

	$term_exist = get_terms( types_slug(), array(
		'hide_empty' => false,
	) );

	$term_assign = array();

	foreach ( $term_exist as $term ) {
		$term_assign[ $term->name ] = array(
			'term_id' => $term->term_id,
		);
	}

	remove_filter( 'pre_insert_term', 'WSU\Events\Taxonomies\prevent_type_term_creation', 10 );

	$term_names = get_event_types();

	/**
	 * Look for mismatches between the master list and the existing terms list.
	 *
	 * In this loop:
	 *  $term_names  array of top level parent names.
	 *  $term_name   string containing a top level category.
	 *  $term_assign array of terms that exist in the database with term ids.
	 */
	foreach ( $term_names as $term_name ) {
		if ( ! array_key_exists( $term_name, $term_assign ) ) {
			$new_term = wp_insert_term( $term_name, types_slug(), array(
				'parent' => '0',
			) );

			if ( ! is_wp_error( $new_term ) ) {
				$term_assign[ $term_name ] = array(
					'term_id' => $new_term['term_id'],
				);
			}
		}
	}

	add_filter( 'pre_insert_term', 'WSU\Events\Taxonomies\prevent_type_term_creation', 10 );

	clear_types_cache();
}

/**
 * Pre-load Types terms whenever a new site is created on the network.
 *
 * @since 0.0.2
 *
 * @param int $site_id The ID of the new site.
 */
function pre_load_types( $site_id ) {
	switch_to_blog( $site_id );
	update_types_schema();
	restore_current_blog();
}

/**
 * Check the current version of the Types schema on every admin page load.
 * If it is out of date, fire a single wp-cron event to process the changes.
 *
 * @since 0.0.2
 */
function check_types_schema() {
	if ( get_option( 'wsuwp_event_types_schema', false ) !== types_schema_version() ) {
		wp_schedule_single_event( time() + 60, 'wsuwp_event_types_update_schema' );
	}
}
/**
 * Update the Types schema and version.
 *
 * @since 0.0.2
 */
function update_types_schema() {
	load_type_terms();
	update_option( 'wsuwp_event_types_schema', types_schema_version() );
}

/**
 * Display custom output for Types instead of the default term managment screen.
 *
 * @since 0.0.2
 */
function display_type_terms() {
	if ( types_slug() !== get_current_screen()->taxonomy ) {
		return;
	}

	if ( get_option( 'wsuwp_event_types_schema', false ) !== types_schema_version() ) {
		update_types_schema();
	}

	require_once ABSPATH . 'wp-admin/admin-header.php';

	$tax = get_taxonomy( types_slug() );

	$terms = get_terms( types_slug(), array(
		'hide_empty' => false,
	) );

	$count_link_args = array(
		'post_type' => get_current_screen()->post_type,
		'taxonomy' => $tax->name,
	);
	?>

	<div class="wrap nosubsub">

		<h1 class="wp-heading-inline"><?php echo esc_html( $tax->labels->name ); ?></h1>

		<ul>
			<?php foreach ( $terms as $term ) { ?>
			<li><?php echo esc_html( $term->name ); ?></li>
			<?php } ?>
		</ul>

	</div>

	<?php
	require_once ABSPATH . 'wp-admin/admin-footer.php';

	die();
}

/**
 * Sets the active parent menu item for the Types dashboard page.
 * Using the `load-edit-tags.php` hook prevents this from being set by default.
 *
 * @since 0.0.2
 *
 * @param string $parent_file
 *
 * @return string
 */
function types_parent_file( $parent_file ) {
	if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['taxonomy'] ) ) { // WPCS: CSRF ok.
		return $parent_file;
	}

	if ( 'event' !== $_GET['post_type'] || types_slug() !== $_GET['taxonomy'] ) { // WPCS: CSRF ok.
		return $parent_file;
	}

	$parent_file = 'edit.php?post_type=event';

	return $parent_file;
}

/**
 * Sets the active menu item for the Types dashboard page.
 * Using the `load-edit-tags.php` hook prevents this from being set by default.
 *
 * @since 0.0.2
 *
 * @param string $submenu_file
 * @param string $parent_file
 *
 * @return string
 */
function types_submenu_file( $submenu_file, $parent_file ) {
	if ( ! isset( $_GET['post_type'] ) || ! isset( $_GET['taxonomy'] ) ) { // WPCS: CSRF ok.
		return $submenu_file;
	}

	if ( 'event' !== $_GET['post_type'] || types_slug() !== $_GET['taxonomy'] ) { // WPCS: CSRF ok.
		return $submenu_file;
	}

	$submenu_file = 'edit-tags.php?taxonomy=event-type&amp;post_type=event';

	return $submenu_file;
}
