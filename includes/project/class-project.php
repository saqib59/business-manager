<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Project', false ) ) :

	/**
	 * Business_Manager_Project Class.
	 */
	class Business_Manager_Project {

		public $post_type = 'bm-project';
		public $post_id   = null;
		public $cmb       = null;

		public $access_type = 'bm_access_projects';
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
			add_filter( 'post_row_actions', array( $this, 'login_access_post_row_actions' ), 10, 1 );
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
				return __( 'Save Project', 'business-manager' );
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
						return str_replace( 'Post', __( 'Project', 'business-manager' ), str_replace( 'updated', __( 'saved', 'business-manager' ), str_replace( 'published', __( 'saved', 'business-manager' ), $string ) ) );
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

			// Hide Buttons
			if ( $this->bm_access == 'limited' ) {
				echo '<style type="text/css">';
				echo 'a.page-title-action { display: none; }';
				echo '#delete-action { display: none; }';
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
		public function login_access_post_row_actions( $actions ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || $this->bm_access == 'full' || $post_type != $this->post_type ) {
				return $actions;
			}

			if ( $this->bm_access == 'limited' ) {
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
				$title = __( 'Enter New Project Name', 'business-manager' );
			}
			return $title;
		}

		/**
		 * Returns data for all published projects.
		 *
		 * @since 1.0.0
		 */
		public function get_projects( $query_args = array() ) {
			$user             = get_current_user_id();
			$user_bm_employee = get_user_meta( $user, 'bm_employee', true );

			$query = array(
				'key' => '_bm_project_assigned_to',
			);

			if ( isset( $user_bm_employee ) && ! empty( $user_bm_employee ) ) {
				$query['value'] = $user_bm_employee;
			}

			if ( is_int( $this->post_id ) && $this->post_id != null ) {
				$query['value']   = $this->post_id;
				$query['compare'] = '=';
			}

			$query_args = array(
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => -1,
				'meta_key'       => '_bm_project_end_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					$query,
				),
			);

			$post_data = business_manager_post_items( $query_args );

			return $post_data;
		}

		/**
		 * HTML for the upcoming deadlines on the dashboard.
		 *
		 * @since 1.0.0
		 */
		public function upcoming_deadlines_html_dashboard() {
			$posts = $this->get_projects();
			$count = 0;

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					$end = get_post_meta( $post['id'], '_bm_project_end_date', true );

					// skip if in the past
					if ( current_time( 'timestamp' ) > $end ) {
						continue;
					}

					$meta   = $post['meta'];
					$client = $meta['_bm_project_client'][0]; ?>

				<div class="bm-dashboard-row">
					<div class="bm-dashboard-column image">
							<?php echo business_manager_client_logo( get_post_meta( $client, '_bm_client_logo_id', true ) ); ?>
					</div>

					<div class="bm-dashboard-column">
						<div class="name">
							<a href="<?php echo esc_url( get_edit_post_link( $post['id'] ) ); ?>"><?php echo esc_html( get_the_title( $post['id'] ) ); ?></a> 
								<?php
								if ( $client != 0 && strlen( get_the_title( $client ) ) > 0 ) :
									?>
									<br><span><?php echo esc_html( get_the_title( $client ) ); ?></span><?php endif; ?>
						</div>
						
						<div class="date">
								<?php echo esc_html( business_manager_date_format( $end ) ); ?> 
						</div>
					</div>
				</div>
					<?php
					++$count;
				}
			}

			if ( $count == 0 ) {
				_e( 'No upcoming deadlines', 'business-manager' );
			}
		}

		/**
		 * HTML for projects on the dashboard.
		 *
		 * @since 1.5.0
		 */
		public function projects_html_dashboard() {
			$user             = get_current_user_id();
			$user_bm_employee = get_user_meta( $user, 'bm_employee', true );

			$query = array(
				'key' => '_bm_project_assigned_to',
			);

			if ( isset( $user_bm_employee ) && ! empty( $user_bm_employee ) ) {
				$query['value'] = $user_bm_employee;
			}

			$query_args = array(
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => -1,
				'meta_key'       => '_bm_project_end_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					$query,
				),
			);

			$posts = get_posts( $query_args );

			if ( ! empty( $posts ) ) {
				?>
		<div class="bm-dashboard-scrollbox inside">
			<table class="widefat striped">
				<thead>
					<tr>
						<th style="width:70%;"><?php _e( 'Project', 'business-manager' ); ?></th>
						<th style="width:30%;"><?php _e( 'Status', 'business-manager' ); ?></th>
					</tr>
				</thead>

				<tbody>
						<?php foreach ( $posts as $key => $post ) : ?>
							<?php $bm_project_statuses = array_column( wp_get_post_terms( $post->ID, 'bm-status' ), 'name' ); ?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( $post->post_title ); ?></a></td>
						<td><?php echo implode( ',', $bm_project_statuses ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
				<?php
			}
		}

		public function tasks_html_metabox() {
			include_once BUSINESSMANAGER_DIR . 'includes/project/tasks/kanban-board.php';
		}
	}

endif;

return new Business_Manager_Project();
