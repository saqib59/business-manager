<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Email_Notification_Metaboxes', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Email_Notification_Metaboxes {
		/**
		 * Post type.
		 * @var string
		 */
		public $post_type   = 'bm-email';
        public $access_type = 'bm_access_leave';
        public $bm_employee_id;
        public $bm_access;

		/**
		 * Metabox prefix.
		 *
		 * @since 1.0.0
		 */
		private $pre = '_bm_email_';

		public function __construct() {
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

        public function metabox_details() {
            $id = $this->pre . 'details_box';
    
            $box = new_cmb2_box(
                [
                    'id' => $id,
                    'title' => '<span class="dashicons dashicons-edit"></span> '.__('Plain Text Email Content', 'business-manager'),
                    'object_types' => [ $this->post_type ],
                    'classes' => 'bm',
                    'priority' => 'high',
                    'show_in_rest' => true,
                ],
            );
    
            $fields = [];
    
            $fields[] = [
                'name' => '',
                'desc' => '',
                'id' => $this->pre . 'plain_text',
                'type' => ( $this->bm_access == 'limited' ? 'hidden' : 'textarea' ),
                'default' => '',
                'attributes' => [
                    'required' => 'required',
                ],
                //'after_row' => __( 'Test', 'business-manager' ),
            ];
    
            // filter the fields
            $fields = apply_filters( "business_manager_metabox_{$id}", $fields, $id, $this->post_type );
    
            // sort numerically
            ksort( $fields );
    
            // loop through ordered fields and add them to the metabox
            if ( $fields ) {
                foreach ( $fields as $key => $value ) {
                    $fields[ $key ] = $box->add_field( $value );
                }
            }
        }
    }

endif;

return new Business_Manager_Email_Notification_Metaboxes();
