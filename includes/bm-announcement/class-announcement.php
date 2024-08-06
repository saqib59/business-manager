<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Announcements', false ) ) :

	class Business_Manager_Announcements {
		/**
		 * The post type for Business Manager Announcement.
		 *
		 * @var string
		 */
		public $post_type = 'bm-announcement';

		/**
		 * Constructor for the Business Manager Announcement class.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Hook in to actions & filters.
		 */
		public function hooks() {
			add_action( 'cmb2_save_post_fields', [ $this, 'on_save_announcement' ], 10, 1 );
		}

		/**
		 * HTML for announcements on the dashboard.
		 */
		public function announcements_html_dashboard() {
			$query_args = [
				'post_status'    => 'publish',
				'post_type'      => $this->post_type,
				'posts_per_page' => - 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			];

			$posts = get_posts( $query_args );

			if ( ! empty( $posts ) ) {
				?>
				<div class="bm-dashboard-scrollbox inside">
					<table class="widefat striped">
						<thead>
						<tr>
							<th style="width:50%;"><?php esc_html_e( 'Annoucements', 'business-manager' ); ?></th>
						</tr>
						</thead>

						<tbody>
						<?php foreach ( $posts as $key => $post ) : ?>
							<?php
							$announcement_department = get_post_meta( $post->ID, '_bm_announcement_employee_departments', true );
							if ( ! current_user_can( 'manage_options' ) && is_numeric( $announcement_department ) ) {
								$employee_id          = business_manager_employee_id( get_current_user_id() );
								$employees_department = business_manager_employee_main_department( $employee_id );
								// skip if the announcement does not belong to employees department.
								if ( $employees_department->term_id !== (int) $announcement_department ) {
									continue;
								}
							}
							?>
							<tr>
								<td>
									<div class="bm-dashboard-row bm-annoucement-title-row">
										<div class="bm-dashboard-column">
											<div class="name">
												<b><?php echo esc_html( get_the_title( $post->ID ) ); ?></b><br>
											</div>
											<div class="desc" style="display: none;">
											<?php echo wp_kses_post( preg_replace( '/<div[^>]*><\/div>/', '<br>', wpautop( get_post_field( 'post_content', $post->ID ) ) ) ); ?><br>
											</div>
										</div>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<!-- Modal to display announcement on click -->
				<div class="bm-modal bm-announcement-modal">
					<div class="bm-modal-content">
						<div class="bm-modal-close">&times;</div>
							<h1 class="bm-annoucement-title">
							</h1>
							<div class="bm-annoucement-desc" style="max-height: 500px; overflow: auto;">
							</div>
						<div class="bm-modal-footer">

						</div>
					</div>
				</div>
				<?php
			}
		}

		/**
		 * Handles the saving of an announcement.
		 *
		 * @param int $announcement_id The ID of the announcement being saved.
		 */
		public function on_save_announcement( $announcement_id ) {
			require_once BUSINESSMANAGER_DIR . 'includes/email-sender.php';

			if ( get_post_type( $announcement_id ) !== $this->post_type ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( wp_is_post_revision( $announcement_id ) ) {
				return;
			}

			$employees_department    = get_post_meta( $announcement_id, '_bm_announcement_employee_departments', true );
			$announcement_email_sent = get_post_meta( $announcement_id, '_bm_announcement_email_sent', true );
			$send_announcement_email = get_post_meta( $announcement_id, '_bm_announcement_send_email', true );

			if ( empty( $announcement_email_sent ) && ! empty( $employees_department ) && $send_announcement_email ) {
				$subject            = __( 'New Announcement', 'business-manager' );
				$announcement_title = get_the_title( $announcement_id );
				// Translators: %s is the title of the announcement.
				$subject = sprintf( __( 'New Announcement - %s', 'business-manager' ), $announcement_title );
				$message = wpautop( get_post_field( 'post_content', $announcement_id ) );
				$message = preg_replace( '/<div[^>]*><\/div>/', '<br>', $message );

				$query_args = [
					'post_status'    => 'publish',
					'post_type'      => 'bm-employee',
					'posts_per_page' => -1,
				];

				if ( is_numeric( $employees_department ) ) {
					// Construct tax query if $employees_department is numeric and an integer.
					$query_args['tax_query'] = [
						[
							'taxonomy' => 'bm-department',
							'field'    => 'id', // Use 'id' to retrieve term IDs.
							'terms'    => $employees_department,
						],
					];
				}

				$bm_employees = business_manager_post_items( $query_args );

				foreach ( $bm_employees as $key => $employee ) {
					$employee_id    = $employee['id'];
					$employee_email = business_manager_employee_work_email( $employee_id );

					if ( $employee_email ) {
						$email_sender = new Business_Manager_Email_Sender( $employee_email, $subject, $message );
						$email_sent   = $email_sender->send_email();
						update_post_meta( $announcement_id, '_bm_announcement_email_sent', $email_sent );
					}
				}
			}
		}
	}

endif;

return new Business_Manager_Announcements();
