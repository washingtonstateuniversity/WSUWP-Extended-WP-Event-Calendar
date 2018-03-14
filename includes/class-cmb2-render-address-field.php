<?php
/**
 * This code is a fork of https://github.com/CMB2/CMB2-Snippet-Library/tree/master/custom-field-types/address-field-type
 * with a handful of WCPS coding standards applied.
 */

/**
 * Handles 'address' custom field type.
 */
class CMB2_Render_Address_Field extends CMB2_Type_Base {

	/**
	 * List of states. To translate, pass array of states in the 'state_list' field param.
	 *
	 * @var array
	 */
	protected static $state_list = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District Of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
	);

	public static function init() {
		add_filter( 'cmb2_render_class_address', array( __CLASS__, 'class_name' ) );
		add_filter( 'cmb2_sanitize_address', array( __CLASS__, 'sanitize' ), 12, 4 );
	}

	/**
	 * Return the name of this class so that CMB2 can initiate the default
	 * renderer, which is the static render() method on this class.
	 *
	 * @return string
	 */
	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Render the address field on the post edit screen.
	 */
	public function render() {
		$object_id = $this->field->object_id;

		// Define the expected data associated with an address.
		$address_data = array(
			'address-1' => '',
			'address-2' => '',
			'city' => '',
			'state' => '',
			'zip' => '',
			'latitude' => '',
			'longitude' => '',
		);

		foreach ( $address_data as $key => $value ) {
			$address_data[ $key ] = esc_attr( get_post_meta( $object_id, $this->field->args( 'id' ) . '_' . $key, true ) );
		}

		if ( ! $this->field->args( 'do_country' ) ) {
			$state_list = $this->field->args( 'state_list', array() );
			if ( empty( $state_list ) ) {
				$state_list = self::$state_list;
			}

			// Add the "label" option. Can override via the field text param
			$state_list = array(
				'' => esc_html( $this->_text( 'address_select_state_text', 'Select a State' ) ),
			) + $state_list;

			$state_options = '';
			foreach ( $state_list as $abrev => $state ) {
				$state_options .= '<option value="' . $abrev . '" ' . selected( $address_data['state'], $abrev, false ) . '>' . $state . '</option>';
			}
		}

		$state_label = 'State';
		if ( $this->field->args( 'do_country' ) ) {
			$state_label .= '/Province';
		}

		ob_start();
		// Do html
		?>
		<div><p><label for="<?php echo esc_attr( $this->_id( '_address_1', false ) ); ?>"><?php echo esc_html( $this->_text( 'address_address_1_text', 'Address 1' ) ); ?></label></p>
			<?php echo $this->types->input( // WPCS: XSS Ok.
				array(
					'name'  => $this->_name( '[address-1]' ),
					'id'    => $this->_id( '_address_1' ),
					'value' => $address_data['address-1'],
					'desc'  => '',
				)
			); ?>
		</div>
		<div><p><label for="<?php echo esc_attr( $this->_id( '_address_2', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_address_2_text', 'Address 2' ) ); ?></label></p>
			<?php echo $this->types->input( // WPCS: XSS Ok.
				array(
					'name'  => $this->_name( '[address-2]' ),
					'id'    => $this->_id( '_address_2' ),
					'value' => $address_data['address-2'],
					'desc'  => '',
				)
			); ?>
		</div>
		<div style="overflow: hidden;">
			<div class="alignleft"><p><label for="<?php echo esc_attr( $this->_id( '_city', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_city_text', 'City' ) ); ?></label></p>
				<?php echo $this->types->input( // WPCS: XSS Ok.
					array(
						'class' => 'cmb_text_small',
						'name'  => $this->_name( '[city]' ),
						'id'    => $this->_id( '_city' ),
						'value' => $address_data['city'],
						'desc'  => '',
					)
				); ?>
			</div>
			<div class="alignleft"><p><label for="<?php echo esc_attr( $this->_id( '_state', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_state_text', $state_label ) ); ?></label></p>
				<?php if ( $this->field->args( 'do_country' ) ) : ?>
					<?php echo $this->types->input( // WPCS: XSS Ok.
						array(
							'class' => 'cmb_text_small',
							'name'  => $this->_name( '[state]' ),
							'id'    => $this->_id( '_state' ),
							'value' => $address_data['state'],
							'desc'  => '',
						)
					); ?>
				<?php else : ?>
					<?php echo $this->types->select( // WPCS: XSS Ok.
						array(
							'name'    => $this->_name( '[state]' ),
							'id'      => $this->_id( '_state' ),
							'options' => $state_options,
							'desc'    => '',
						)
					); ?>
				<?php endif; ?>
			</div>
			<div class="alignleft"><p><label for="<?php echo esc_attr( $this->_id( '_zip', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_zip_text', 'Zip' ) ); ?></label></p>
				<?php echo $this->types->input( // WPCS: XSS Ok.
					array(
						'class' => 'cmb_text_small',
						'name'  => $this->_name( '[zip]' ),
						'id'    => $this->_id( '_zip' ),
						'value' => $address_data['zip'],
						'type'  => 'number',
						'desc'  => '',
					)
				); ?>
			</div>
		</div>
		<?php if ( $this->field->args( 'do_country' ) ) : ?>
			<div class="clear"><p><label for="<?php echo esc_attr( $this->_id( '_country', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_country_text', 'Country' ) ); ?></label></p>
				<?php echo $this->types->input( // WPCS: XSS Ok.
					array(
						'name'  => $this->_name( '[country]' ),
						'id'    => $this->_id( '_country' ),
						'value' => $address_data['country'],
						'desc'  => '',
					)
				); ?>
			</div>
		<?php endif; ?>
		<p class="clear">
			<?php echo $this->_desc(); // WPCS: XSS Ok. ?>
		</p>
		<div class="alignleft"><p><label for="<?php echo esc_attr( $this->_id( '_latitude', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_latitude_text', 'Latitude' ) ); ?></label></p>
			<?php echo $this->types->input( // WPCS: XSS Ok.
				array(
					'class' => 'cmb_text_small',
					'name'  => $this->_name( '[latitude]' ),
					'id'    => $this->_id( '_latitude' ),
					'value' => $address_data['latitude'],
					'desc'  => '',
				)
			); ?>
		</div>
		<div class="alignleft"><p><label for="<?php echo esc_attr( $this->_id( '_longitude', false ) ); ?>'"><?php echo esc_html( $this->_text( 'address_longitude_text', 'Longitude' ) ); ?></label></p>
			<?php echo $this->types->input( // WPCS: XSS Ok.
				array(
					'class' => 'cmb_text_small',
					'name'  => $this->_name( '[longitude]' ),
					'id'    => $this->_id( '_longitude' ),
					'value' => $address_data['longitude'],
					'desc'  => '',
				)
			); ?>
		</div>
		<?php

		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() ); // WPCS: XSS Ok.
	}

	/**
	 * Store individual pieces of address data in their own meta keys.
	 *
	 * This sanitizes the data while also short-circuiting the sanitization process.
	 *
	 * @param null  $override_value
	 * @param array $value
	 * @param int   $object_id
	 * @param array $field_args
	 *
	 * @return bool
	 */
	public static function sanitize( $override_value, $value, $object_id, $field_args ) {
		$address_keys = array(
			'address-1',
			'address-2',
			'city',
			'state',
			'zip',
			'latitude',
			'longitude',
		);

		foreach ( $address_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				// Basic validation of latitude/longitude.
				if ( in_array( $key, array( 'latitude', 'longitude' ), true ) ) {
					$value[ $key ] = (float) filter_var( $value[ $key ], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				}
				update_post_meta( $object_id, $field_args['id'] . '_' . $key, sanitize_text_field( $value[ $key ] ) );
			} else {
				delete_post_meta( $object_id, $field_args['id'] . '_' . $key );
			}
		}

		remove_filter( 'cmb2_sanitize_address', array( __CLASS__, 'sanitize' ), 10 );

		// Tell CMB2 we already did the update
		return true;
	}
}
