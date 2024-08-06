<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles custom field typea.
 */
class Business_Manager_User_Field extends CMB2_Type_Base {
	public static function init_users() {
		add_filter( 'cmb2_render_class_users', [ __CLASS__, 'class_name' ] );
		add_filter( 'cmb2_sanitize_users', [ __CLASS__, 'maybe_save_split_values' ], 12, 4 );
		/**
		 * The following snippets are required for allowing the users field
		 * to work as a repeatable field, or in a repeatable group.
		 */
		add_filter( 'cmb2_sanitize_users', [ __CLASS__, 'sanitize' ], 10, 5 );
		add_filter( 'cmb2_types_esc_users', [ __CLASS__, 'escape' ], 10, 4 );
	}

	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Handles outputting the users field.
	 */
	public function render() {
		// make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$this->field->escaped_value(),
			[
				'name'       => '',
				'email'      => '',
				'phone'      => '',
				'department' => '',
			]
		);

		ob_start();
		// Do html?>
		<div><label for="<?php echo esc_attr( $this->_id( '_name' ) ); ?>"><?php echo esc_html( $this->_text( 'users_name_text', __( 'Name', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[name]' ) ),
					'id'    => esc_attr( $this->_id( '_name' ) ),
					'value' => $value['name'],
					'desc'  => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_email' ) ); ?>'"><?php echo esc_html( $this->_text( 'users_email_text', __( 'Email', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[email]' ) ),
					'id'    => esc_attr( $this->_id( '_email' ) ),
					'value' => $value['email'],
					'desc'  => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_phone' ) ); ?>'"><?php echo esc_html( $this->_text( 'users_phone_text', __( 'Phone', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[phone]' ) ),
					'id'    => esc_attr( $this->_id( '_phone' ) ),
					'value' => $value['phone'],
					'desc'  => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_department' ) ); ?>'"><?php echo esc_html( $this->_text( 'users_department_text', __( 'Department', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[department]' ) ),
					'id'    => esc_attr( $this->_id( '_department' ) ),
					'value' => $value['department'],
					'desc'  => '',
				]
			);
			?>
		</div>
		
		<p class="clear">
			<?php echo esc_html( $this->_desc() ); ?>
		</p>
		<?php
		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	/**
	 * Optionally save the Address values into separate fields.
	 */
	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}
		$users_keys = [ 'name', 'phone', 'email', 'department' ];
		foreach ( $users_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'users_' . $key, sanitize_text_field( $value[ $key ] ) );
			}
		}
		remove_filter( 'cmb2_sanitize_users', [ __CLASS__, 'sanitize' ], 10, 5 );
		// Tell CMB2 we already did the update
		return true;
	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
		// if not repeatable, bail out.

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}
		return array_filter( $meta_value );
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}
		return array_filter( $meta_value );
	}
}

/**
 * Handles custom field typea.
 */
class Business_Manager_Ratings_Field extends CMB2_Type_Base {
	public static function init_ratings() {
		add_filter( 'cmb2_render_class_ratings', [ __CLASS__, 'class_name' ] );
		add_filter( 'cmb2_sanitize_ratings', [ __CLASS__, 'maybe_save_split_values' ], 12, 4 );
		/**
		 * The following snippets are required for allowing the ratings field
		 * to work as a repeatable field, or in a repeatable group.
		 */
		add_filter( 'cmb2_sanitize_ratings', [ __CLASS__, 'sanitize' ], 10, 5 );
		add_filter( 'cmb2_types_esc_ratings', [ __CLASS__, 'escape' ], 10, 4 );
	}

	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Handles outputting the ratings field.
	 */
	public function render() {
		// make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$this->field->escaped_value(),
			[
				'item'     => '',
				'rating'   => '',
				'comments' => '',
			]
		);

		$ratings = $this->field->args( 'ratings_options', [] );
		if ( empty( $ratings ) ) {
			$ratings = business_manager_dropdown_ratings();
		}
		// Add the "label" option. Can override via the field text param
		$ratings = [ '' => esc_html( $this->_text( 'ratings_select_rating_text', '' ) ) ] + $ratings;

		$rating_options = '';
		foreach ( $ratings as $abrev => $state ) {
			$rating_options .= '<option value="' . esc_attr( $abrev ) . '" ' . esc_attr( selected( $value['rating'], $abrev, false ) ) . '>' . esc_html( $state ) . '</option>';
		}

