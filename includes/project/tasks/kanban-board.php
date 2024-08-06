<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="bm-tasks" class="wrap">
	
		<div class="dash-wrap">

			<a id="new-task" class="button button-secondary"><?php _e( 'Add Task', 'business-manager' ); ?></a>
			
			<div class="col-left">

				<div class="postbox add-task">
					<h2><?php _e( 'Add Task', 'business-manager' ); ?></h2>
					<div class="inside">
						<div class="main">
						
							<?php business_manager_add_new_task(); ?>

						</div>
					</div>
				</div>

			</div>
			
			<?php do_action( 'business_manager_kanban_top' ); ?>


			<div class="col-right">

				<div class="sort-wrapper">

					<h4 class="col-1"><?php echo esc_html( business_manager_column_title( 0 ) ); ?>&nbsp;</h4>
					<h4 class="col-2"><?php echo esc_html( business_manager_column_title( 1 ) ); ?>&nbsp;</h4>
					<h4 class="col-3"><?php echo esc_html( business_manager_column_title( 2 ) ); ?>&nbsp;</h4>

					<?php
					$cols      = 3;
					$tasks     = business_manager_get_tasks( $cols );
					$positions = business_manager_get_positions();

					// minus 1 for zero indexing
					for ( $i = 0; $i <= ( $cols - 1 ); $i++ ) {
						?>

						<ul id="sortable<?php echo (int) $i; ?>" class="connected sortable cols-<?php echo (int) $cols; ?>">
							<?php
							if ( isset( $positions[ $i ] ) && $positions[ $i ] ) {
								foreach ( $positions[ $i ] as $index => $task_id ) {
									echo business_manager_single_task( $task_id );
								}
							}
							?>
						</ul>

					<?php } ?>

				</div>

			</div>

		</div>

</div>
