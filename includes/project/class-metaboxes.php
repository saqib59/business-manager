<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Project_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Project_Metaboxes {
		/**
         * The post type associated with the project.
         *
         * @var string
         */
        public $post_type = 'bm-project';

        /**
         * The date format used for the project.
         *
         * @var string
         */
        public $date_format = '';

        /**
         * The access type for projects.
         *
         * @var string
         */
        public $access_type = 'bm_access_projects';

        /**
         * The employee ID associated with the project.
         *
         * @var int|null
         */
        public $bm_employee_id;

        /**
         * The access level for the project.
         *
         * @var string|null
         */
        public $bm_access;

        /**
         * The prefix used for generating keys or identifiers related to the project.
         *
         * @var string
         */
		private $pre = '_bm_project_';

        /**
         * Class constructor for initializing the Project instance.
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
			add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );

			add_action( 'cmb2_admin_init', [ $this, 'metabox_settings' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_timeline' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_tasks' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_files' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_notes' ] );

			add_action( 'cmb2_admin_init', [ $this, 'init_custom_fields' ] );
			add_action( 'cmb2_admin_init', [ $this, 'init_tasks' ] );
		}

		/**
         *
         * Set Business Manager Variables that rely on pluggable functions.
         *
         * @since 1.4.1
         */
		public function bm_pluggable() {
			$this->bm_employee_id = business_manager_employee_id( get_current_user_id() );
			$this->bm_access      = business_manager_employee_access( get_current_user_id(), $this->access_type );
		}

        /**
         * Initialize custom fields for the Project.
         */
		public function init_custom_fields() {
			require_once BUSINESSMANAGER_DIR . 'includes/class-fields.php';
			Business_Manager_Notes_Field::init_notes();
		}

        /**
         * Initialize tasks for the Project.
         */
		public function init_tasks() {
			require_once BUSINESSMANAGER_DIR . 'includes/project/tasks/class-setup.php';
		}

        /**
         * Define and configure metabox settings for the Project.
         */
		public function metabox_settings() {
			$id = $this->pre . 'settings_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-admin-settings"></span> ' . __( 'Settings', 'business-manager' ),
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
				'name'       => __( 'Assigned To', 'business-manager' ),
				'id'         => $this->pre . 'assigned_to',
				'type'       => 'pw_multiselect',
				'default'    => $this->bm_employee_id,
				'options_cb' => 'business_manager_dropdown_get_employee',
				'access'     => [ 'full' ],
			];

			if ( business_manager_is_enabled( 'clients' ) ) {
				$fields['general'][] = [
					'name'       => __( 'Client', 'business-manager' ),
					'id'         => $this->pre . 'client',
					'type'       => 'select',
					'options_cb' => 'business_manager_dropdown_get_client',
					'access'     => [ 'full', 'limited' ],
				];
			}

			$fields['general'][] = [
				'name'       => __( 'Complete', 'business-manager' ),
				'id'         => $this->pre . 'complete',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_percentages',
				'access'     => [ 'full', 'limited' ],
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
         * Define and configure metabox notes for the Project.
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
				'name'        => __( 'Project Notes', 'business-manager' ),
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
         * Define and configure a timeline metabox for the Project.
         */
		public function metabox_timeline() {
			$id = $this->pre . 'timeline_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-clock"></span> ' . __( 'Timeline', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
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
				'name'        => __( 'Start Date', 'business-manager' ),
				'id'          => $this->pre . 'start_date',
				'type'        => 'text_date_timestamp',
				'classes'     => 'bm-field-half-1',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
			];

			$fields['general'][] = [
				'name'        => __( 'End Date', 'business-manager' ),
				'id'          => $this->pre . 'end_date',
				'type'        => 'text_date_timestamp',
				'classes'     => 'bm-field-half-2',
				'date_format' => $this->date_format,
				'attributes'  => [
					'autocomplete' => 'off',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Work Days', 'business-manager' ),
				'id'         => $this->pre . 'work_days',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'type' => 'number',
					'step' => 'any',
					'min'  => '0',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Calendar Days', 'business-manager' ),
				'id'         => $this->pre . 'calendar_days',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'type' => 'number',
					'step' => 'any',
					'min'  => '0',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Estimated Hours', 'business-manager' ),
				'id'         => $this->pre . 'estimated_hours',
				'type'       => 'text',
				'classes'    => 'bm-field-half-1',
				'attributes' => [
					'type' => 'number',
					'step' => 'any',
					'min'  => '0',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Actual Hours', 'business-manager' ),
				'id'         => $this->pre . 'actual_hours',
				'type'       => 'text',
				'classes'    => 'bm-field-half-2',
				'attributes' => [
					'type' => 'number',
					'step' => 'any',
					'min'  => '0',
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
         * Define and configure a timeline metabox for the Project.
         */
		public function metabox_tasks() {
			$id = $this->pre . 'tasks_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-editor-table"></span> ' . __( 'Tasks', 'business-manager' ),
				'object_types' => [ $this->post_type ], // Post type.
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
				'name'  => '',
				'desc'  => '',
				'id'    => $this->pre . 'tasks',
				'type'  => 'title',
				'after' => 'business_manager_project_tasks_html_metabox',
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
         * Define and configure a files metabox for the Project.
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

$business_manager_project_metaboxes = new Business_Manager_Project_Metaboxes();