		ob_start();
		// Do html
		?>
		<div class="bm-employee-review-rating bm-field-review-item"><label for="<?php echo esc_attr( $this->_id( '_item' ) ); ?>"><?php echo esc_html( $this->_text( 'ratings_item_text', __( 'Item', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[item]' ) ),
					'id'    => esc_attr( $this->_id( '_item' ) ),
					'value' => $value['item'],
					'desc'  => '',
				]
			);
			?>
		</div>
		<div class="bm-employee-review-rating bm-field-review-rating"><label for="<?php echo esc_attr( $this->_id( '_rating' ) ); ?>'"><?php echo esc_html( $this->_text( 'ratings_rating_text', __( 'Rating', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->select(
				[
					'name'    => esc_attr( $this->_name( '[rating]' ) ),
					'id'      => esc_attr( $this->_id( '_rating' ) ),
					'options' => $rating_options,
					'desc'    => '',
				]
			);
			?>
		</div>
		<div class="bm-employee-review-rating"><label for="<?php echo esc_attr( $this->_id( '_comments' ) ); ?>'"><?php echo esc_html( $this->_text( 'ratings_comments_text', __( 'Comments', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->textarea(
				[
					'name'  => esc_attr( $this->_name( '[comments]' ) ),
					'id'    => esc_attr( $this->_id( '_comments' ) ),
					'value' => $value['comments'],
					'desc'  => '',
					'rows'  => 4,
				]
			);
			?>
		</div>

		
		<p class="clear">
			<?php echo esc_html( $this->_desc() ); ?>
		</p>
		<?php
		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	/**
	 * Optionally save the Address values into separate fields.
	 */
	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}
		$ratings_keys = [ 'item', 'rating', 'comments' ];
		foreach ( $ratings_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'ratings_' . $key, sanitize_text_field( $value[ $key ] ) );
			}
		}
		remove_filter( 'cmb2_sanitize_ratings', [ __CLASS__, 'sanitize' ], 10, 5 );
		// Tell CMB2 we already did the update
		return true;
	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
		// if not repeatable, bail out.

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}
		return array_filter( $meta_value );
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}
		return array_filter( $meta_value );
	}
}

/**
 * Handles custom field typea.
 */
class Business_Manager_Goal_Field extends CMB2_Type_Base {
	public static function init_goals() {
		add_filter( 'cmb2_render_class_goals', [ __CLASS__, 'class_name' ] );
		add_filter( 'cmb2_sanitize_goals', [ __CLASS__, 'maybe_save_split_values' ], 12, 4 );
		/**
		 * The following snippets are required for allowing the goals field
		 * to work as a repeatable field, or in a repeatable group.
		 */
		add_filter( 'cmb2_sanitize_goals', [ __CLASS__, 'sanitize' ], 10, 5 );
		add_filter( 'cmb2_types_esc_goals', [ __CLASS__, 'escape' ], 10, 4 );
	}

	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Handles outputting the goals field.
	 */
	public function render() {
		// make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$this->field->escaped_value(),
			[
				'goal'      => '',
				'benchmark' => '',
				'resources' => '',
				'date'      => '',
			]
		);

