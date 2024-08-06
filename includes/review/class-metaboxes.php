<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Review_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Review_Metaboxes {

        /**
         * The post type associated with the review.
         *
         * @var string
         */
        public $post_type = 'bm-review';

        /**
         * The date format used for the review.
         *
         * @var string
         */
        public $date_format = '';

        /**
         * The prefix used for generating keys or identifiers related to the review.
         *
         * @var string
         */
        private $pre = '_bm_review_';

        /**
         * Class constructor for initializing the Review instance.
         */
		public function __construct() {
			$this->date_format = business_manager_datepicker_format();

			$this->hooks();
		}

		/**
		 * Hook in to actions & filters.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'cmb2_admin_init', [ $this, 'metabox_details' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_ratings' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_summary' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_goals' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_files' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_notes' ] );

			add_action( 'cmb2_admin_init', [ $this, 'init_custom_fields' ] );

			$reviewer_field = $this->pre . 'reviewer';
			add_action( "cmb2_override_{$reviewer_field}_meta_value", [ $this, 'override_reviewer_field_value' ], 10, 4 );
		}

		/**
         * Override the value of a reviewer field for a review post.
         *
         * This method allows developers to override the value of a specific field for a review post.
         *
         * @param mixed        $value      The original value of the field.
         * @param int          $object_id  The ID of the object (post) being edited.
         * @param array|string $args       An array of arguments for the field.
         * @param string       $field      The name of the field being edited.
         *
         * @return mixed The modified value of the field.
         */
		public function override_reviewer_field_value( $value, $object_id, $args, $field ) {
			$reviewers = get_post_meta( $object_id, $this->pre . 'reviewer', true );
			if ( ! $reviewers ) {
				return $value;
			}
			if ( ! is_array( $reviewers ) ) {
				$value = [ $reviewers ]; // if string was saved previously, make it an array.
			}
			return $value;
		}

        /**
         * Initialize custom fields for the Review.
         */
		public function init_custom_fields() {
			require_once BUSINESSMANAGER_DIR . 'includes/class-fields.php';
			Business_Manager_Ratings_Field::init_ratings();
			Business_Manager_Goal_Field::init_goals();
			Business_Manager_Notes_Field::init_notes();
		}

        /**
         * Define and configure a details metabox for the Review.
         */
		public function metabox_details() {
			$id = $this->pre . 'details_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-admin-settings"></span> ' . __( 'Details', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'       => __( 'Employee', 'business-manager' ),
				'id'         => $this->pre . 'employee',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_get_employee',
			];

			$fields['general'][] = [
				'name'       => __( 'Reviewer', 'business-manager' ),
				'id'         => $this->pre . 'reviewer',
				'type'       => 'pw_multiselect', // @since 1.5.5 conversion of dropdown to multiselect
				'options_cb' => 'business_manager_dropdown_get_employee',
			];

			$fields['general'][] = [
				'name'        => __( 'Date of Review', 'business-manager' ),
				'id'          => $this->pre . 'date',
				'type'        => 'text_date_timestamp',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
				'classes'     => 'bm-field-half-1',
			];

			$fields['general'][] = [
				'name'        => __( 'Next Review', 'business-manager' ),
				'id'          => $this->pre . 'next_date',
				'type'        => 'text_date_timestamp',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
				'classes'     => 'bm-field-half-2',
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}

        /**
         * Define and configure a notes metabox for the Review.
         */
		public function metabox_notes() {
			$id = $this->pre . 'notes_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-welcome-write-blog"></span> ' . __( 'Notes', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'        => __( 'Review Notes', 'business-manager' ),
				'id'          => $this->pre . 'notes',
				'type'        => 'notes',
				'classes'     => 'bm-field-full',
				'repeatable'  => true,
				'date_format' => $this->date_format,
				'options'     => [
					'add_row_text' => __( 'Add Note', 'business-manager' ),
				],
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}

        /**
         * Define and configure a files metabox for the Review.
         */
		public function metabox_files() {
			$id = $this->pre . 'file_list_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-portfolio"></span> ' . __( 'Files', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'    => __( 'Review Files', 'business-manager' ),
				'id'      => $this->pre . 'files',
				'type'    => 'file_list',
				'options' => [
					'url' => false,
				],
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}

        /**
         * Define and configure a ratings metabox for the Review.
         */
		public function metabox_ratings() {
			$id = $this->pre . 'ratings_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-star-filled"></span> ' . __( 'Ratings', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'       => __( 'Ratings', 'business-manager' ),
				'id'         => $this->pre . 'ratings',
				'type'       => 'ratings',
				'repeatable' => true,
				'classes'    => 'bm-repeat',
				'text'       => [
					'add_row_text' => __( 'Add Rating', 'business-manager' ),
				],
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}

        /**
         * Define and configure a summary metabox for the Review.
         */
		public function metabox_summary() {
			$id = $this->pre . 'summary_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-format-status"></span> ' . __( 'Summary', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'       => __( 'Strong points', 'business-manager' ),
				'desc'       => __( 'What are the employee\'s strongest points?', 'business-manager' ),
				'id'         => $this->pre . 'strong_points',
				'type'       => 'textarea',
				'attributes' => [
					'rows' => 6,
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Weak points', 'business-manager' ),
				'desc'       => __( 'What are the employee\'s weakest points?', 'business-manager' ),
				'id'         => $this->pre . 'weak_points',
				'type'       => 'textarea',
				'attributes' => [
					'rows' => 6,
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Improvements', 'business-manager' ),
				'desc'       => __( 'What can the employee\'s do to be more effective or make improvements?', 'business-manager' ),
				'id'         => $this->pre . 'improvements',
				'type'       => 'textarea',
				'attributes' => [
					'rows' => 6,
				],
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}

        /**
         * Define and configure a goals metabox for the Review.
         */
		public function metabox_goals() {
			$id = $this->pre . 'goals_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-chart-bar"></span> ' . __( 'Goals', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'        => __( 'Goals', 'business-manager' ),
				'id'          => $this->pre . 'goals',
				'type'        => 'goals',
				'repeatable'  => true,
				'classes'     => 'bm-repeat',
				'date_format' => $this->date_format,
				'text'        => [
					'add_row_text' => __( 'Add Goal', 'business-manager' ),
				],
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'general',
				'title'  => __( 'General', 'business-manager' ),
				'fields' => $fields['general'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// set tabs.
			$box->add_field(
                [
					'id'   => '__tabs',
					'type' => 'tabs',
					'tabs' => $tabs_setting,
				]
            );
		}
	}

endif;

return new Business_Manager_Review_Metaboxes();
