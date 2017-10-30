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
		'_contact_name' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_contact_email' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_email',
		),
		'_contact_phone' => array(
			'type' => 'string',
			'sanitize_callback' => 'WSU\Events\Meta_Data\sanitize_phone_number',
		),
		'_action_text' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_action_url' => array(
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
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
	add_meta_box(
		'wsuwp_event_calendar_contact',
		'Contact/Organizer',
		'WSU\Events\Meta_Data\display_contact_meta_box',
		\wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'default'
	);
	add_meta_box(
		'wsuwp_event_calendar_action',
		'Action',
		'WSU\Events\Meta_Data\display_action_meta_box',
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

/**
 * Displays the meta box used to capture an event's contact data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_contact_meta_box( $post ) {
	$name = get_post_meta( $post->ID, '_contact_name', true );
	$email = get_post_meta( $post->ID, '_contact_email', true );
	$phone = get_post_meta( $post->ID, '_contact_phone', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_contact_name">Name</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_contact_name"
					   name="_contact_name"
					   value="<?php echo esc_attr( $name ); ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="wsuwp_event_contact_email">Email</label>
			</th>
			<td>
				<input type="email"
					   id="wsuwp_event_contact_email"
					   name="_contact_email"
					   value="<?php echo esc_attr( $email ); ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="wsuwp_event_contact_phone">Phone Number</label>
			</th>
			<td>
				<input type="tel"
					   id="wsuwp_event_contact_phone"
					   name="_contact_phone"
					   placeholder="(555) 555-5555, ext. 5555"
					   value="<?php echo esc_attr( $phone ); ?>" />
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Displays the meta box used to capture an event's action data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_action_meta_box( $post ) {
	$text = get_post_meta( $post->ID, '_action_text', true );
	$url = get_post_meta( $post->ID, '_action_url', true );
	?>
	<p class="description">Use these fields to create a call to action (Register, RSVP, etc.)</p>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_action_text">Text</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_action_text"
					   name="_action_text"
					   value="<?php echo esc_attr( $text ); ?>" />
			</td>
		</tr>
		<tr>
			<th>
				<label for="wsuwp_event_action_url">URL</label>
			</th>
			<td>
				<input type="url"
					   id="wsuwp_event_action_url"
					   name="_action_url"
					   class="widefat"
					   value="<?php echo esc_attr( $url ); ?>" />
			</td>
		</tr>
	</table>
	<?php
}
