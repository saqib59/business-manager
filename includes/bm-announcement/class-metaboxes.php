<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Announcement_Metaboxes' ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Announcement_Metaboxes {

        /**
         * The post type for Announcement.
         *
         * @var string
         */
        public $post_type = 'bm-announcement';

        /**
         * Prefix for meta keys related to announcements.
         *
         * @var string
         */
        private $pre = '_bm_announcement_';

        /**
         * Class constructor.
         *
         * Initializes the object and sets up necessary actions and filters.
         */
        public function __construct() {
			$this->hooks();
		}

		/**
		 * Hook in to actions & filters.
		 */
		public function hooks() {
			add_action( 'cmb2_init', [ $this, 'metabox_fields' ] );
		}

        /**
         * Register CMB fields and create the leave details meta box.
         */
		public function metabox_fields() {
			$id = $this->pre . 'details_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-calendar-alt"></span> ' . __( 'Notify Employees', 'business-manager' ),
				'object_types' => [ $this->post_type ],
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
				'name'       => __( 'Send Notification To', 'business-manager' ),
				'id'         => $this->pre . 'employee_departments',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_get_departments',
				'attributes' => [
					'required' => 'required',
				],
			];

			$fields['general'][] = [
				'name' => __( 'Send Email', 'business-manager' ),
				'desc' => __( 'Checking this will send an email notification to the employees of selected department.', 'business-manager' ),
				'id'   => $this->pre . 'send_email',
				'type' => 'checkbox',
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

return new Business_Manager_Announcement_Metaboxes();
