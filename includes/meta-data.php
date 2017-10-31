<?php

namespace WSU\Events\Meta_Data;

add_filter( 'wp_event_calendar_location', '__return_false' );
add_action( 'init', 'WSU\Events\Meta_Data\register_meta' );
add_action( 'add_meta_boxes_event', 'WSU\Events\Meta_Data\meta_boxes', 10 );
add_action( 'save_post_event', 'WSU\Events\Meta_Data\save_post', 10, 2 );
add_action( 'admin_enqueue_scripts', 'WSU\Events\Meta_Data\admin_enqueue_scripts' );

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
		'_cost' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_related_site' => array(
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
	add_meta_box(
		'wsuwp_event_calendar_cost',
		'Cost',
		'WSU\Events\Meta_Data\display_cost_meta_box',
		\wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'default'
	);
	add_meta_box(
		'wsuwp_event_calendar_site',
		'Related Site',
		'WSU\Events\Meta_Data\display_site_meta_box',
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

/**
 * Displays the meta box used to capture an event's cost data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_cost_meta_box( $post ) {
	$cost = get_post_meta( $post->ID, '_cost', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_cost">Price</label>
			</th>
			<td>
				$<input type="text"
						id="wsuwp_event_cost"
						name="_cost"
						value="<?php echo esc_attr( $cost ); ?>" />
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Displays the meta box used to capture an event's related site data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_site_meta_box( $post ) {
	$url = get_post_meta( $post->ID, '_related_site', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_site">URL</label>
			</th>
			<td>
				<input type="url"
					   id="wsuwp_event_site"
					   name="_related_site"
					   class="widefat"
					   value="<?php echo esc_attr( $url ); ?>" />
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Sanitizes coordinate field values.
 *
 * @since 0.0.1
 *
 * @param string $coordinate The unsanitized coordinate value.
 *
 * @return float
 */
function sanitize_coordinate( $coordinate ) {
	$coordinate_float = floatval( $coordinate );

	if ( (float) 0 === $coordinate_float ) {
		return '';
	}

	return $coordinate_float;
}

/**
 * Sanitizes and formats the value of the contact phone number field.
 *
 * @since 0.0.1
 *
 * @param string $phone_number The unsanitized phone value.
 *
 * @return string
 */
function sanitize_phone_number( $phone_number ) {
	$digits = preg_replace( '/[^0-9]/', '', $phone_number );

	if ( strlen( $digits ) !== 10 && strlen( $digits ) !== 14 ) {
		return '';
	}

	$area_code = substr( $digits, 0, 3 );
	$prefix = substr( $digits, 3, 3 );
	$line_number = substr( $digits, 6, 4 );
	$sanitized_phone_number = '(' . $area_code . ') ' . $prefix . '-' . $line_number;

	if ( strlen( $digits ) === 14 ) {
		$extension = substr( $digits, -4 );
		$sanitized_phone_number .= ', ext. ' . $extension;
	}

	return $sanitized_phone_number;
}

/**
 * Saves additional data for an event.
 *
 * @since 0.0.1
 *
 * @param int     $post_id
 * @param \WP_Post $post
 */
function save_post( $post_id, $post ) {
	if ( ! isset( $_POST['wsuwp_event_nonce'] ) || ! wp_verify_nonce( $_POST['wsuwp_event_nonce'], 'wsuwp_event' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( 'auto-draft' === $post->post_status ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$keys = get_registered_meta_keys( 'post' );

	foreach ( post_meta_keys() as $key => $args ) {
		if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] && isset( $args['sanitize_callback'] ) ) {
			update_post_meta( $post_id, $key, $_POST[ $key ] );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}

/**
 * Enqueue scripts
 *
 * @since 0.0.1
 */
function admin_enqueue_scripts() {

	// Bail if not an event post type.
	if ( ! post_type_supports( get_post_type(), 'events' ) ) {
		return;
	}

	wp_enqueue_style( 'wsuwp_event_calendar', plugins_url( '/css/edit-post.css', dirname( __FILE__ ) ) );
}
