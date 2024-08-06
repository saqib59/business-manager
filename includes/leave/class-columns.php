<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Business_Manager_Leave_Columns', false ) ) :

	/**
	 * Business_Manager_Leave_Columns Class.
	 */
	class Business_Manager_Leave_Columns {
        /**
         * The post type for Business Manager Leave.
         *
         * @var string
         */
        public $post_type = 'bm-leave';

        /**
         * The columns associated with Business Manager Leave.
         *
         * @var array|null
         */
        public $cols = null;

        /**
         * The access type for Business Manager Leave.
         *
         * @var string
         */
        public $access_type = 'bm_access_leave';

        /**
         * The employee ID associated with Business Manager Leave.
         *
         * @var int|null
         */
        public $bm_employee_id;

        /**
         * The access information for Business Manager Leave.
         *
         * @var string|null
         */
        public $bm_access;

        /**
         * Constructor for the Business Manager Leave Columns class.
         */
		public function __construct() {
			$this->init();
			$this->columns();
			$this->filters();
		}

		/**
		 * Init.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			$this->cols = new BM_columns( $this->post_type, false );

			add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );
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

			if ( 'limited' === $this->bm_access ) {
				$this->cols->remove_column( 'cb' );
			}
		}

		/**
		 * Define columns.
		 *
		 * @since 1.0.0
		 */
		public function columns() {
			$this->cols->remove_column( 'title' );
			$this->cols->remove_column( 'date' );
			$this->cols->remove_column( 'author' );

			$columns = [
				'_bm_leave_employee'   => [
					'label'       => __( 'Employee', 'business-manager' ),
					'type'        => 'post_meta',
					'format'      => 'person_meta',
					'meta_fields' => [
						'first_name' => '_bm_employee_first_name',
						'last_name'  => '_bm_employee_last_name',
						'photo_id'   => '_bm_employee_photo_id',
					],
					'meta_key'    => '_bm_leave_employee',
					'orderby'     => 'meta_value',
					'sortable'    => true,
					'title_link'  => true,
				],

				'_bm_leave_type'       => [
					'label'    => __( 'Type', 'business-manager' ),
					'type'     => 'post_meta',
					'meta_key' => '_bm_leave_type',
					'orderby'  => 'meta_value',
					'sortable' => true,
				],

				'_bm_leave_status'     => [
					'label'    => __( 'Status', 'business-manager' ),
					'type'     => 'post_meta',
					'meta_key' => '_bm_leave_status',
					'orderby'  => 'meta_value',
					'sortable' => true,
					'def'      => '',
					'format'   => 'status',
				],

				'_bm_leave_start'      => [
					'label'    => __( 'Start', 'business-manager' ),
					'type'     => 'post_meta',
					'meta_key' => '_bm_leave_start',
					'orderby'  => 'meta_value',
					'sortable' => true,
					'format'   => 'date',
				],

				'_bm_leave_end'        => [
					'label'    => __( 'End', 'business-manager' ),
					'type'     => 'post_meta',
					'meta_key' => '_bm_leave_end',
					'orderby'  => 'meta_value',
					'sortable' => true,
					'format'   => 'date',
				],

				'_bm_leave_total_days' => [
					'label'    => __( 'Total Days', 'business-manager' ),
					'type'     => 'post_meta',
					'meta_key' => '_bm_leave_total_days',
					'sortable' => false,
				],
			];

			$columns = apply_filters( 'business_manager_columns_leave', $columns );

			foreach ( $columns as $key => $col ) {
				$this->cols->add_column( $key, $col );
			}
		}

        /**
         * Sets up filters for the Business Manager Leave Columns.
         */
		public function filters() {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( 'edit.php' === $pagenow && $post_type === $this->post_type ) {
				$this->cols->remove_filter( 'date' );

				$filters = [
					'_bm_leave_employee' => [
						'label'    => __( 'Employees', 'business-manager' ),
						'type'     => 'post_meta',
						'meta_key' => '_bm_leave_employee',
						'format'   => 'employees',
						'access'   => [
							'type'  => 'bm_access_leave',
							'field' => '_bm_leave_employee',
						],
					],

					'_bm_leave_type'     => [
						'label'    => __( 'Types', 'business-manager' ),
						'type'     => 'post_meta',
						'meta_key' => '_bm_leave_type',
						'access'   => [
							'type'  => 'bm_access_leave',
							'field' => '_bm_leave_employee',
						],
					],

					'_bm_leave_status'   => [
						'label'    => __( 'Statuses', 'business-manager' ),
						'type'     => 'post_meta',
						'meta_key' => '_bm_leave_status',
						'access'   => [
							'type'  => 'bm_access_leave',
							'field' => '_bm_leave_employee',
						],
					],

					'_bm_leave_start'    => [
						'label'    => __( 'Start Dates', 'business-manager' ),
						'type'     => 'post_meta',
						'meta_key' => '_bm_leave_start',
						'format'   => 'dates',
						'access'   => [
							'type'  => 'bm_access_leave',
							'field' => '_bm_leave_employee',
						],
					],

					'_bm_leave_end'      => [
						'label'    => __( 'End Dates', 'business-manager' ),
						'type'     => 'post_meta',
						'meta_key' => '_bm_leave_end',
						'format'   => 'dates',
						'access'   => [
							'type'  => 'bm_access_leave',
							'field' => '_bm_leave_employee',
						],
					],
				];

				foreach ( $filters as $key => $filter ) {
					$this->cols->add_filter( $key, $filter );
				}
			}
		}
	}

endif;

return new Business_Manager_Leave_Columns();
