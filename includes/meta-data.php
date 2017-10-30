<?php

namespace WSU\Events\Meta_Data;

add_action( 'init', 'WSU\Events\Meta_Data\register_meta' );
add_action( 'add_meta_boxes_event', 'WSU\Events\Meta_Data\meta_boxes', 10 );

/**
 * Provides an array of additional post meta keys associated with events.
 *
 * @since 0.0.1
 *
 * @return array
 */
function post_meta_keys() {
	return array(
		'_location_name' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_location_latitude' => array(
			'type' => 'float',
			'sanitize_callback' => 'WSU\Events\Meta_Data\sanitize_coordinate',
		),
		'_location_longitude' => array(
			'type' => 'float',
			'sanitize_callback' => 'WSU\Events\Meta_Data\sanitize_coordinate',
		),
		'_location_notes' => array(
			'type' => 'string',
			'sanitize_callback' => 'wp_kses_post',
		),
	);
}

/**
 * Registers the meta associated with events.
 *
 * @since 0.0.1
 */
function register_meta() {
	foreach ( post_meta_keys() as $key => $args ) {
		$args['single'] = true;
		\register_meta( 'post', $key, $args );
	}
}

/**
 * Adds meta boxes used to manage additional event data.
 *
 * @since 0.0.1
 *
 * @param string $post_type
 */
function meta_boxes() {
	add_meta_box(
		'wsuwp_event_calendar_location',
		'Location',
		'WSU\Events\Meta_Data\display_location_meta_box',
		\wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'default'
	);
}

/**
 * Displays the meta box used to capture an event's location data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_location_meta_box( $post ) {
	wp_nonce_field( 'wsuwp_event', 'wsuwp_event_nonce' );

	$name = get_post_meta( $post->ID, '_location_name', true );
	$latitude = get_post_meta( $post->ID, '_location_latitude', true );
	$longitude = get_post_meta( $post->ID, '_location_longitude', true );
	$notes = get_post_meta( $post->ID, '_location_notes', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_location_name">Name</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_location_name"
					   name="_location_name"
					   value="<?php echo esc_attr( $name ); ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="wsuwp_event_location_latitude">Latitude</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_location_latitude"
					   name="_location_latitude"
					   value="<?php echo esc_attr( $latitude ); ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="wsuwp_event_location_longitude">Longitude</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_location_longitude"
					   name="_location_longitude"
					   value="<?php echo esc_attr( $longitude ); ?>" />
			</td>
		</tr>
		<tr>
			<th>Notes</th>
			<td>
				<?php
				$notes_editor_settings = array(
					'media_buttons' => false,
					'textarea_rows' => 2,
					'teeny' => true,
					'quicktags' => false,
				);

				wp_editor( $notes, '_location_notes', $notes_editor_settings );
				?>
			</td>
		</tr>
	</table>
	<?php
}
