<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Leave_Metaboxes' ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Leave_Metaboxes {

        /**
         * The post type for leaves.
         *
         * @var string
         */
        public $post_type = 'bm-leave';

        /**
         * The date format for leaves.
         *
         * @var string
         */
        public $date_format = '';

        /**
         * The access type for leaves.
         *
         * @var string
         */
        public $access_type = 'bm_access_leave';

        /**
         * The ID of the business manager employee associated with the leave.
         *
         * @var int|null
         */
        public $bm_employee_id;

        /**
         * The access information for leaves.
         *
         * @var mixed
         */
        public $bm_access;

        /**
         * Prefix for meta keys related to leaves.
         *
         * @var string
         */
        private $pre = '_bm_leave_';

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
			add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );

			add_action( 'cmb2_init', [ $this, 'metabox_details' ] );
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
         * Register CMB fields and create the leave details meta box.
         */
		public function metabox_details() {
			$id = $this->pre . 'details_box';

			$box_options = [
				'id'           => $id,
				'title'        => '<span class="dashicons dashicons-calendar-alt"></span> ' . __( 'Leave Details', 'business-manager' ),
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
				'name'       => __( 'Employee', 'business-manager' ),
				'id'         => $this->pre . 'employee',
				'type'       => ( 'limited' === $this->bm_access ? 'hidden' : 'select' ),
				'default'    => $this->bm_employee_id,
				'options_cb' => 'business_manager_dropdown_get_employee',
				'attributes' => [
					'required' => 'required',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Type', 'business-manager' ),
				'id'         => $this->pre . 'type',
				'type'       => 'select',
				'options_cb' => 'business_manager_dropdown_leave_type',
				'classes'    => ( 'limited' === $this->bm_access ? 'bm-field-clear' : '' ),
				'attributes' => [
					'required' => 'required',
				],
			];

			$fields['general'][] = [
				'name'        => __( 'First Day of Leave', 'business-manager' ),
				'id'          => $this->pre . 'start',
				'type'        => 'text_date_timestamp',
				'date_format' => $this->date_format,
				'classes'     => 'bm-field-quarter-1',
				'attributes'  => [
					'required'     => 'required',
					'autocomplete' => 'off',
				],
			];

			$fields['general'][] = [
				'name'        => __( 'Last Day of Leave', 'business-manager' ),
				'id'          => $this->pre . 'end',
				'type'        => 'text_date_timestamp',
				'date_format' => $this->date_format,
				'classes'     => 'bm-field-quarter-2',
				'attributes'  => [
					'required'                   => 'required',
					'autocomplete'               => 'off',
					'data-parsley-gte'           => '#_bm_leave_start',
					'data-parsley-error-message' => __( 'Last day of leave must be greater than or equal to the first day of leave.', 'business-manager' ),
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Total Days', 'business-manager' ),
				'id'         => $this->pre . 'total_days',
				'type'       => 'text',
				'classes'    => 'bm-field-quarter-3',
				'attributes' => [
					'required' => 'required',
				],
			];

			$fields['general'][] = [
				'name'       => __( 'Status', 'business-manager' ),
				'id'         => $this->pre . 'status',
				'type'       => ( 'limited' === $this->bm_access ? 'hidden' : 'select' ),
				'default'    => 'Requested',
				'attributes' => [
					'required' => 'required',
				],
				'options_cb' => 'business_manager_dropdown_leave_status',
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

return new Business_Manager_Leave_Metaboxes();