		ob_start();
		// Do html
		?>
		<div class="bm-employee-review-goal"><label for="<?php echo esc_attr( $this->_id( '_goal' ) ); ?>"><?php echo esc_html( $this->_text( 'goals_goal_text', __( 'Goal', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->textarea(
				[
					'name'  => esc_attr( $this->_name( '[goal]' ) ),
					'id'    => esc_attr( $this->_id( '_goal' ) ),
					'value' => $value['goal'],
					'rows'  => 3,
				]
			);
			?>
			<p class="cmb2-metabox-description"><?php _e( 'What is the goal you want to achieve? It should be measurable.', 'business-manager' ); ?></p>
		</div>
		<div class="bm-employee-review-goal"><label for="<?php echo esc_attr( $this->_id( '_benchmark' ) ); ?>'"><?php echo esc_html( $this->_text( 'goals_benchmark_text', __( 'Benchmark', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->textarea(
				[
					'name'  => esc_attr( $this->_name( '[benchmark]' ) ),
					'id'    => esc_attr( $this->_id( '_benchmark' ) ),
					'value' => $value['benchmark'],
					'rows'  => 3,
				]
			);
			?>
			<p class="cmb2-metabox-description"><?php _e( 'What is the current measurable benchmark for this goal?', 'business-manager' ); ?></p>
		</div>
		<div class="bm-employee-review-goal"><label for="<?php echo esc_attr( $this->_id( '_resources' ) ); ?>'"><?php echo esc_html( $this->_text( 'goals_resources_text', __( 'Resources', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->textarea(
				[
					'name'  => esc_attr( $this->_name( '[resources]' ) ),
					'id'    => esc_attr( $this->_id( '_resources' ) ),
					'value' => $value['resources'],
					'rows'  => 3,
				]
			);
			?>
			<p class="cmb2-metabox-description"><?php _e( 'List resources required to achieve the goal.', 'business-manager' ); ?></p>
		</div>
		<div class="bm-employee-review-goal"><label for="<?php echo esc_attr( $this->_id( '_time' ) ); ?>'"><?php echo esc_html( $this->_text( 'goals_date_text', __( 'Date', 'business-manager' ) ) ); ?></label>
			<?php
			echo @$this->types->text_date_timestamp(
				[
					'name'         => esc_attr( $this->_name( '[date]' ) ),
					'id'           => esc_attr( $this->_id( '_date' ) ),
					'value'        => $value['date'],
					'autocomplete' => 'off',
				]
			);
			?>
			<p class="cmb2-metabox-description"><?php _e( 'When is the goal due to be completed by?', 'business-manager' ); ?></p>
		</div>

		<?php
		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	/**
	 * Optionally save the Address values into separate fields.
	 */
	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}
		$goals_keys = [ 'goal', 'benchmark', 'resources', 'date' ];
		foreach ( $goals_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'goals_' . $key, sanitize_text_field( $value[ $key ] ) );
			}
		}
		remove_filter( 'cmb2_sanitize_goals', [ __CLASS__, 'sanitize' ], 10, 5 );
		// Tell CMB2 we already did the update
		return true;
	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
		// if not repeatable, bail out.

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			// change to timestamp
			if ( isset( $val['date'] ) && ! empty( $val['date'] ) ) {
				$value       = wp_unslash( $val['date'] );
				$date_object = date_create_from_format( $field_args['date_format'], $value );
				$val['date'] = $date_object ? $date_object->setTime( 0, 0, 0 )->getTimeStamp() : strtotime( $value );
			}
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}

		return array_filter( $meta_value );
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}

		foreach ( $meta_value as $key => $val ) {
			if ( isset( $val['date'] ) && ! empty( $val['date'] ) ) {
				$val['date'] = $field_object->get_timestamp_format( 'date_format', $val['date'] );
			}
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}
		return $meta_value;
	}
}

/**
 * Handles custom field typea.
 */
class Business_Manager_Document_Field extends CMB2_Type_Base {
	public static function init_documents() {
		add_filter( 'cmb2_render_class_documents', [ __CLASS__, 'class_name' ] );
		add_filter( 'cmb2_sanitize_documents', [ __CLASS__, 'maybe_save_split_values' ], 12, 4 );
		/**
		 * The following snippets are required for allowing the documents field
		 * to work as a repeatable field, or in a repeatable group.
		 */
		add_filter( 'cmb2_sanitize_documents', [ __CLASS__, 'sanitize' ], 10, 5 );
		add_filter( 'cmb2_types_esc_documents', [ __CLASS__, 'escape' ], 10, 4 );
	}

	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Handles outputting the documents field.
	 */
	public function render() {
		// make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$this->field->escaped_value(),
			[
				'name'    => '',
				'date'    => '',
				'version' => '',
				'file'    => '',
			]
		);

		$employees = $this->field->args( 'employees_options', [] );
		if ( empty( $employees ) ) {
			$employees = business_manager_dropdown_get_employee();
		}
		// Add the "label" option. Can override via the field text param
		$employees = [ '' => esc_html( $this->_text( 'employees_select_name_text', '' ) ) ] + $employees;

		$employees_options = '';
		foreach ( $employees as $abrev => $emp ) {
			$employees_options .= '<option value="' . esc_attr( $abrev ) . '" ' . esc_attr( selected( $value['name'], $abrev, false ) ) . '>' . $emp . '</option>';
		}

		ob_start();
		// Do html
		?>
		<div><label for="<?php echo esc_attr( $this->_id( '_name' ) ); ?>"><?php echo esc_html( $this->_text( 'documents_name_text', __( 'By', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->select(
				[
					'name'    => esc_attr( $this->_name( '[name]' ) ),
					'id'      => esc_attr( $this->_id( '_name' ) ),
					'options' => $employees_options,
					'desc'    => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_date' ) ); ?>'"><?php echo esc_html( $this->_text( 'documents_date_text', __( 'Date', 'business-manager' ) ) ); ?></label>
			<?php
			echo @$this->types->text_date_timestamp(
				[
					'name'         => esc_attr( $this->_name( '[date]' ) ),
					'id'           => esc_attr( $this->_id( '_date' ) ),
					'value'        => $value['date'],
					'autocomplete' => 'off',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_version' ) ); ?>'"><?php echo esc_html( $this->_text( 'documents_version_text', __( 'Version', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->input(
				[
					'name'  => esc_attr( $this->_name( '[version]' ) ),
					'id'    => esc_attr( $this->_id( '_version' ) ),
					'value' => $value['version'],
					'desc'  => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_file' ) ); ?>'"><?php echo esc_html( $this->_text( 'documents_file_text', __( 'File', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->file(
				[
					'name'  => esc_attr( $this->_name( '[file]' ) ),
					'id'    => $this->_id( '_file' ),
					'value' => $value['file'],
					'desc'  => '',
				]
			);
			?>
		</div>
		
		<p class="clear">
			<?php echo $this->_desc(); ?>
		</p>
		<?php
		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	/**
	 * Optionally save the Address values into separate fields.
	 */
	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}
		$documents_keys = [ 'name', 'date', 'version', 'file' ];
		foreach ( $documents_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'documents_' . $key, sanitize_text_field( $value[ $key ] ) );
			}
		}
		remove_filter( 'cmb2_sanitize_documents', [ __CLASS__, 'sanitize' ], 10, 5 );
		// Tell CMB2 we already did the update
		return true;
	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
		// if not repeatable, bail out.

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}
		return array_filter( $meta_value );
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}
		return array_filter( $meta_value );
	}
}

/**
 * Handles custom field typea.
 */
class Business_Manager_Notes_Field extends CMB2_Type_Base {
	public static function init_notes() {
		add_filter( 'cmb2_render_class_notes', [ __CLASS__, 'class_name' ] );
		add_filter( 'cmb2_sanitize_notes', [ __CLASS__, 'maybe_save_split_values' ], 12, 4 );
		/**
		 * The following snippets are required for allowing the notes field
		 * to work as a repeatable field, or in a repeatable group.
		 */
		add_filter( 'cmb2_sanitize_notes', [ __CLASS__, 'sanitize' ], 10, 5 );
		add_filter( 'cmb2_types_esc_notes', [ __CLASS__, 'escape' ], 10, 4 );
	}

	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Handles outputting the notes field.
	 */
	public function render() {
		// make sure we assign each part of the value we need.
		$value = wp_parse_args(
			$this->field->escaped_value(),
			[
				'note' => '',
				'date' => '',
			]
		);

		ob_start();
		// Do html
		?>

		<div>
		<label for="<?php echo esc_attr( $this->_id( '_note' ) ); ?>'"><?php echo esc_html( $this->_text( 'notes_note_text', __( 'Note', 'business-manager' ) ) ); ?></label>
			<?php
			echo $this->types->textarea(
				[
					'name'  => $this->_name( '[note]' ),
					'id'    => $this->_id( '_note' ),
					'value' => $value['note'],
					'rows'  => 4,
					'desc'  => '',
				]
			);
			?>
		</div>
		<div><label for="<?php echo esc_attr( $this->_id( '_date' ) ); ?>'"><?php echo esc_html( $this->_text( 'notes_date_text', __( 'Date Added', 'business-manager' ) ) ); ?></label>
			<?php
			echo @$this->types->text_date_timestamp(
				[
					'name'         => $this->_name( '[date]' ),
					'id'           => $this->_id( '_date' ),
					'value'        => $value['date'],
					'desc'         => '',
					'autocomplete' => 'off',
				]
			);
			?>
		</div>

		
		<p class="clear">
			<?php echo $this->_desc(); ?>
		</p>
		<?php
		// grab the data from the output buffer.
		return $this->rendered( ob_get_clean() );
	}

	/**
	 * Optionally save the Address values into separate fields.
	 */
	public static function maybe_save_split_values( $override_value, $value, $object_id, $field_args ) {
		if ( ! isset( $field_args['split_values'] ) || ! $field_args['split_values'] ) {
			// Don't do the override
			return $override_value;
		}
		$notes_keys = [ 'note', 'date' ];
		foreach ( $notes_keys as $key ) {
			if ( ! empty( $value[ $key ] ) ) {
				update_post_meta( $object_id, $field_args['id'] . 'notes_' . $key, sanitize_text_field( $value[ $key ] ) );
			}
		}
		remove_filter( 'cmb2_sanitize_notes', [ __CLASS__, 'sanitize' ], 10, 5 );
		// Tell CMB2 we already did the update
		return true;
	}

	public static function sanitize( $check, $meta_value, $object_id, $field_args, $sanitize_object ) {
		// if not repeatable, bail out.

		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'sanitize_text_field', $val ) );
		}
		return array_filter( $meta_value );
	}

	public static function escape( $check, $meta_value, $field_args, $field_object ) {
		// if not repeatable, bail out.
		if ( ! is_array( $meta_value ) || ! $field_args['repeatable'] ) {
			return $check;
		}
		foreach ( $meta_value as $key => $val ) {
			$meta_value[ $key ] = array_filter( array_map( 'esc_attr', $val ) );
		}
		return array_filter( $meta_value );
	}
}
