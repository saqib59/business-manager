<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class.
 *
 * @since 1.0.0
 */
class Business_Manager_Disable_Post_Lock {


	public $bm_employee_id;
	/***
	 * @var
	 * stores access for curernt post type
	 */
	public $bm_access;
	/***
	 * @var
	 * store access type for current post type
	 */
	public $access_type;
	/**
	 * @var
	 */
	public $post_id;
	/***
	 * @var
	 * Stores post wise access meta key
	 */
	public $access_types;
	/***
	 * @var
	 * stores all the access types for which the post lock should be disabled
	 */
	public $disabled_lock_access;

	public $post_type;

	/**
	 * Main constructor.
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {
		// Hook into actions & filters
		$this->init();

		$this->hooks();
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->access_types = [
			'bm-employee' => 'bm_access_employees',
			'bm-project'  => 'bm_access_projects',
			'bm-leave'    => 'bm_access_leave',
			'bm-review'   => 'bm_access_reviews',
			'bm-client'   => 'bm_access_clients',
			'bm-document' => 'bm_access_documents'
		];

	}

	/**
	 * Hook in to actions & filters.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );
		add_action( 'admin_init', [ $this, 'may_be_disable_heartbeat' ], 1 );
		add_filter( 'wp_check_post_lock_window', [ $this, 'may_be_disable_post_lock' ] );
		add_filter( 'update_post_metadata', [ $this, 'may_be_disable_update_meta_edit_lock' ], 10, 5 );
	}


	public function may_be_disable_update_meta_edit_lock( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( $meta_key !== '_edit_lock' ) {
			return $check;
		}
		if ( $this->may_be_disable() ) {
			return true; //any non null value will result in
		}

		return $check;
	}

	public function may_be_disable() {
		//variables
		global $pagenow;
		$disable = true;

		//validations
		if ( $pagenow !== 'post.php' ) {
			$disable = false;
		}

		//return of required vars are not defined
		if ( ! $this->access_type || ! $this->disabled_lock_access ) {
			$disable = false;
		}
		//return if access type of current post is not in array
		if ( ! in_array( $this->bm_access, $this->disabled_lock_access ) ) {
			$disable = false;
		}

		return apply_filters( 'business_manager_may_be_disable_edit_lock', $disable, $this->post_type, $this->bm_access, $this->access_type, $this->bm_employee_id, $this->post_id );
	}

	/**
	 *
	 * Set Business Manager Variables that rely on pluggable functions.
	 *
	 * @since 1.4.1
	 */
	public function bm_pluggable() {

		//variables definition
		$this->bm_employee_id = business_manager_employee_id( get_current_user_id() );
		$this->post_id        = ( isset( $_GET['post'] ) ? sanitize_text_field( $_GET['post'] ) : null );
		$this->post_type      = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : get_post_type( $this->post_id ) );

		/***
		 * @hook business_manager_disable_post_lock_access_types
		 * Must return array with key being post name and type of access against that post
		 */
		$this->access_types = apply_filters( 'business_manager_disable_post_lock_access_types',
			$this->access_types,
			$this->post_type,
			$this->post_id,
			$this->bm_employee_id );

		/***
		 * hook business_manager_disable_post_lock_disabled_lock_for_access
		 * must return array of all access for which post lock must be disabled
		 */
		$this->disabled_lock_access = apply_filters( 'business_manager_disable_post_lock_disabled_lock_for_access',
			[ 'limited', 'none' ],
			$this->post_type,
			$this->post_id,
			$this->bm_employee_id );


		$this->access_type = isset( $this->access_types[ $this->post_type ] ) ? $this->access_types[ $this->post_type ] : false;

		//validations
		if ( current_user_can( 'manage_options' ) ) {
			return;
		} //return of administrators
		if ( ! $this->access_type ) {
			return;
		} //return if access type of current post is not in array

		$this->bm_access = business_manager_employee_access( get_current_user_id(), $this->access_type );
	}

	public function may_be_disable_post_lock( $time_window ) {
		if ( $this->may_be_disable() ) {
			return false;
		}

		return $time_window;
	}

	public function may_be_disable_heartbeat() {
		if ( $this->may_be_disable() ) {
			wp_deregister_script( 'heartbeat' );
		}
	}

}

return new Business_Manager_Disable_Post_Lock();
