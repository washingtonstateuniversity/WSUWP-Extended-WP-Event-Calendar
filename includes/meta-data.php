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
		'_wsuwp_event_location_notes' => array(
			'type' => 'string',
			'sanitize_callback' => 'wp_kses_post',
		),
		'_wsuwp_event_contact_name' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsuwp_event_contact_email' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_email',
		),
		'_wsuwp_event_contact_phone' => array(
			'type' => 'string',
			'sanitize_callback' => 'WSU\Events\Meta_Data\sanitize_phone_number',
		),
		'_wsuwp_event_action_text' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		),
		'_wsuwp_event_action_url' => array(
			'type' => 'string',
			'sanitize_callback' => 'esc_url_raw',
		),
		'_wsuwp_event_cost' => array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
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
		'wp_event_calendar_event_featured_categories',
		__( 'Featured Event Categories', 'wp-event-calendar' ),
		'WSU\Events\Meta_Data\display_featured_categories',
		wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'high'
	);

	remove_meta_box(
		'wp_event_calendar_details',
		\wp_event_calendar_allowed_post_types(),
		'above_event_editor'
	);

	add_meta_box(
		'wp_event_calendar_details',
		__( 'Details', 'wp-event-calendar' ),
		'wp_event_calendar_details_metabox',
		wp_event_calendar_allowed_post_types(),
		'above_event_editor',
		'low'
	);

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

	$notes = get_post_meta( $post->ID, '_wsuwp_event_location_notes', true );

	$event_venue = wp_get_post_terms( $post->ID, 'venue-tax' );

	if ( is_wp_error( $event_venue ) || empty( $event_venue ) ) {
		$event_venue = '';
	} else {
		$event_venue = $event_venue[0]->slug;
	}

	$venues = get_terms( array(
		'taxonomy' => 'venue-tax',
		'hide_empty' => true,
	) );

	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_venue">Venue</label>
			</th>
			<td>
				<select id="wsuwp_event_venue" name="_wsuwp_event_venue">
					<option value="" <?php selected( $event_venue, '', true ); ?>>--- Select Venue ---</option>
					<?php
					foreach ( $venues as $venue ) {
						?>
						<option value="<?php echo esc_attr( $venue->slug ); ?>" <?php selected( $event_venue, $venue->slug, true ); ?>><?php echo esc_html( $venue->name ); ?></option>
						<?php
					}
					?>
				</select>
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

				wp_editor( $notes, '_wsuwp_event_location_notes', $notes_editor_settings );
				?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Displays the meta box used to capture featured category data.
 *
 * @since 0.0.1
 *
 * @param \WP_Post $post
 */
function display_featured_categories( $post ) {

	$featured_events = array(
		1350 => 'Commencement',
	);

	$featured_event_terms = wp_get_post_terms( $post->ID, 'wsuwp_university_category', array( 'fields' => 'ids' ) );

	$campus = array(
		332 => 'WSU Pullman',
		334 => 'WSU Spokane',
		335 => 'WSU Tri-Cities',
		336 => 'WSU Vancouver',
		337 => 'WSU Global Campus',
		340 => 'WSU Everett',
		338 => 'WSU Extension',
	);

	$campus_terms = wp_get_post_terms( $post->ID, 'wsuwp_university_location', array( 'fields' => 'ids' ) );

	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="_wsuwp_event_campus">Campus/Location</label>
			</th>
			<td>
			<fieldset>      
				<?php foreach ( $campus as $campus_id => $campus_label ) : ?>  
				<input type="checkbox" name="tax_input[wsuwp_university_location][]" value="<?php echo esc_attr( $campus_id ); ?>" <?php if ( in_array( $campus_id, $campus_terms ) ) : ?>checked="checked"<?php endif; ?>><?php echo wp_kses_post( $campus_label ); ?><br>          
				<?php endforeach; ?>    
			</fieldset>
			</td>
		</tr>
		<tr>
			<th>
				<label for="_wsuwp_event_featured_events">Featured Event Categories</label>
			</th>
			<td>
			<fieldset>      
				<?php foreach ( $featured_events as $featured_event_id => $featured_event_label ) : ?>  
				<input type="checkbox" name="tax_input[wsuwp_university_category][]" value="<?php echo esc_attr( $featured_event_id ); ?>" <?php if ( in_array( $featured_event_id, $featured_event_terms ) ) : ?>checked="checked"<?php endif; ?>><?php echo wp_kses_post( $featured_event_label ); ?><br>          
				<?php endforeach; ?>    
			</fieldset>
			</td>
		</tr>
	</table>
	<strong>NOTE:</strong> To remove a category it must be unchecked above <strong>AND</strong> removed in the right column. 
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
	$name = get_post_meta( $post->ID, '_wsuwp_event_contact_name', true );
	$email = get_post_meta( $post->ID, '_wsuwp_event_contact_email', true );
	$phone = get_post_meta( $post->ID, '_wsuwp_event_contact_phone', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_contact_name">Name</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_contact_name"
					   name="_wsuwp_event_contact_name"
					   class="widefat"
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
					   name="_wsuwp_event_contact_email"
					   class="widefat"
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
					   name="_wsuwp_event_contact_phone"
					   placeholder="(555) 555-5555, ext. 5555"
					   class="widefat"
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
	$text = get_post_meta( $post->ID, '_wsuwp_event_action_text', true );
	$url = get_post_meta( $post->ID, '_wsuwp_event_action_url', true );
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
					   name="_wsuwp_event_action_text"
					   class="widefat"
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
					   name="_wsuwp_event_action_url"
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
	$cost = get_post_meta( $post->ID, '_wsuwp_event_cost', true );
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="wsuwp_event_cost">Price</label>
			</th>
			<td>
				<input type="text"
					   id="wsuwp_event_cost"
					   name="_wsuwp_event_cost"
					   class="widefat"
					   value="<?php echo esc_attr( $cost ); ?>" />
			</td>
		</tr>
	</table>
	<?php
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

	if ( isset( $_POST['_wsuwp_event_venue'] ) ) {
		if ( empty( $_POST['_wsuwp_event_venue'] ) ) {
			wp_set_post_terms( $post_id, '', 'venue-tax' );
		} else {
			$valid_venue = get_term_by( 'slug', sanitize_text_field( $_POST['_wsuwp_event_venue'] ), 'venue-tax' );

			if ( is_object( $valid_venue ) ) {
				wp_set_post_terms( $post_id, sanitize_text_field( $_POST['_wsuwp_event_venue'] ), 'venue-tax' );
			}
		}
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

	wp_enqueue_style( 'wsuwp_event_calendar', plugins_url( '/css/edit-post.css', dirname( __FILE__ ) ) ); // @codingStandardsIgnoreLine
}
