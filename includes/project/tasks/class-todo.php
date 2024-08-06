<?php


// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Business_Manager_Tasks_Todo' ) ) :

/**
 * The main class
 *
 * @since 1.0.0
 */
class Business_Manager_Tasks_Todo {


	
	public function button_confirm() {
		ob_start();

		?>
		<span class="business-manager-todo-button">
		    <span class="business-manager-todo-action business-manager-todo-button-confirm">Sure?</span>
		    <span class="business-manager-todo-action business-manager-todo-button-cancel"><span class="dashicons dashicons-no-alt"></span></span>
		</span>

		<?php
		$output = ob_get_clean();
		$output = preg_replace('/^\s+|\n|\r|\s+$/m', '', $output);
		return $output;
	}

	public function item_edit() {
		ob_start();

		?>
		<div class="business-manager-todo-edit">
		    <div class="business-manager-todo-edit-input">
		        <input type="text" name="e" value="" />
		    </div>
		    <span class="business-manager-todo-edit-save" title="Save"><span class="dashicons dashicons-yes"></span></span>
		</div>

		<?php
		$output = ob_get_clean();
		$output = preg_replace('/^\s+|\n|\r|\s+$/m', '', $output);
		return $output;
	}

	public function item() {
		ob_start();

		?>
		<div class="business-manager-todo-item">
		    <div class="">
		        <div class="business-manager-todo-item-title business-manager-todo-action-edit business-manager-todo-action">
		            <span class="business-manager-todo-item-title-text"></span>
		        </div>
		    </div>
		    <span class="business-manager-todo-item-actions-left">
		        <span class="business-manager-todo-action business-manager-todo-item-checkbox"></span>
		    </span>
		    <span class="business-manager-todo-item-actions-right">
		        <span class="business-manager-todo-action business-manager-todo-item-action-remove"><span class="dashicons dashicons-no-alt"></span></span>
		    </span>
		</div>

		<?php
		$output = ob_get_clean();
		$output = preg_replace('/^\s+|\n|\r|\s+$/m', '', $output);
		return $output;
	}


	public function list_items() {
		ob_start();

		?>
		<div class="business-manager-todo" style="display:none;">

		    <div class="business-manager-todo-footer">
		        <div class="business-manager-todo-add">
		            <span class="business-manager-todo-add-input">
		                <input class="business-manager-todo-add-input-text" placeholder="New Item" tabindex="3" type="text" name="j" name="j" value="" />
		            </span>
		            <a class="business-manager-todo-action business-manager-todo-add-action" href="javascript:"><span class="dashicons dashicons-plus"></span></a>
		        </div>
		    </div>
		    
		    <div class="business-manager-todo-items"></div>

		    
		</div>

		<?php
		$output = ob_get_clean();
		$output = preg_replace('/^\s+|\n|\r|\s+$/m', '', $output);
		return $output;
	}



}

endif;