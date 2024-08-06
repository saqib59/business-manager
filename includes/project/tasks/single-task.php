<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $task variable passing through from class-task.php, output_task() function

$title 		= isset( $task['title'] ) ? $task['title'] : '';
$notes 		= isset( $task['notes'] ) ? $task['notes'] : '';
$todos 		= isset( $task['todos'] ) ? $task['todos'] : '';
$file 		= isset( $task['file'] ) ? $task['file'] : '';
$start 		= isset( $task['start_date'] ) ? $task['start_date'] : '';
$end 		= isset( $task['end_date'] ) ? $task['end_date'] : '';
$tags 		= isset( $task['tags'] ) ? $task['tags'] : '';
$color 		= isset( $task['color'] ) ? $task['color'] : '#fff';

?>


<li class="task ui-state-default" data-id="<?php echo (int) $task['task_id']; ?>" style="border-color:<?php echo esc_attr( $color ); ?>">
	
	<div class="handle"></div>
	<div id="delete-task" class="delete"><span class="dashicons dashicons-no-alt"></span></div>
	<div id="edit-task" class="edit"><span class="dashicons dashicons-edit"></span></div>
	
	<div class="inner">

		<?php if( $title ) { ?>
		<div class="title">
			<?php echo esc_html( $title ); ?>	
		</div>
		<?php } ?>

		<?php if( $notes ) { ?>
		<div class="notes">
			<?php echo wp_kses_post( nl2br( $notes ) ); ?>	
		</div>
		<?php } ?>

		<?php 
		if( $todos ) { ?>
			<ul class="todos">
				<?php foreach ( $todos as $key => $todo ) { ?>
					<li class="<?php echo esc_attr( $todo['done'] ); ?>">
						<span class="dashicons"></span>
						<span class="text"><?php echo esc_html( $todo['item'] ); ?></span>
					</li>
				<?php } ?>
			</ul>
		<?php } ?>

		<?php if( $start ) { ?>
		<div class="start_date">
			<strong><?php esc_html_e( 'Start', 'organized' ); ?>:</strong> <?php echo esc_html( $start ); ?>
		</div>
		<?php } ?>

		<?php if( $end ) { ?>
		<div class="end_date">
			<strong><?php esc_html_e( 'Due', 'organized' ); ?>:</strong> <?php echo esc_html( $end ); ?>
		</div>
		<?php } ?>

		<?php if( $file ) { ?>
		<div class="file <?php echo esc_html( $file['subtype'] ); ?>">
			<img src="<?php echo esc_attr($file['preview']); ?>" />
			<div class="icons">
				<span><a target="_blank" href="<?php echo esc_attr( $file['editurl'] ); ?>"><span class="dashicons dashicons-edit"></span></a></span>
				<span><a target="_blank" href="<?php echo esc_attr( $file['url'] ); ?>"><span class="dashicons dashicons-download"></span></a></span>
			</div>
			<?php if( ! is_business_manager_img( $file['subtype'] ) ) { ?>
			<div class="data">
				<span><?php echo esc_attr( $file['filename'] ); ?></span>
				<span><?php echo esc_attr( $file['filesize'] ); ?></span>
			</div>
			<?php } ?>
		</div>
		<?php } ?>

	</div>
</li>

