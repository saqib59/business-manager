<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Document', false ) ) :

	/**
	 * Business_Manager_Document Class.
	 */
	class Business_Manager_Document {

		public $post_type = 'bm-document';
		public $post_id   = null;

		public $access_type = 'bm_access_documents';
		public $bm_employee_id;
		public $bm_access;

		public function __construct() {
			$this->init();
			$this->hooks();
		}

		/**
		 * Init.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			global $post;

			// if we are in the post edit screen
			if ( isset( $post->ID ) && $post->post_type == $this->post_type ) {
				$this->post_id = (int) $post->ID;
			}
		}

		/**
		 * Hook in to actions & filters.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'plugins_loaded', array( $this, 'bm_pluggable' ) );

			// Interface Changes
			add_filter( 'gettext', array( $this, 'publish_metabox_rename' ), 10, 2 );
			add_filter( 'post_updated_messages', array( $this, 'post_updated_message' ) );
			add_action( 'admin_menu', array( $this, 'login_access_admin_menu' ) );
			add_filter( 'views_edit-' . $this->post_type, array( $this, 'login_access_views_edit' ), 1 );
			add_filter( 'post_row_actions', array( $this, 'login_access_post_row_actions' ), 10, 2 );
			add_filter( 'bulk_actions-edit-' . $this->post_type, array( $this, 'login_access_bulk_actions' ) );

			add_action( 'cmb2_save_post_fields', array( $this, 'on_save_update' ), 10, 4 );
			add_filter( 'enter_title_here', array( $this, 'change_title_text' ) );
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
		 *
		 * Rename Publish text.
		 *
		 * @since 1.4.4
		 */
		public function publish_metabox_rename( $translation, $text ) {
			global $post_type;

			if ( $post_type == $this->post_type && ( $text == 'Publish' || $text == 'Update' ) ) {
				return __( 'Save Document', 'business-manager' );
			}

			return $translation;
		}

		/**
		 *
		 * Rename post updated message.
		 *
		 * @since 1.4.4
		 */
		public function post_updated_message( $messages ) {
			global $post_type;

			if ( $post_type == $this->post_type ) {
				$messages['post'] = array_map(
					function ( $string ) {
						return str_replace( 'Post', __( 'Document', 'business-manager' ), str_replace( 'updated', __( 'saved', 'business-manager' ), str_replace( 'published', __( 'saved', 'business-manager' ), $string ) ) );
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

			// Hide Metaboxes
			if ( $this->bm_access == 'limited' && $pagenow == 'post.php' ) {
				remove_meta_box( 'submitdiv', $this->post_type, 'side' );
				$object = get_post_type_object( $this->post_type );
				// set get_post_type_labels()
				$object->labels->edit_item = sprintf( 'View %s', business_manager_label_document_single() );
			}

			// Hide Buttons
			if ( $this->bm_access == 'limited' ) {
				echo '<style type="text/css">';
				echo 'a.page-title-action { display: none; }';
				echo '.cmb-add-row { display: none; }';
				echo '.cmb2-upload-button, .cmb2-remove-file-button { display: none !important; }';
				echo '</style>';
			}
		}

		/**
		 *
		 * Change view options based on Employee Login Access.
		 *
		 * @since 1.4.0
		 */
		public function login_access_views_edit( $views ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || $this->bm_access == 'full' || $post_type != $this->post_type ) {
				return $views;
			}

			$remove_views = array( 'all', 'publish', 'future', 'sticky', 'draft', 'pending', 'trash', 'mine' );

			foreach ( (array) $remove_views as $view ) {
				if ( isset( $views[ $view ] ) ) {
					unset( $views[ $view ] );
				}
			}

			return $views;
		}

		/**
		 *
		 * Change row actions based on Employee Login Access.
		 *
		 * @since 1.4.0
		 */
		public function login_access_post_row_actions( $actions, $post ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || $this->bm_access == 'full' || $post_type != $this->post_type ) {
				return $actions;
			}

			if ( $this->bm_access == 'limited' ) {
				$actions['edit'] = str_replace( __( 'Edit' ), __( 'View' ), $actions['edit'] );
				unset( $actions['trash'], $actions['inline hide-if-no-js'] );
			}

			return $actions;
		}

		/**
		 *
		 * Change bulks actions based on Employee Login Access.
		 *
		 * @since 1.4.0
		 */
		public function login_access_bulk_actions( $actions ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || $this->bm_access == 'full' || $post_type != $this->post_type ) {
				return $actions;
			}
		}

		/**
		 * Change Title field placeholder text.
		 *
		 * @since 1.3.1
		 */
		public function change_title_text( $title ) {
			$screen = get_current_screen();
			if ( $this->post_type == $screen->post_type ) {
				$title = __( 'Enter New Document Name', 'business-manager' );
			}
			return $title;
		}

		/**
		 * Run after saving post.
		 *
		 * @since 1.0.0
		 */
		public function on_save_update( $object_id, $cmb_id, $updated, $cmb ) {
			if ( get_post_type( $object_id ) != $this->post_type ) {
				return;
			}

			$this->post_id = $object_id;
			$this->cmb     = $cmb;

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( wp_is_post_revision( $this->post_id ) ) {
				return;
			}

			remove_action( 'cmb2_save_post_fields', array( $this, 'on_save_update' ), 10, 4 );

			$this->update_latest_version();

			add_action( 'cmb2_save_post_fields', array( $this, 'on_save_update' ), 10, 4 );
		}

		/**
		 * Update latest version of file.
		 *
		 * @since 1.0.0
		 */
		public function update_latest_version() {
			update_post_meta( $this->post_id, '_bm_document_latest', $this->latest_version() );
		}

		/**
		 * Get info on latest version.
		 *
		 * @since 1.0.0
		 */
		public function latest_version( $post_id = null ) {
			if ( ! $post_id ) {
				$post_id = $this->post_id;
			}

			$files = get_post_meta( $post_id, '_bm_document_files', true );

			if ( ! is_array( $files ) ) {
				return null;
			}

			$latest = end( $files );

			return array(
				'doc_id'        => $post_id,
				'employee_id'   => isset( $latest['name'] ) ? $latest['name'] : '',
				'employee_name' => isset( $latest['name'] ) ? business_manager_employee_full_name( $latest['name'] ) : '',
				'date'          => isset( $latest['date'] ) ? $latest['date'] : '',
				'version'       => isset( $latest['version'] ) ? $latest['version'] : '',
				'file'          => isset( $latest['file'] ) ? $latest['file'] : '',
			);
		}

		/**
		 * HTML for the latest document metabox
		 * within document edit screen.
		 *
		 * @since 1.0.0
		 */
		public function latest_document_html_metabox() {
			$latest = get_post_meta( $this->post_id, '_bm_document_latest', true );

			if ( ! empty( $latest ) ) {
				$file  = $latest['file'] ? pathinfo( $latest['file'] ) : '';
				$image = false;
				if ( $latest['file'] && @is_array( getimagesize( $latest['file'] ) ) ) {
					$image = true;
				} ?>

			<div class="latest-document bm-metabox">

				<div class="name">
					<span><?php _e( 'By:', 'business-manager' ); ?></span> 
					<span><?php echo esc_html( $latest['employee_name'] ); ?></span> 
				</div>
				<div class="date">
					<span><?php _e( 'Date:', 'business-manager' ); ?></span> 
					<span><?php echo esc_html( $latest['date'] ); ?></span> 
				</div>
				<div class="version">
					<span><?php _e( 'Version:', 'business-manager' ); ?></span> 
					<span><?php echo esc_html( $latest['version'] ); ?></span> 
				</div>
				<div class="file">
					<span><?php _e( 'File:', 'business-manager' ); ?></span> 
					<strong><?php echo esc_html( $latest['file'] ) ? esc_html( $file['basename'] ) : ''; ?></strong>
				</div>

				<a class="button button-secondary button-small" href="<?php echo esc_attr( $latest['file'] ); ?>" target="_blank" download><?php _e( 'Download', 'business-manager' ); ?></a>

				<?php if ( $image ) { ?>
					<div class="image">
						<span></span> 
						<img src="<?php echo $latest['file'] ? esc_attr( $latest['file'] ) : ''; ?>" />
					</div>
				<?php } ?>

			</div>

				<?php
			} else {
				_e( 'No document', 'business-manager' );
			}
		}
	}

endif;

return new Business_Manager_Document();
