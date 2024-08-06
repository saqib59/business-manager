<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap bm-dashboard">
	<h1>
		<?php if ( isset( $business['logo'] ) && strlen( $business['logo'] ) > 0 ) : ?>
		<img class="bm-business-logo" src="<?php echo esc_attr( $business['logo'] ); ?>">
		<?php endif; ?>
		<?php echo( isset( $business['business_name'] ) && ! empty( ( $business['business_name'] ) ) ? esc_html( $business['business_name'] ) : __( 'Dashboard', 'business-manager' ) ); ?>
	</h1>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets">
			<div class="postbox-container" id="postbox-container-1">
				<div class="meta-box-sortables">
					<div class="postbox">
						<div class="inside">
							<?php if ( 'full' === business_manager_employee_access( get_current_user_id(), 'bm_access_announcement' ) ) : ?>
								<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bm-announcement' ) ); ?>">
									<?php esc_html_e( 'Add Announcement', 'business-manager' ); ?>
								</a>
							<?php endif; ?>
							<h2><?php echo business_manager_item_count_text( 'bm-announcement' ); ?></h2>
						</div>

						<?php business_manager_announcements_html_dashboard(); ?>

					</div>
				</div>
			</div>
			<?php if ( business_manager_is_enabled( 'projects' ) && ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_projects' ) != 'none' ) ) : ?>
			<div class="postbox-container" id="postbox-container-1">
				<div class="meta-box-sortables">
					<div class="postbox">
						<div class="inside">
							<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=bm-project&orderby=title&order=asc' ); ?>"><?php _e( 'View Projects List', 'business-manager' ); ?></a>
							<h2><?php echo business_manager_item_count_text( 'bm-project' ); ?></h2>
						</div>

						<?php business_manager_projects_html_dashboard(); ?>

						<div class="inside">
							<h3><span class="dashicons dashicons-calendar-alt"></span> <?php _e( 'Deadline Approaching', 'business-manager' ); ?></h3>
							<?php business_manager_upcoming_deadlines_html_dashboard(); ?>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="postbox-container" id="postbox-container-<?php echo( business_manager_is_enabled( 'projects' ) && ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_projects' ) != 'none' ) ? '2' : '1' ); ?>">
				<div class="meta-box-sortables">
					<div class="postbox">
						<?php if ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_employees' ) != 'none' ) : ?>
						<div class="inside">
							<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=bm-employee&orderby=_bm_employee_last_name&order=asc' ); ?>"><?php _e( 'View Employees List', 'business-manager' ); ?></a>
							<h2><?php echo business_manager_item_count_text( 'bm-employee' ); ?></h2>
						</div>

							<?php business_manager_employees_html_dashboard(); ?>

						<?php endif; ?>
						
						<?php if ( business_manager_is_enabled( 'leave' ) && ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_leave' ) != 'none' ) ) : ?>
						<div class="inside">
							<h3><span class="dashicons dashicons-calendar-alt"></span> <?php _e( 'Upcoming Leave', 'business-manager' ); ?></h3>
							<?php business_manager_upcoming_leave_html_dashboard(); ?>
						</div>
						<?php endif; ?>

						<?php if ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_employees' ) == 'full' ) : ?>
						<div class="inside">
							<h3><span class="dashicons dashicons-calendar-alt"></span> <?php _e( 'Upcoming Birthdays', 'business-manager' ); ?></h3>
							<?php business_manager_upcoming_birthdays_html_dashboard(); ?>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php if ( business_manager_is_enabled( 'clients' ) && ( ! current_user_can( 'bm_employee' ) || business_manager_employee_access( get_current_user_id(), 'bm_access_clients' ) != 'none' ) ) : ?>
			<div class="postbox-container" id="postbox-container-3">
				<div class="meta-box-sortables">
					<div class="postbox">
						<div class="inside">
							<a class="button button-secondary" href="<?php echo admin_url( 'edit.php?post_type=bm-client&orderby=title&order=asc' ); ?>"><?php _e( 'View Clients List', 'business-manager' ); ?></a>
							<h2><?php echo business_manager_item_count_text( 'bm-client' ); ?></h2>
						</div>

						<?php business_manager_clients_html_dashboard(); ?>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
