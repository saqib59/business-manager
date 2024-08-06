<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Document_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Document_Metaboxes {
		/**
		 * Post type.
         *
		 * @var string
		 */
		public $post_type = 'bm-document';

        /**
         * Date format used for processing dates.
         *
         * @var string
         */
		public $date_format = '';

		/**
		 * Metabox prefix.
		 *
		 * @var string
		 */
		private $pre = '_bm_document_';

        /**
         * Class constructor.
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
			add_action( 'cmb2_admin_init', [ $this, 'metabox_latest' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_files' ] );
			add_action( 'cmb2_admin_init', [ $this, 'metabox_notes' ] );

			add_action( 'cmb2_admin_init', [ $this, 'init_custom_fields' ] );
		}

        /**
         * Initialize custom fields.
         */
		public function init_custom_fields() {
			require_once BUSINESSMANAGER_DIR . 'includes/class-fields.php';
			Business_Manager_Document_Field::init_documents();
			Business_Manager_Notes_Field::init_notes();
		}

        /**
         * Define and render the latest metabox with cmb fields.
         */
		public function metabox_latest() {
			$id = $this->pre . 'latest_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-paperclip"></span> ' . __( 'Latest Version', 'business-manager' ),
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
				'name'  => __( '', 'business-manager' ),
				'id'    => $this->pre . 'latest_html',
				'type'  => 'title',
				'after' => 'business_manager_latest_document_html_metabox',
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
         * Define and render the motes metabox with cmb fields.
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
				'name'        => __( 'Document Notes', 'business-manager' ),
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
         * Define and render the versions metabox with cmb fields.
         */
		public function metabox_files() {
			$id = $this->pre . 'file_list_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-portfolio"></span> ' . __( 'Versions', 'business-manager' ),
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
				'name'        => __( 'Document Files', 'business-manager' ),
				'id'          => $this->pre . 'files',
				'type'        => 'documents',
				'classes'     => 'bm-repeat',
				'date_format' => $this->date_format,
				'repeatable'  => true,
				'options'     => [
					'url' => false, // Hide the text input for the url.
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

return new Business_Manager_Document_Metaboxes();
