<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Employee_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Employee_Metaboxes {
		/**
		 * Post type.
         *
		 * @var string
		 */
		public $post_type = 'bm-employee';

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
		 */
		private $pre = '_bm_employee_';

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
			add_action( 'cmb2_init', [ $this, 'metabox_access' ] );
			add_action( 'cmb2_init', [ $this, 'metabox_details' ] );
			add_action( 'cmb2_init', [ $this, 'metabox_files' ] );
			add_action( 'cmb2_init', [ $this, 'metabox_notes' ] );
			add_action( 'cmb2_init', [ $this, 'metabox_assets' ] );

			if ( business_manager_is_enabled( 'leave' ) ) {
				add_action( 'cmb2_init', [ $this, 'metabox_leave' ] );
			}

			add_action( 'cmb2_init', [ $this, 'init_custom_fields' ] );

			add_filter(
                'business_manager_disable_post_lock_disabled_lock_for_access',
                [ $this, 'add_edit_profile_access_in_disabled_post_lock' ],
                10,
                4
            );
		}

		/**
         * Add or edit profile access in disabled post lock.
         *
         * @param bool   $disabled_lock_access Whether access is disabled or not.
         * @param string $post_type            The post type.
         * @param int    $post_id              The post ID.
         * @param int    $bm_employee_id       The business manager employee ID.
         *
         * @return bool The updated value for disabled_lock_access.
         */
		public function add_edit_profile_access_in_disabled_post_lock( $disabled_lock_access, $post_type, $post_id, $bm_employee_id ) {

			if ( ! is_array( $disabled_lock_access ) ) {
				return $disabled_lock_access;
			}
			// when in other post type don't bother other post type will handle their locks.
			if ( $post_type !== $this->post_type ) {
				return $disabled_lock_access;
			}
			// user is in profile page then lock should be set.
			if ( $bm_employee_id === $post_id ) {
				return $disabled_lock_access;
			}

			// otherwise if user is in employee page and not in profile page then lock must not be set.
			$disabled_lock_access[] = 'edit_profile';

			return $disabled_lock_access;
		}

		/**
		 * Initialize custom fields.
		 */
		public function init_custom_fields() {
			require_once BUSINESSMANAGER_DIR . 'includes/class-fields.php';
			Business_Manager_Notes_Field::init_notes();
		}

		/**
		 * Initialize side metabox for upcoming leaves.
		 */
		public function metabox_leave() {
			$id = $this->pre . 'leave_box';

			$box = new_cmb2_box(
                [
					'id'           => $id,
					'title'        => '<span class="dashicons dashicons-calendar-alt"></span> ' . __( 'Upcoming Leave', 'business-manager' ),
					'object_types' => [ $this->post_type ], // Post type.
					'context'      => 'side',
					'classes'      => 'bm',
					'priority'     => 'default',
					'show_in_rest' => true,
				]
            );

			$fields = [];

			$fields[] = [
				'name'  => __( '', 'business-manager' ),
				'id'    => $this->pre . 'leave_html',
				'type'  => 'title',
				'after' => 'business_manager_upcoming_leave_html_metabox',
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			// sort numerically.
			ksort( $fields );

			// loop through ordered fields and add them to the metabox.
			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					$fields[ $key ] = $box->add_field( $value );
				}
			}
		}

		/**
		 * Initialize side metabox to handle access limits for employee.
		 */
		public function metabox_access() {
			$id = $this->pre . 'access_box';

			$box = new_cmb2_box(
				[
					'id'           => $id,
					'title'        => '<span class="dashicons dashicons-admin-network"></span> ' . __( 'Access', 'business-manager' ),
					'object_types' => [ $this->post_type ],
					'context'      => 'side',
					'classes'      => 'bm',
					'priority'     => 'core',
					'show_in_rest' => true,
				]
			);

			$fields = [];

			$fields[] = [
				'name'       => __( 'Login Account', 'business-manager' ),
				'id'         => $this->pre . 'user_id',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_employee_user_accounts',
				'desc'       => '<a target="_blank" href="https://bzmngr.com/docs/employee-login-accounts-and-access/">Please visit our documentation for more informaton on assigning Login Accounts and setting up access for this employee</a>.',
			];

			$access_options = [
				'employees' => [
					'name'    => business_manager_label_employee_plural(),
					'default' => 'none',
				],
				'leave'     => [
					'name'    => business_manager_label_leave_plural(),
					'default' => 'limited',
				],
				'reviews'   => [
					'name'    => business_manager_label_review_plural(),
					'default' => 'limited',
				],
				'projects'  => [
					'name'    => business_manager_label_project_plural(),
					'default' => 'limited',
				],
				'clients'   => [
					'name'    => business_manager_label_client_plural(),
					'default' => 'limited',
				],
				'documents' => [
					'name'    => business_manager_label_document_plural(),
					'default' => 'limited',
				],
			];

			$extensions = get_option( 'business-manager-extensions' );
			if ( ! empty( $extensions ) ) {
				foreach ( $extensions as $extension => $license_key ) {
					$extension_details = get_option( 'business-manager-' . $extension );

					if ( ! empty( $extension_details ) && $extension_details['access'] ) {
						$access_options[ $extension ] = [
							'name'    => $extension_details['name'],
							'default' => 'limited',
						];
					}
				}
			}

			foreach ( $access_options as $type => $options ) {
				$access_types = [
					'full'    => __( 'Full', 'business-manager' ),
					'limited' => __( 'Limited', 'business-manager' ),
					'none'    => __( 'None', 'business-manager' ),
				];

				if ( 'employees' === $type ) {
					$access_types['full'] = __( 'Edit &amp; Read All Profiles', 'business-manager' );

					$access_types['limited'] = __( 'Read All Profiles', 'business-manager' );

					$access_types['none'] = __( 'Read Own Profile', 'business-manager' );

					$access_types = array_merge(
                        $access_types,
                        [
							'edit_profile' => __( 'Edit Own Profile', 'business-manager' ),
						]
                    );
				}

				$fields[] = [
					'name'    => $options['name'],
					'id'      => 'bm_access_' . $type,
					'type'    => 'radio_inline',
					'options' => $access_types,
					'default' => $options['default'],
				];
			}

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			// sort numerically.
			ksort( $fields );

			// loop through ordered fields and add them to the metabox.
			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					$fields[ $key ] = $box->add_field( $value );
				}
			}
		}

		/**
		 * Initialize metabox to handle employee details having cmb fields.
		 */
		public function metabox_details() {
			$id = $this->pre . 'details_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-businessman"></span> ' . __( 'Employee Details', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
				'show_in_rest' => true,
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields = [];

			$fields['personal'][] = [
				'name'       => __( 'First Name', 'business-manager' ),
				'id'         => $this->pre . 'first_name',
				'type'       => 'text',
				'attributes' => [
					'required'     => 'required',
					'autocomplete' => 'off',
					'classes'      => 'bm-field-full ',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Last Name', 'business-manager' ),
				'id'         => $this->pre . 'last_name',
				'type'       => 'text',
				'attributes' => [
					'required'     => 'required',
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'    => __( 'Gender', 'business-manager' ),
				'id'      => $this->pre . 'gender',
				'type'    => 'select',
				'options' => [
					''       => '',
					'male'   => 'Male',
					'female' => 'Female',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Marital Status', 'business-manager' ),
				'id'         => $this->pre . 'marital_status',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_marital_status',
			];

			$fields['personal'][] = [
				'name'        => __( 'Date of Birth', 'business-manager' ),
				'id'          => $this->pre . 'dob',
				'type'        => 'text_date_timestamp',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Address', 'business-manager' ),
				'id'         => $this->pre . 'address',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Address Line 2', 'business-manager' ),
				'id'         => $this->pre . 'address2',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'City', 'business-manager' ),
				'id'         => $this->pre . 'city',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'State/Province', 'business-manager' ),
				'id'         => $this->pre . 'state_province',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Zipcode', 'business-manager' ),
				'id'         => $this->pre . 'zipcode',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Country', 'business-manager' ),
				'id'         => $this->pre . 'country',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Tax ID', 'business-manager' ),
				'id'         => $this->pre . 'tax_id',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Personal Phone', 'business-manager' ),
				'id'         => $this->pre . 'phone',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Personal Email', 'business-manager' ),
				'id'         => $this->pre . 'email',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Emergency Contact Name', 'business-manager' ),
				'id'         => $this->pre . 'emergency_name',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'       => __( 'Emergency Contact Phone', 'business-manager' ),
				'id'         => $this->pre . 'emergency_phone',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['personal'][] = [
				'name'         => __( 'Photo', 'business-manager' ),
				'id'           => $this->pre . 'photo',
				'type'         => 'file',
				'classes'      => 'bm-field-clear',
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => 'Add or Upload Photo',
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

			$fields['employment'][] = [
				'name'       => __( 'Job Title', 'business-manager' ),
				'id'         => $this->pre . 'title',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['employment'][] = [
				'name'       => __( 'Status', 'business-manager' ),
				'id'         => $this->pre . 'status',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_employment_status',
			];

			$fields['employment'][] = [
				'name'       => __( 'Type', 'business-manager' ),
				'id'         => $this->pre . 'type',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_employment_type',
			];

			$fields['employment'][] = [
				'name'        => __( 'Start Date', 'business-manager' ),
				'id'          => $this->pre . 'start_date',
				'type'        => 'text_date_timestamp',
				'classes'     => 'bm-field-half-1',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
			];

			$fields['employment'][] = [
				'name'        => __( 'Finish Date', 'business-manager' ),
				'id'          => $this->pre . 'finish_date',
				'type'        => 'text_date_timestamp',
				'classes'     => 'bm-field-half-2',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
			];

			$fields['employment'][] = [
				'name'       => __( 'Work Phone', 'business-manager' ),
				'id'         => $this->pre . 'work_phone',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['employment'][] = [
				'name'       => __( 'Work Email', 'business-manager' ),
				'id'         => $this->pre . 'work_email',
				'type'       => 'text',
				'attributes' => [
					'autocomplete' => 'off',
				],
			];

			$fields['employment'][] = [
				'name'       => __( 'Manager', 'business-manager' ),
				'id'         => $this->pre . 'manager',
				'type'       => 'pw_multiselect',
				'options_cb' => 'business_manager_dropdown_get_employee',
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			$tabs_setting['tabs'][] = [
				'id'     => 'personal',
				'title'  => __( 'Personal', 'business-manager' ),
				'fields' => $fields['personal'],
			];

			$tabs_setting['tabs'][] = [
				'id'     => 'employment',
				'title'  => __( 'Employment', 'business-manager' ),
				'fields' => $fields['employment'],
			];

			$tabs_setting = apply_filters( "business_manager_metabox_tabs_{$id}", $tabs_setting, $id, $fields );

			// loop through tab fields, hide them & save them.
			// this is a hack for the tabs.
			if ( $fields['personal'] ) {
				foreach ( $fields['personal'] as $key => $value ) {
					$value['type']                   = 'hidden';
					$value['save_field']             = false;
					$value['attributes']['readonly'] = 'readonly';
					$value['attributes']['disabled'] = 'disabled';
					$fields[ $key ]                  = $box->add_field( $value );
				}
			}

			if ( $fields['employment'] ) {
				foreach ( $fields['employment'] as $key => $value ) {
					$value['type']                   = 'hidden';
					$value['save_field']             = false;
					$value['attributes']['readonly'] = 'readonly';
					$value['attributes']['disabled'] = 'disabled';
					$fields[ $key ]                  = $box->add_field( $value );
				}
			}

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
		 * Initialize metabox to display files and documents.
		 */
		public function metabox_files() {
			$id = $this->pre . 'files_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-portfolio"></span> ' . __( 'Files & Documents', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
				'context'      => 'normal',
				'classes'      => 'bm',
				'priority'     => 'high',
				'show_in_rest' => true,
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

			$fields['application'][]  = [
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'application_files',
				'type'    => 'file_list',
				'desc'    => __( 'Job application documents & resume', 'business-manager' ),
				'options' => [
					'url' => false,
				],
			];
			$fields['contracts'][]    = [
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'contract_files',
				'desc'    => __( 'Contract documents & files', 'business-manager' ),
				'type'    => 'file_list',
				'options' => [
					'url' => false,
				],
			];
			$fields['training'][]     = [
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'training_files',
				'desc'    => __( 'Training documents & certificates', 'business-manager' ),
				'type'    => 'file_list',
				'options' => [
					'url' => false,
				],
			];
			$fields['disciplinary'][] = [
				'name'    => __( 'Files', 'business-manager' ),
				'id'      => $this->pre . 'disciplinary_files',
				'desc'    => __( 'Warnings & other disciplinary documents', 'business-manager' ),
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
			$tabs_setting['tabs'][] = [
				'id'     => 'application',
				'title'  => __( 'Application', 'business-manager' ),
				'fields' => $fields['application'],
			];
			$tabs_setting['tabs'][] = [
				'id'     => 'training',
				'title'  => __( 'Training', 'business-manager' ),
				'fields' => $fields['training'],
			];
			$tabs_setting['tabs'][] = [
				'id'     => 'disciplinary',
				'title'  => __( 'Disciplinary', 'business-manager' ),
				'fields' => $fields['disciplinary'],
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
		 * Initialize metabox to display notes.
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
				'show_in_rest' => true,
			];

			// Setup meta box.
			$box = new_cmb2_box( $box_options );

			// setting tabs.
			$tabs_setting = [
				'config' => $box_options,
				'tabs'   => [],
			];

			$fields['general'][] = [
				'name'        => __( 'Employee Notes', 'business-manager' ),
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
		 * Initialize side metabox for asset manager add on.
		 */
		public function metabox_assets() {
			$assets_is_active = business_manager_addon_is_active( 'business-manager-asset-manager' );

			$id  = $this->pre;
			$id .= $assets_is_active ? 'assets_box' : 'assets_box_inactive';

			$box = new_cmb2_box(
                [
					'id'           => $id,
					'title'        => '<span class="dashicons dashicons-laptop"></span> ' . __( 'Assets', 'business-manager' ),
					'object_types' => [ $this->post_type ], // Post type.
					'context'      => 'side',
					'classes'      => 'bm',
					'priority'     => 'default',
					'show_in_rest' => true,
				]
            );

			$fields = [];

			$fields[] = [
				'name'  => '',
				'id'    => $this->pre . 'leave_html',
				'type'  => 'title',
				'after' => $assets_is_active ? 'business_manager_assets_html_metabox' : '',
			];

			// filter the fields.
			$fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );

			// sort numerically.
			ksort( $fields );

			// loop through ordered fields and add them to the metabox.
			if ( $fields ) {
				foreach ( $fields as $key => $value ) {
					$fields[ $key ] = $box->add_field( $value );
				}
			}
		}
	}

endif;

return new Business_Manager_Employee_Metaboxes();
