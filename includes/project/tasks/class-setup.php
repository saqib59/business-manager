<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Tasks_Setup', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Tasks_Setup {

		/**
		 *
		 * Singleton instance of the class.
		 *
		 * @var The one true instance
		 * @since 1.0.0
		 */
		protected static $instance = null;

		/**
		 * The task associated with this object.
		 *
		 * @var Task|null The task object or null if not set.
		 */
		public $task;

		/**
		 * Represents a todo item.
		 *
		 * @var mixed Holds the todo item data.
		 */
		public $todo;

		/**
		 * Holds additional fields for the object.
		 *
		 * @var array Holds an array of additional fields.
		 */
		public $fields;

		/**
		 * Main Instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Class constructor.
		 */
		public function __construct() {
			$this->includes();
			$this->hooks();
			$this->init();
		}

		/**
		 * Includes.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			require_once BUSINESSMANAGER_DIR . 'includes/project/tasks/class-task.php';
			require_once BUSINESSMANAGER_DIR . 'includes/project/tasks/class-todo.php';
			require_once BUSINESSMANAGER_DIR . 'includes/project/tasks/class-fields.php';
			require_once BUSINESSMANAGER_DIR . 'includes/project/tasks/functions.php';
		}

		/**
		 * Hook in to actions & filters
		 *
		 * @since 1.0.0
		 */
		public function init() {
			$this->task   = new Business_Manager_Task();
			$this->todo   = new Business_Manager_Tasks_Todo();
			$this->fields = new Business_Manager_Tasks_Fields();
		}



		/**
		 * Hook in to actions & filters
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
			add_action( 'admin_footer', [ $this, 'message_html' ] );
		}

		/**
		 * Init
		 */
		public function admin_styles() {
			$url = BUSINESSMANAGER_URL;
			$v   = BUSINESSMANAGER_VERSION;

			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'bm-tasks', $url . 'assets/css/tasks.css', [], $v );
		}

		/**
		 * Init
		 */
		public function admin_scripts() {

			$url = BUSINESSMANAGER_URL;
			$v   = BUSINESSMANAGER_VERSION;

			wp_enqueue_media();
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-droppable' );
			wp_enqueue_script( 'jquery-ui-draggable' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_script( 'bm-tasks-todo', $url . 'assets/js/tasks-todo.js', [ 'jquery' ], $v, true );

			// our last script.
			wp_enqueue_script(
                'bm-tasks',
                $url . 'assets/js/tasks.js',
                [
					'jquery',
					'wp-color-picker',
					'jquery-ui-core',
					'jquery-ui-datepicker',
					'jquery-ui-droppable',
					'jquery-ui-draggable',
					'jquery-ui-sortable',
					'bm-tasks-todo',
                ],
                $v,
                true
            );

			// js options and i18n.
			$options = [
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'todo_button_confirm' => business_manager_tasks()->todo->button_confirm(),
				'todo_item_edit'      => business_manager_tasks()->todo->item_edit(),
				'todo_item'           => business_manager_tasks()->todo->item(),
				'todo_list'           => business_manager_tasks()->todo->list_items(),
				'nonce'               => wp_create_nonce( 'business_manager_nonce' ),
			];

			$i18n = [
				'todo_remove'      => __( 'Sure?', 'business-manager' ),
				'todo_tooltip'     => __( 'Click to edit', 'business-manager' ),
				'todo_placeholder' => __( 'Todo list', 'business-manager' ),
				'delete'           => __( 'Delete', 'business-manager' ),
				'cancel'           => __( 'Cancel', 'business-manager' ),
				'insert_file'      => __( 'Insert File', 'business-manager' ),
				'edit_task'        => __( 'Edit Task', 'business-manager' ),
				'add_task'         => __( 'Add Task', 'business-manager' ),
			];

			wp_localize_script( 'bm-tasks-todo', 'business_manager_plugin', array_merge( $options, $i18n ) );
		}


		/**
		 * Message wrapper
		 */
		public function message_html() {
			?>

		<div class="business-manager-message" style="display:none"></div>

			<?php
		}
	}

endif;

/**
 * Run the plugin.
 */
function business_manager_tasks() {
	return Business_Manager_Tasks_Setup::instance();
}
business_manager_tasks();