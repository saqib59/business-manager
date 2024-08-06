<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Client', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Client {

		public $post_type = 'bm-client';
		public $post_id   = null;
		public $cmb       = null;

		public $access_type = 'bm_access_clients';
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
				return __( 'Save Client', 'business-manager' );
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
						return str_replace( 'Post', __( 'Client', 'business-manager' ), str_replace( 'updated', __( 'saved', 'business-manager' ), str_replace( 'published', __( 'saved', 'business-manager' ), $string ) ) );
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
				$object->labels->edit_item = sprintf( 'View %s', business_manager_label_client_single() );
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
		 * @since 1.0.0
		 */
		public function change_title_text( $title ) {
			$screen = get_current_screen();
			if ( $this->post_type == $screen->post_type ) {
				$title = __( 'Enter New Client Name', 'business-manager' );
			}
			return $title;
		}

		/**
		 * HTML for clients on the dashboard.
		 *
		 * @since 1.5.0
		 */
		public function clients_html_dashboard() {
			$query_args = array(
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			);

			$posts = get_posts( $query_args );

			if ( ! empty( $posts ) ) { ?>
		<div class="bm-dashboard-scrollbox inside">
			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width:70%;"><?php _e( 'Client', 'business-manager' ); ?></th>
						<th style="width:30%;"><?php _e( 'Status', 'business-manager' ); ?></th>
					</tr>
				</thead>

				<tbody>
						<?php foreach ( $posts as $key => $post ) : ?>
							<?php $client_meta = get_post_meta( $post->ID ); ?>
							<?php $bm_client_statuses = array_column( wp_get_post_terms( $post->ID, 'bm-status-client' ), 'name' ); ?>
					<tr>
						<td>
							<div class="bm-dashboard-row">
								<div class="bm-dashboard-column image">
									<?php echo business_manager_client_logo( ( isset( $client_meta['_bm_client_logo_id'][0] ) ? $client_meta['_bm_client_logo_id'][0] : '' ) ); ?>
								</div>

								<div class="bm-dashboard-column">
									<div class="name">
										<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a>
									</div>
								</div>
							</div>
						</td>
						<td><?php echo implode( ',', $bm_client_statuses ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
				<?php
			}
		}
	}

endif;

return new Business_Manager_Client();
