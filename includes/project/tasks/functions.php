<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



function business_manager_add_new_task() {
	$task = new Business_Manager_Task();
	return $task->add_new();
}

function business_manager_get_tasks( $cols = 3 ) {
	$task 		= new Business_Manager_Task();
	$tasks 		= $task->get_tasks();
	return $tasks;

}
function business_manager_get_positions() {
	$task 		= new Business_Manager_Task();
	$positions 	= $task->get_positions();
	return $positions;
}



function business_manager_single_task( $task_id ) {
	$task 	= new Business_Manager_Task();
	return $task->output_task( $task_id );
}

function is_business_manager_img( $filetype ) {
	if( $filetype == 'jpeg' || $filetype == 'jpg' || $filetype == 'png' || $filetype == 'gif' ) {
		return true;
	} else {
		return false;
	}
}


function business_manager_column_title( $col ) {
	$title = business_manager_option( 'business-manager-project', "col_{$col}_title" );
	return $title;
}