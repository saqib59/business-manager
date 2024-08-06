<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Client_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Client_Metaboxes {
		/**
		 * Post type.
         *
		 * @var string
		 */
		public $post_type = 'bm-client';

        /**
         * Description of the member variable.
         *
         * @var string
         */
		public $date_format = '';

		/**
		 * Metabox prefix.
		 *
         * @var string
		 * @since 1.0.0
		 */
		private $pre = '_bm_client_';

        /**
         * Class constructor.
         *
         * Initializes the object and sets up necessary actions and filters.
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
			add_action( 'cmb2_admin_init', [ $this, 'metabox_files' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_notes' ] );

			add_action( 'cmb2_admin_init', [ $this, 'init_custom_fields' ] );
		}

        /**
         * Initialize custom fields.
         */
		public function init_custom_fields() {
			require_once BUSINESSMANAGER_DIR . 'includes/class-fields.php';
			Business_Manager_User_Field::init_users();
			Business_Manager_Notes_Field::init_notes();
		}

        /**
         * Initialize metabox details metabox for cmb fields.
         */
		public function metabox_details() {
			$id = $this->pre . 'details_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-id-alt"></span> ' . __( 'Client Details', 'business-manager' ),
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

			$fields = [];

			$fields['main'][] = [
				'name'       => __( 'Address', 'business-manager' ),
				'id'         => $this->pre . 'address',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Address Line 2', 'business-manager' ),
				'id'         => $this->pre . 'address2',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'City', 'business-manager' ),
				'id'         => $this->pre . 'city',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'State/Province', 'business-manager' ),
				'id'         => $this->pre . 'state_province',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Zipcode', 'business-manager' ),
				'id'         => $this->pre . 'zipcode',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Country', 'business-manager' ),
				'id'         => $this->pre . 'country',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Tax ID', 'business-manager' ),
				'id'         => $this->pre . 'tax_id',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Phone', 'business-manager' ),
				'id'         => $this->pre . 'phone',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Email', 'business-manager' ),
				'id'         => $this->pre . 'email',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Website', 'business-manager' ),
				'id'         => $this->pre . 'website',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Working Hours', 'business-manager' ),
				'id'         => $this->pre . 'working_hours',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Working Days', 'business-manager' ),
				'id'         => $this->pre . 'working_days',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Main Contact Name', 'business-manager' ),
				'id'         => $this->pre . 'contact_name',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['main'][] = [
				'name'       => __( 'Main Contact Phone', 'business-manager' ),
				'id'         => $this->pre . 'contact_phone',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['staff'][] = [
				'name'        => __( 'Staff Members', 'business-manager' ),
				'id'          => $this->pre . 'staff',
				'type'        => 'users',
				'repeatable'  => true,
				'classes'     => 'bm-repeat',
				'date_format' => $this->date_format,
				'text'        => [
					'add_row_text' => __( 'Add Staff Member', 'business-manager' ),
				],
			];

			$fields['main'][] = [
				'name'         => __( 'Logo', 'business-manager' ),
				'id'           => $this->pre . 'logo',
				'type'         => 'file',
				'classes'      => 'bm-field-clear',
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => 'Add or Upload Logo',
				],
				'query_args'   => [
					'type' => [
						'image/gif',
						'image/jpeg',
						'image/png',
					],
				],
				'preview_size' => 'thumbnail',
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'main',
				'title'  => __( 'Main', 'business-manager' ),
				'fields' => $fields['main'],
			];

			$tabs_setting['tabs'][] = [
				'id'     => 'staff',
				'title'  => __( 'Staff', 'business-manager' ),
				'fields' => $fields['staff'],
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
         * Initialize metabox notes metabox for cmb fields.
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
				'name'        => __( 'Client Notes', 'business-manager' ),
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
         * Initialize metabox files metabox for cmb fields.
         */
		public function metabox_files() {
			$id = $this->pre . 'files_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-portfolio"></span> ' . __( 'Files & Documents', 'business-manager' ),
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
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'general_files',
				'type'    => 'file_list',
				'options' => [
					'url' => false,
				],
			];

			$fields['contracts'][] = [
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'contract_files',
				'desc'    => __( 'Contract documents & files', 'business-manager' ),
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
			$tabs_setting['tabs'][] = [
				'id'     => 'contracts',
				'title'  => __( 'Contracts', 'business-manager' ),
				'fields' => $fields['contracts'],
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

return new Business_Manager_Client_Metaboxes();
