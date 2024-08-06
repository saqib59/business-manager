<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Leave' ) ) :

	/**
	 * Business_Manager_Leave Class.
	 */
	class Business_Manager_Leave {

		/**
         * Post type.
         *
         * @var string
         */
		public $post_type = 'bm-leave';

		/**
		 * Post ID.
		 *
		 * @var int|null
		 */
		public $post_id = null;

		/**
		 * Employee ID.
		 *
		 * @var int|null
		 */
		public $employee_id = null;

		/**
		 * Custom metabox.
		 *
		 * @var null
		 */
		public $cmb = null;

		/**
		 * Access type.
		 *
		 * @var string
		 */
		public $access_type = 'bm_access_leave';

		/**
		 * Business manager employee ID.
		 *
		 * @var mixed
		 */
		public $bm_employee_id;

		/**
		 * Business manager access.
		 *
		 * @var mixed
		 */
		public $bm_access;

		/**
		 * Constructor.
		 */
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

			// Interface Changes.
			add_filter( 'gettext', [ $this, 'publish_metabox_rename' ], 10, 2 );
			add_filter( 'post_updated_messages', [ $this, 'post_updated_message' ] );
			add_action( 'admin_menu', [ $this, 'login_access_admin_menu' ] );
			add_filter( 'views_edit-' . $this->post_type, [ $this, 'login_access_views_edit' ], 1 );
			add_filter( 'post_row_actions', [ $this, 'login_access_post_row_actions' ], 10, 1 );
			add_filter( 'bulk_actions-edit-' . $this->post_type, [ $this, 'login_access_bulk_actions' ] );

			add_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );
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
		 * Modify the translation for specific text strings in the publishing metabox.
		 *
		 * @param string $translation The translated text.
		 * @param string $text        The original text before translation.
		 * @return string The modified or translated text.
		 *
		 * @since 1.4.4
		 */
		public function publish_metabox_rename( $translation, $text ) {
			global $post_type;

			if ( $post_type === $this->post_type && ( 'Publish' === $text || 'Update' === $text ) ) {
				return __( 'Save Leave', 'business-manager' );
			}

			return $translation;
		}

		/**
		 * Modify the post updated messages for the specified post type.
		 *
		 * @param array $messages The array of post updated messages.
		 * @return array The modified array of post updated messages.
		 *
		 * @since 1.4.4
		 */
		public function post_updated_message( $messages ) {
			global $post_type;

			if ( $post_type == $this->post_type ) {
				$messages['post'] = array_map(
					function ( $string ) {
						return str_replace( 'Post', __( 'Leave', 'business-manager' ), str_replace( 'updated', __( 'saved', 'business-manager' ), str_replace( 'published', __( 'saved', 'business-manager' ), $string ) ) );
					},
					$messages['post']
				);
			}

			return $messages;
		}

		/**
		 *
		 * Change interface based on Employee Login Access.
		 *
		 * @since 1.4.0
		 */
		public function login_access_admin_menu() {
			global $pagenow;

			$this->post_id = ( isset( $_GET['post'] ) ? sanitize_text_field( $_GET['post'] ) : null );
			$post_type     = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : get_post_type( $this->post_id ) );

			if ( current_user_can( 'manage_options' ) || $post_type != $this->post_type ) {
				return;
			}

			// Hide Publish Metabox.
			if ( 'limited' === $this->bm_access && 'post.php' === $pagenow && get_post_meta( $this->post_id, '_bm_leave_employee', true ) == $this->bm_employee_id ) {
				remove_meta_box( 'submitdiv', $this->post_type, 'side' );
				$object                    = get_post_type_object( $this->post_type );
				$object->labels->edit_item = sprintf( 'View %s', business_manager_label_leave_single() );
			}
		}

		/**
         * Filter the views available to manage login access items.
         *
         * @param array $views An array of views available to manage login access items.
         * @return array Modified array of views available to manage login access items.
         *
         * @since 1.4.0
         */
		public function login_access_views_edit( $views ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || 'full' === $this->bm_access || $post_type !== $this->post_type ) {
				return $views;
			}

			$remove_views = [ 'all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash', 'mine' ];

			foreach ( (array) $remove_views as $view ) {
				if ( isset( $views[ $view ] ) ) {
					unset( $views[ $view ] );
				}
			}

			return $views;
		}

		/**
		 * Filter the row actions available for each login access item in the list table.
		 *
		 * @param array $actions An array of row actions available for each login access item.
		 * @return array Modified array of row actions available for each login access item.
		 *
         * @since 1.4.0
         */
		public function login_access_post_row_actions( $actions ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || 'full' === $this->bm_access || $post_type !== $this->post_type ) {
				return $actions;
			}

			if ( 'limited' === $this->bm_access ) {
				$actions['edit'] = str_replace( __( 'Edit', 'business-manager' ), __( 'View', 'business-manager' ), $actions['edit'] );
				unset( $actions['trash'], $actions['inline hide-if-no-js'] );
			}

			return $actions;
		}

		/**
		 * Filter the bulk actions available for the login access items in the list table.
		 *
		 * @param array $actions An array of bulk actions available for the login access items.
		 * @return array Modified array of bulk actions available for the login access items.
		 *
         * @since 1.4.0
         */
		public function login_access_bulk_actions( $actions ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || 'full' === $this->bm_access || $post_type !== $this->post_type ) {
				return $actions;
			}
		}


		/**
		 * Handle the save/update action for leave to send email notification for leave to the manger of employee.
		 *
		 * @param int    $object_id The ID of the object being saved/updated.
		 * @param string $cmb_id The ID of the metabox.
		 * @param bool   $updated Whether the object was updated successfully.
		 * @param object $cmb The metabox object.
		 */
		public function on_save_update( $object_id, $cmb_id, $updated, $cmb ) {
			require_once BUSINESSMANAGER_DIR . 'includes/email-sender.php';

			if ( get_post_type( $object_id ) !== $this->post_type ) {
				return;
			}

			$this->employee_id = (int) $cmb->data_to_save['_bm_leave_employee'];
			$this->post_id     = $object_id;
			$this->cmb         = $cmb;

			if ( is_null( $this->employee_id ) ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( wp_is_post_revision( $this->post_id ) ) {
				return;
			}

			$employee_name        = business_manager_employee_full_name( $this->employee_id );
			$employee_manager_ids = get_post_meta( $this->employee_id, '_bm_employee_manager', true );
			$leave_email_sent     = get_post_meta( $object_id, '_bm_leave_email_sent', true );
			$leave_type           = get_post_meta( $object_id, '_bm_leave_type', true );
			$leave_start_date     = date( 'm-d-Y', get_post_meta( $object_id, '_bm_leave_start', true ) );
			$leave_end_date       = date( 'm-d-Y', get_post_meta( $object_id, '_bm_leave_end', true ) );

			if ( empty( $leave_email_sent ) && ! empty( $employee_manager_ids ) ) {
				$edit_post_link = get_edit_post_link( $object_id );
				$subject        = __( $leave_type . ' Leave Request By ' . $employee_name, 'business-manager' );
				$message        = __( $employee_name . ' have applied for leave from the date ' . $leave_start_date . ' to ' . $leave_end_date . '.', 'business-manager' );

				foreach ( $employee_manager_ids as $employee_manager_id ) {
					$manager_email   = business_manager_employee_work_email( $employee_manager_id );
					$manager_user_id = get_post_meta( $employee_manager_id, '_bm_employee_user_id', true );
					if ( $manager_user_id ) {
						$message .= '<br /><br />';
						$message .= __( "You can approve or decline the leave request by clicking <a href='" . $edit_post_link . "'>here</a>.", 'business-manager' );
					}

					if ( $manager_email ) {
						$email_sender = new Business_Manager_Email_Sender( $manager_email, $subject, $message );
						$email_sent   = $email_sender->send_email();
						update_post_meta( $object_id, '_bm_leave_email_sent', $email_sent );
					}
				}
			}

			remove_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );
			$this->update_title();
			add_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );
		}

		/**
		 * Update title.
		 *
		 * @since 1.0.0
		 */
		public function update_title() {
			$post = [
				'ID'         => $this->post_id,
				'post_title' => business_manager_employee_full_name( $this->employee_id ),
			];

			wp_update_post( $post );
		}
	}

endif;

return new Business_Manager_Leave();
