<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Employee', false ) ) :

	class Business_Manager_Employee {
		/**
		 * The post type for Business Manager Employee.
		 *
		 * @var string
		 */
		public $post_type = 'bm-employee';
		/**
		 * The post ID associated with Business Manager Employee.
		 *
		 * @var int|null
		 */
		public $post_id = null;
		/**
		 * Custom Metabox (CMB) data associated with Business Manager Employee.
		 *
		 * @var mixed|null
		 */
		public $cmb = null;
		/**
		 * The access type for Business Manager Employee.
		 *
		 * @var string
		 */
		public $access_type = 'bm_access_employees';
		/**
		 * The employee ID associated with Business Manager Employee.
		 *
		 * @var int|null
		 */
		public $bm_employee_id;
		/**
		 * The access information for Business Manager Employee.
		 *
		 * @var string|null
		 */
		public $bm_access;


		/**
		 * Constructor for the Business Manager Employee class.
		 *
		 * @param int|null $post_id The post ID associated with the Business Manager Employee.
		 */
		public function __construct( $post_id = null ) {
			$this->post_id = $post_id;

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

			// if we are in the post edit screen.
			if ( isset( $post->ID ) && $post->post_type === $this->post_type ) {
				$this->post_id = (int) $post->ID;
			}
		}

		/**
		 * Hook in to actions & filters.
		 *
		 * @since 1.4.0
		 */
		public function hooks() {
			add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );

			// Interface Changes.
			add_filter( 'gettext', [ $this, 'publish_metabox_rename' ], 10, 2 );
			add_filter( 'post_updated_messages', [ $this, 'post_updated_message' ] );
			add_action( 'admin_menu', [ $this, 'login_access_admin_menu' ] );
			add_filter( 'views_edit-' . $this->post_type, [ $this, 'login_access_views_edit' ], 1 );
			add_filter( 'post_row_actions', [ $this, 'login_access_post_row_actions' ], 10, 2 );
			add_filter( 'bulk_actions-edit-' . $this->post_type, [ $this, 'login_access_bulk_actions' ] );

			add_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );
			// Add new contractor button.
			add_action( 'admin_head', [ $this, 'bm_contractors_button' ] );
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
		 * Rename the "Publish" text in the metabox.
		 *
		 * @since 1.4.4
		 *
		 * @param string $translation The translated text.
		 * @param string $text        The original text before translation.
		 */
		public function publish_metabox_rename( $translation, $text ) {
			global $post_type;

			if ( $post_type == $this->post_type && ( 'Publish' === $text || 'Update' === $text ) ) {
				return __( 'Save Employee', 'business-manager' );
			}

			return $translation;
		}

		/**
		 * Filters the messages displayed after a post is updated.
		 *
		 * @param array $messages The array of post update messages.
		 */
		public function post_updated_message( $messages ) {
			global $post_type;

			if ( $post_type === $this->post_type ) {
				$messages['post'] = array_map(
					function ( $string ) {
						return str_replace( 'Post', __( 'Employee', 'business-manager' ), str_replace( 'updated', __( 'saved', 'business-manager' ), str_replace( 'published', __( 'saved', 'business-manager' ), $string ) ) );
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

			$roles_can_save         = [ 'full', 'edit_profile' ];
			$roles_can_administrate = [ 'full' ];

			$this->post_id = ( isset( $_GET['post'] ) ? sanitize_text_field( $_GET['post'] ) : null );
			$post_type     = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : get_post_type( $this->post_id ) );

			if ( current_user_can( 'manage_options' ) || $post_type !== $this->post_type ) {
				return;
			}

			$object = get_post_type_object( $this->post_type );
			// rename labels.
			if ( $this->bm_employee_id === $this->post_id ) {
				// set get_post_type_labels().
				if ( ! in_array( $this->bm_access, $roles_can_save, true ) && 'post.php' === $pagenow ) {
					$object->labels->edit_item = __( 'View Profile', 'business-manager' );
				} else {
					$object->labels->edit_item = __( 'Edit Profile', 'business-manager' );
				}
			}

			// Hide Metaboxes.
			if ( ! in_array( $this->bm_access, $roles_can_save ) && 'post.php' === $pagenow ) {
				remove_meta_box( 'submitdiv', $this->post_type, 'side' );
				/* Translators: %s is a placeholder representing the required Business Manager Employee labels.*/
				$object->labels->edit_item = sprintf( __( 'View %s', 'business-manager' ), $this->bm_employee_id === $this->post_id ? __( 'Profile', 'business-manager' ) : business_manager_label_employee_single() );
			}

			if ( ! in_array( $this->bm_access, $roles_can_administrate ) && 'post.php' === $pagenow ) {
				remove_meta_box( '_bm_employee_access_box', $this->post_type, 'normal' );
				remove_meta_box( '_bm_employee_notes_box', $this->post_type, 'normal' );
				remove_meta_box( '_bm_employee_access_box', $this->post_type, 'side' );
			}

			// Hide New employee button for edit_profile access.
			if ( ! in_array( $this->bm_access, $roles_can_administrate ) ) {
				echo '<style type="text/css">';
				echo 'a.page-title-action { display: none; }';
				echo '</style>';
			}

			// Hide Buttons.
			if ( ! in_array( $this->bm_access, $roles_can_save, true ) ) {
				echo '<style type="text/css">';
				echo 'a.page-title-action { display: none; }';
				echo '.cmb-add-row { display: none; }';
				echo '.cmb2-upload-button, .cmb2-remove-file-button { display: none !important; }';
				echo '</style>';
			}
		}

		/**
		 * Filters the views displayed on the login access page for editing.
		 *
		 * @param array $views The array of login access views.
		 * @return array The modified or original array of login access views.
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
		 * Change row actions based on Employee Login Access.
		 *
		 * @since 1.4.0
		 *
		 * @param array  $actions The array of row actions.
		 * @param object $post The post object.
		 * @return array The modified or original array of row actions.
		 */
		public function login_access_post_row_actions( $actions, $post ) {
			global $pagenow;

			$roles_can_edit = [ 'full' ];
			$roles_can_view = [ 'limited' ];

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || $post_type != $this->post_type ) {
				return $actions;
			}

			if ( ! in_array( $this->bm_access, $roles_can_edit ) ) {
				if ( in_array( $this->bm_access, $roles_can_view, true ) ) {
					$actions['edit'] = str_replace( __( 'Edit', 'business-manager' ), __( 'View', 'business-manager' ), $actions['edit'] );
				} else {
					unset( $actions['edit'] );
				}

				unset( $actions['trash'], $actions['inline hide-if-no-js'] );
			}

			return $actions;
		}

		/**
		 * Change bulk actions based on Employee Login Access.
		 *
		 * @since 1.4.0
		 *
		 * @param array $actions The array of bulk actions.
		 * @return array The modified or original array of bulk actions.
		 */
		public function login_access_bulk_actions( $actions ) {
			global $pagenow;

			$post_type = ( isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : null );

			if ( current_user_can( 'manage_options' ) || 'full' === $this->bm_access || $post_type !== $this->post_type ) {
				return $actions;
			}
		}

		/**
		 * Run after saving post.
		 *
		 * @since 1.0.0
		 *
		 * @param int    $object_id The ID of the object being saved.
		 * @param string $cmb_id The ID of the Custom Metabox (CMB).
		 * @param bool   $updated Whether the post was updated or not.
		 * @param object $cmb The Custom Metabox (CMB) object.
		 */
		public function on_save_update( $object_id, $cmb_id, $updated, $cmb ) {
			if ( get_post_type( $object_id ) !== $this->post_type ) {
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

			remove_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );

			if ( '_bm_employee_access_box' === $cmb_id && 0 !== $cmb->data_to_save['_bm_employee_user_id'] ) {
				// Remove this Business Manager Employer ID from all current Business Manager Users.
				$users = get_users(
					[
						'meta_key'   => 'bm_employee',
						'meta_value' => $this->post_id,
					]
				);

				foreach ( $users as $u ) {
					delete_user_meta( $u->ID, 'bm_employee' );
				}

				// Add this Business Manager Employer ID to the select Business Manager User.
				update_user_meta( $cmb->data_to_save['_bm_employee_user_id'], 'bm_employee', $this->post_id );
			}

			$this->update_title();

			add_action( 'cmb2_save_post_fields', [ $this, 'on_save_update' ], 10, 4 );
		}

		/**
		 * Update title.
		 *
		 * @since 1.0.0
		 */
		public function update_title() {
			$name = business_manager_employee_full_name( $this->post_id );
			if ( ! $name ) {
				$name = __( '(no name)', 'business-manager' );
			}
			$post = [
				'ID'         => $this->post_id,
				'post_title' => $name,
			];
			wp_update_post( $post );
		}

		/**
		 * Returns data for all published employees.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_args Optional query arguments for customizing the employee data retrieval.
		 * @return array An array of employee data.
		 */
		public function get_employees( $query_args = [] ) {
			$query_args['post_type'] = $this->post_type;
			$post_data               = business_manager_post_items( $query_args );

			return $post_data;
		}

		/**
		 * HTML for the upcoming leave metabox
		 * within employee edit screen.
		 *
		 * @since 1.0.0
		 */
		public function upcoming_leave_html_metabox() {
			$leave = $this->get_leave_data();
			$count = 0;

			if ( ! empty( $leave ) ) {
				foreach ( $leave as $key => $data ) {
					$meta   = $data['meta'];
					$type   = $meta['_bm_leave_type'][0];
					$status = $meta['_bm_leave_status'][0];
					$start  = $meta['_bm_leave_start'][0];
					$end    = $meta['_bm_leave_end'][0];
					$days   = $meta['_bm_leave_total_days'][0];

					// skip if leave is in the past.
					if ( current_time( 'timestamp' ) > $start ) {
						continue;
					} ?>

					<div class="employee-leave bm-metabox">
						<div class="type">
							<span><?php esc_html_e( 'Type:', 'business-manager' ); ?></span>
							<span><?php echo esc_html( $type ); ?></span>
						</div>
						<div class="status">
							<span><?php esc_html_e( 'Status:', 'business-manager' ); ?></span>
							<span class="<?php echo esc_attr( strtolower( $status ) ); ?>"><?php echo esc_html( $status ); ?></span>
						</div>
						<div class="days">
							<span><?php esc_html_e( 'Total Days:', 'business-manager' ); ?></span>
							<span><?php echo esc_html( $days ); ?></span>
						</div>
						<div class="start">
							<span><?php esc_html_e( 'First Day:', 'business-manager' ); ?></span>
							<span><?php echo esc_html( business_manager_date_format( $start ) ); ?></span>
						</div>
						<div class="end">
							<span><?php esc_html_e( 'Last Day:', 'business-manager' ); ?></span>
							<span><?php echo esc_html( business_manager_date_format( $end ) ); ?></span>
						</div>
						<a class="button button-secondary button-small"
							href="<?php echo esc_url( get_edit_post_link( $data['id'] ) ); ?>"><?php esc_html_e( 'Edit Leave', 'business-manager' ); ?></a>
					</div>

					<?php
					++$count;
				}
			}

			if ( $count == 0 ) {
				_e( 'No upcoming leave', 'business-manager' );
			}

			apply_filters( 'business_manager_after_upcoming_leaves_metabox', $leave );
		}

		/**
		 * HTML for the main employee column on the employee list table.
		 *
		 * @since 1.3.2
		 *
		 * @param string $link The HTML link for the employee column.
		 */
		public function employee_html_column( $link ) {
			?>
			<div class="bm-employee-column">
				<?php if ( $link != '' ) : ?>
					<a href="<?php echo esc_url( $link ); ?>"><?php echo business_manager_employee_photo( wp_kses_post( get_post_meta( $this->post_id, '_bm_employee_photo_id', true ) ) ); ?></a>
				<?php else : ?>
					<?php echo business_manager_employee_photo( wp_kses_post( get_post_meta( $this->post_id, '_bm_employee_photo_id', true ) ) ); ?>
				<?php endif; ?>
			</div>

			<div class="bm-employee-column">
				<?php if ( '' !== $link ) : ?>
					<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( business_manager_employee_full_name( $this->post_id ) ); ?></a>
				<?php else : ?>
					<?php echo esc_html( business_manager_employee_full_name( $this->post_id ) ); ?>
				<?php endif; ?>
			</div>
			<?php
		}

		/**
		 * HTML for the upcoming leave column
		 * on employee list table.
		 *
		 * @since 1.0.0
		 */
		public function upcoming_leave_html_column() {
			$leave = $this->get_leave_data();

			if ( ! empty( $leave ) ) {
				foreach ( $leave as $key => $data ) {
					$meta   = $data['meta'];
					$type   = $meta['_bm_leave_type'][0];
					$status = $meta['_bm_leave_status'][0];
					$start  = $meta['_bm_leave_start'][0];
					$end    = $meta['_bm_leave_end'][0];
					$days   = $meta['_bm_leave_total_days'][0];

					// skip if leave is in the past.
					if ( current_time( 'timestamp' ) > $start ) {
						continue;
					}
					?>

					<div class="employee-leave columns">

						<div class="type">
							<span><?php echo esc_html( $days ); ?> <?php esc_html_e( 'Days', 'business-manager' ); ?> - <?php echo esc_html( $type ); ?></span>
						</div>
						<div class="status">
							<span class="<?php echo esc_attr( strtolower( $status ) ); ?>"><?php echo esc_html( $status ); ?></span>
						</div>
						<div class="dates">
							<span><?php echo esc_html( business_manager_date_format( $start ) ); ?> - <?php echo esc_html( business_manager_date_format( $end ) ); ?></span>
						</div>

					</div>

					<?php
				}
			}
		}

		/**
		 * HTML for the upcoming leave on the dashboard.
		 *
		 * @since 1.0.0
		 */
		public function upcoming_leave_html_dashboard() {
			$leave = $this->get_leave_data();
			$count = 0;

			if ( ! empty( $leave ) ) {
				foreach ( $leave as $key => $data ) {
					$leave_id = $data['id'];
					$meta     = $data['meta'];
					$id       = $meta['_bm_leave_employee'][0];
					$type     = $meta['_bm_leave_type'][0];
					$status   = $meta['_bm_leave_status'][0];
					$start    = $meta['_bm_leave_start'][0];
					$end      = $meta['_bm_leave_end'][0];
					$days     = $meta['_bm_leave_total_days'][0] ?? '';

					// skip if leave is in the past.
					if ( current_time( 'timestamp' ) > $start ) {
						continue;
					}
					?>

					<div class="bm-dashboard-row">
						<div class="bm-dashboard-column image">
							<?php echo business_manager_employee_photo( wp_kses_post( get_post_meta( $id, '_bm_employee_photo_id', true ) ) ); ?>
						</div>

						<div class="bm-dashboard-column">
							<div class="name">
								<a href="<?php echo esc_url( get_edit_post_link( $data['id'] ) ); ?>"><?php echo esc_html( business_manager_employee_full_name( $id ) ); ?></a>
							</div>

							<div class="type">
								<?php echo esc_html( $days ); ?> <?php esc_html_e( 'Days', 'business-manager' ); ?>
								- <?php echo esc_html( $type ); ?>
							</div>

							<div class="status">
								<a href="<?php echo esc_url( get_edit_post_link( $leave_id ) ); ?>"><span class="<?php echo esc_attr( strtolower( $status ) ); ?>"><?php echo esc_html( $status ); ?></span></a>
							</div>

							<div class="dates">
								<?php echo esc_html( business_manager_date_format( $start ) ); ?>
								- <?php echo esc_html( business_manager_date_format( $end ) ); ?>
							</div>
						</div>
					</div>

					<?php
					++$count;
				}
			}

			if ( 0 === $count ) {
				esc_html_e( 'No upcoming leave', 'business-manager' );
			}
		}

		/**
		 * Get all leave for an employee (or all employees)
		 * including past and future leave.
		 *
		 * @since 1.0.0
		 */
		public function get_leave_data() {
			$user             = get_current_user_id();
			$user_bm_employee = get_user_meta( $user, 'bm_employee', true );

			$query = [
				'key' => '_bm_leave_employee',
			];

			if ( isset( $user_bm_employee ) && ! empty( $user_bm_employee ) && business_manager_employee_access( $user, 'bm_access_leave' ) === 'limited' ) {
				$query['value'] = $user_bm_employee;
			}

			if ( is_int( $this->post_id ) && $this->post_id != null ) {
				$query['value']   = $this->post_id;
				$query['compare'] = '=';
			}

			$query_args = [
				'post_status'    => 'publish',
				'post_type'      => 'bm-leave',
				'posts_per_page' => - 1,
				'meta_query'     => [
					$query,
				],
			];

			$query_args = apply_filters( 'business_manager_employee_leaves_query_args', $query_args );

			$data = business_manager_post_items( $query_args );

			return $data;
		}

		/**
		 * Get the next birthday date based on the provided birthday.
		 *
		 * @since 1.0.0
		 *
		 * @param string $birthday The birthday date in 'YYYY-MM-DD' format.
		 * @return string The next birthday date in 'YYYY-MM-DD' format.
		 */
		public function get_next_birthday( $birthday ) {
			$date = new DateTime( $birthday );
			$date->modify( '+' . ( date( 'Y' ) - $date->format( 'Y' ) ) . ' years' );
			if ( $date < new DateTime() ) {
				$date->modify( '+1 year' );
			}

			return $date->format( 'Y-m-d' );
		}

		/**
		 * HTML for the upcoming birthdays on the dashboard.
		 *
		 * @since 1.0.0
		 */
		public function upcoming_birthdays_html_dashboard() {
			$args_meta = [
				[
					'key'   => '_bm_employee_status',
					'value' => 'Active',
				],
			];

			$args = [
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => - 1,
				'meta_key'       => '_bm_employee_dob',
				'meta_query'     => $args_meta,
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			];

			$posts = get_posts( $args );
			$count = 0;

			if ( ! empty( $posts ) ) {
				$upcoming_birthdays = [];

				foreach ( $posts as $key => $post ) {
					$post->birthday = $this->get_next_birthday( date( 'Y-m-d', get_post_meta( $post->ID, '_bm_employee_dob', true ) ) );

					// Exclude Employees with birthday more than 3 months away from today.
					if ( strtotime( $post->birthday ) >= strtotime( 'now' ) + ( 3 * MONTH_IN_SECONDS ) ) {
						unset( $posts[ $key ] );
					}
				}

				// Sort birthdays, soonest to latest.
				usort(
					$posts,
					function ( $a, $b ) {
						return strcmp( $a->birthday, $b->birthday );
					}
				);

				foreach ( $posts as $key => $post ) {
					$dob      = get_post_meta( $post->ID, '_bm_employee_dob', true );
					$dob      = date( 'Y-m-d', $dob );
					$birthday = $this->get_next_birthday( $dob );
					?>

					<div class="bm-dashboard-row">
						<div class="bm-dashboard-column image">
							<?php echo business_manager_employee_photo( wp_kses_post( get_post_meta( $post->ID, '_bm_employee_photo_id', true ) ) ); ?>
						</div>

						<div class="bm-dashboard-column">
							<div class="name">
								<a href="<?php echo esc_url( get_edit_post_link( $post->ID ) ); ?>"><?php echo esc_html( business_manager_employee_full_name( $post->ID ) ); ?></a><br>
								<span><?php echo esc_html( business_manager_employee_job_title( $post->ID ) ); ?></span>
							</div>

							<div class="date">
								<?php echo esc_html( business_manager_date_format( strtotime( $birthday ) ) ); ?>
							</div>
						</div>
					</div>

					<?php
					++$count;
				}
			}

			if ( 0 === $count ) {
				esc_html_e( 'No upcoming birthdays', 'business-manager' );
			}
		}

		/**
		 * HTML for employees on the dashboard.
		 *
		 * @since 1.5.0
		 */
		public function employees_html_dashboard() {
			$query_args = [
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => - 1,
				'meta_key'       => '_bm_employee_last_name',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => [
					'key'   => '_bm_employee_status',
					'value' => 'Active',
				],
			];

			$posts = get_posts( $query_args );

			if ( ! empty( $posts ) ) {
				?>
				<div class="bm-dashboard-scrollbox inside">
					<table class="widefat striped">
						<thead>
						<tr>
							<th style="width:50%;"><?php esc_html_e( 'Employee', 'business-manager' ); ?></th>
							<th style="width:50%;"><?php esc_html_e( 'Contact', 'business-manager' ); ?></th>
						</tr>
						</thead>

						<tbody>
						<?php foreach ( $posts as $key => $post ) : ?>
							<?php $employee_meta = get_post_meta( $post->ID ); ?>
							<tr>
								<td>
									<div class="bm-dashboard-row">
										<div class="bm-dashboard-column image">
											<?php echo business_manager_employee_photo( wp_kses_post( get_post_meta( $post->ID, '_bm_employee_photo_id', true ) ) ); ?>
										</div>

										<div class="bm-dashboard-column">
											<div class="name">
												<b><?php echo esc_html( business_manager_employee_full_name( $post->ID ) ); ?></b><br>
												<span><?php echo esc_html( business_manager_employee_job_title( $post->ID ) ); ?></span>
											</div>
										</div>
									</div>
								</td>
								<td>
									<?php echo( isset( $employee_meta['_bm_employee_phone'][0] ) ? esc_html( $employee_meta['_bm_employee_phone'][0] ) : '' ); ?>
									<br><?php echo( isset( $employee_meta['_bm_employee_email'][0] ) ? '<a href="mailto:' . esc_attr( $employee_meta['_bm_employee_email'][0] ) . '">' . esc_html( $employee_meta['_bm_employee_email'][0] ) . '</a>' : '' ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
			}
		}

		/**
		 * Get all assets associated with employee.
		 */
		public function get_assets_data() {
			$user             = get_current_user_id();
			$user_bm_employee = get_user_meta( $user, 'bm_employee', true );

			$query = [
				'key' => '_bm_asset_employee',
			];

			if ( isset( $user_bm_employee ) && ! empty( $user_bm_employee ) && business_manager_employee_access( $user, 'bm_access_leave' ) == 'limited' ) {
				$query['value'] = $user_bm_employee;
			}

			if ( is_int( $this->post_id ) && $this->post_id != null ) {
				$query['value']   = $this->post_id;
				$query['compare'] = '=';
			}

			$query_args = [
				'post_status'    => 'publish',
				'post_type'      => 'bm-asset',
				'posts_per_page' => - 1,
				'meta_query'     => [
					$query,
				],
			];

			$data = business_manager_post_items( $query_args );

			return $data;
		}

		/**
		 * HTML for the assets metabox
		 * within employee edit screen.
		 */
		public function assets_html_metabox() {
			$assets                = $this->get_assets_data();
			$count                 = 0;
			$is_assets_mngr_active = business_manager_addon_is_active( 'business-manager-asset-manager' );

			if ( ! empty( $assets ) && $is_assets_mngr_active ) {
				foreach ( $assets as $key => $data ) {
					$meta        = $data['meta'];
					$asset_title = ucfirst( get_the_title( $data['id'] ) );
					?>
					<div class="employee-leave bm-metabox">
						<div class="title">
							<span><?php _e( 'Asset:', 'business-manager' ); ?></span>
							<a href="<?php echo esc_url( get_edit_post_link( $data['id'] ) ); ?>">
								<?php echo esc_html( $asset_title ); ?>
							</a>
						</div>
					</div>

					<?php
					++$count;
				}
				?>
				<br>
				<a class="button button-secondary button-small bm-add-asset-btn" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bm-asset' ) ); ?>"><?php _e( 'Add New', 'business-manager' ); ?></a>
				<?php
			}

			if ( $count == 0 ) {
				_e( 'No assets allocated', 'business-manager' );
			}
		}

		/**
		 * Renders the button for Business Manager Contractors.
		 */
		public function bm_contractors_button() {
			global $current_screen;

			if ( 'bm-employee' !== $current_screen->post_type ) {
				return;
			}

			$contractors_btn = bm_add_contractor_btn();
			?>
			<script>
				jQuery(function(){
					jQuery('<?php echo $contractors_btn; ?>').insertAfter("body.post-type-bm-employee .wrap a.page-title-action");
				});
			</script>
			<?php
		}
	}

endif;

return new Business_Manager_Employee();
