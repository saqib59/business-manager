<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Business_Manager_Task' ) ) :

	/**
	 * The main class.
	 *
	 * @since 1.0.0
	 */
	class Business_Manager_Task {

		// the project id
		public $post_id = 0;

		// the task id
		public $task_id = 0;

		// the task data id
		public $task = array();

		// are we editing an existing task
		public $editing = false;

		/**
		 * Main constructor.
		 *
		 * @since 1.0.0
		 */
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
			if ( isset( $post->ID ) && $post->post_type == 'bm-project' ) {
				$this->post_id = (int) $post->ID;
			}
		}

		/**
		 * Hook in to actions & filters.
		 *
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'wp_ajax_save_task', array( $this, 'save_task' ) );
			add_action( 'wp_ajax_edit_task', array( $this, 'edit_task' ) );
			add_action( 'wp_ajax_delete_the_task', array( $this, 'delete_the_task' ) );
			add_action( 'wp_ajax_update_tasks', array( $this, 'update_tasks' ) );
		}

		/**
		 * Init.
		 */
		public function add_new() {
			?>

			<div id="add-edit-task">

				<div class="input-wrap">
					<?php
					echo business_manager_tasks()->fields->text(
						array(
							'name'        => 'title',
							'id'          => 'title',
							'tabindex'    => '1',
							'placeholder' => __( 'Title', 'business-manager' ),
						)
					);
					?>
				</div>

				<div class="extras">

					<div class="input-wrap">
						<?php
						echo business_manager_tasks()->fields->textarea(
							array(
								'name'        => 'notes',
								'id'          => 'notes',
								'tabindex'    => '2',
								'placeholder' => __( 'Notes', 'business-manager' ),
							)
						);
						?>
					</div>

					<div class="input-wrap">
						<div id="add-todo" style="width: 100%;"></div>
					</div>
					
					<div class="input-wrap">

						<div class="half">
							<?php
							echo business_manager_tasks()->fields->text(
								array(
									'name'        => 'start_date',
									'id'          => 'start_date',
									'class'       => 'datepicker',
									'tabindex'    => '4',
									'placeholder' => __( 'Start', 'business-manager' ),
								)
							);
							?>
						</div>
					
						<div class="half">
							<?php
							echo business_manager_tasks()->fields->text(
								array(
									'name'        => 'end_date',
									'id'          => 'end_date',
									'class'       => 'datepicker',
									'tabindex'    => '5',
									'placeholder' => __( 'Due Date', 'business-manager' ),
								)
							);
							?>
						</div>

					</div>

					<?php do_action( 'business_manager_form_after_dates', $this ); ?>

					<div class="input-wrap">
						<div class="half">
							
							<a class="button button-small" title="<?php _e( 'Add files to this task', 'business-manager' ); ?>" href="javascript:;" id="add-file" tabindex="7"><?php _e( 'Add Files', 'business-manager' ); ?></a>
							
							<div id="file-container" class="hidden">
							</div>
							
							<a class="hidden" title="<?php _e( 'Remove this file', 'business-manager' ); ?>" href="javascript:;" id="remove-file"><?php _e( 'Remove File', 'business-manager' ); ?></a>

							<input type="hidden" id="file_data" name="file_data" value="" />

						</div>
					
						<div class="half pull-right">
							<?php
							echo business_manager_tasks()->fields->colorpicker(
								array(
									'name'     => 'color',
									'id'       => 'color',
									'tabindex' => '8',
								)
							);
							?>
						</div>
					</div>

					<div class="input-wrap">
						<input type="hidden" id="task_id" name="task_id" value="" />
						<a name="save-task" id="save-task" class="button button-primary">Save Task</a>
					</div>

				</div>

			</div>

			<?php
		}

		/**
		 * Save a task.
		 * Also edits a task.
		 */
		public function save_task() {
			$this->ajax_checks();
			$this->permission_checks();

			$this->normalize_task();

			if ( ! $this->editing ) {
				$this->add_new_task();
			} else {
				$this->update_task();
			}

			if ( $this->post_id ) {
				$html = $this->output_task();
				$this->ajax_return( __( 'Saved', 'business-manager' ), 'success', $html, $this->editing );
			} else {
				$this->ajax_return( __( 'Error! Not Saved', 'business-manager' ), 'fail', null );
			}
		}

		/**
		 * Updates the tasks with a new task.
		 */
		public function add_new_task() {
			$tasks = $this->get_tasks() ? $this->get_tasks() : array();
			array_push( $tasks, $this->task );
			update_post_meta( $this->post_id, '_bm_project_tasks', $tasks );
			update_post_meta( $this->post_id, '_bm_project_tasks_last_id', $this->task_id );
		}

		/**
		 * Updates the tasks with an edited task.
		 */
		public function update_task() {
			// pp($this->task);
			// pp($this->task_id);
			$tasks = $this->get_tasks() ? $this->get_tasks() : array();
			if ( $tasks ) {
				foreach ( $tasks as $i => $task ) {
					if ( $task['task_id'] == $this->task_id ) {
						$tasks[ $i ] = $this->task;
					}
				}
				update_post_meta( $this->post_id, '_bm_project_tasks', $tasks );
			}
		}

		/**
		 * Populates the form with a task
		 * so that it can be edited.
		 */
		public function edit_task() {
			$this->ajax_checks();
			$this->permission_checks();

			$this->post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;
			$this->task_id = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : null;

			$data = null;

			$tasks = get_post_meta( $this->post_id, '_bm_project_tasks', true );

			if ( $tasks ) {
				foreach ( $tasks as $i => $task ) {
					if ( $task['task_id'] == $this->task_id ) {
						$data = $task;
					}
				}
			}

			if ( $data ) {
				$this->ajax_return( __( 'Editing', 'business-manager' ), 'success', $data );
			} else {
				$this->ajax_return( __( 'Error with editing', 'business-manager' ), 'fail', '' );
			}
		}

		/**
		 * set the task id on a new task or when editing.
		 */
		public function get_task_id() {
			$count        = 0;
			$task_last_id = get_post_meta( $this->post_id, '_bm_project_tasks_last_id', true );

			if ( ! empty( $task_last_id ) ) {
				$count = get_post_meta( $this->post_id, '_bm_project_tasks_last_id', true );
			}

			return $count;
		}

		/**
		 * set the task id on a new task or when editing.
		 */
		public function set_task_id() {
			return $this->get_task_id() + 1;
		}

		/**
		 * Normalize our data, posted from the Add Task form.
		 */
		public function normalize_task() {
			$todos  = isset( $_POST['todos'] ) ? (array) $_POST['todos'] : array();
			$posted = isset( $_POST['posted'] ) ? (array) $_POST['posted'] : array();
			$data   = array();

			if ( $posted ) {
				foreach ( $posted as $key => $value ) {
					if ( ! $value ) {
						continue;
					}

					if ( $key == 'notes' ) {
						$data[ $key ] = sanitize_textarea_field( $value );
					} elseif ( $key == 'file' ) {
						$value        = str_replace( '\\', '', $value );
						$data[ $key ] = json_decode( $value, true );
					} else {
						$data[ $key ] = sanitize_text_field( $value );
					}
				}
			}

			$this->post_id = $data['post_id'];
			unset( $data['post_id'] );

			// set the task_id if not coming from posted data
			// and set editing to the task id if editing
			if ( ! isset( $data['task_id'] ) || $data['task_id'] == '' ) {
				$data['task_id'] = $this->set_task_id();
			} else {
				$this->editing = $data['task_id'];
			}

			// set the todos if we have them
			if ( $todos ) {
				array_walk_recursive( $todos, 'sanitize_text_field' );
				$data['todos'] = $todos;
			}

			// set the col_id if not coming from posted data
			// if( ! $data['col_id'] )
			// $data['col_id'] = 0;

			$this->task_id = $data['task_id'];
			$this->task    = $data;

			if ( $this->is_empty( $data ) ) {
				exit( 'No post data' );
			}
		}

		/**
		 * Delete a task.
		 */
		public function delete_the_task() {
			$this->ajax_checks();
			$this->permission_checks();

			$this->task_id = isset( $_POST['task_id'] ) ? absint( $_POST['task_id'] ) : null;
			$this->post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;

			if ( ! $this->task_id ) {
				exit( 'fail' );
			}

			$delete = false;

			// get the task and remove the task
			$tasks     = $this->get_tasks();
			$positions = $this->get_positions();

			if ( $tasks ) {
				foreach ( $tasks as $i => $task ) {
					if ( $task['task_id'] == $this->task_id ) {
						unset( $tasks[ $i ] );
					}
				}
				$delete = update_post_meta( $this->post_id, '_bm_project_tasks', $tasks );
			}

			if ( $positions ) {
				foreach ( $positions as $col => $pos ) {
					foreach ( $pos as $key => $task_id ) {
						if ( $task_id == $this->task_id ) {
							unset( $positions[ $col ][ $key ] );
						}
					}
					$delete_pos = update_post_meta( $this->post_id, '_bm_project_tasks_positions', $positions );
				}
			}

			if ( $delete ) {
				$this->ajax_return( __( 'Deleted', 'business-manager' ), 'success', '' );
			} else {
				$this->ajax_return( __( 'Error! Not Deleted', 'business-manager' ), 'fail', '' );
			}
		}

		/**
		 * get the positions.
		 */
		public function get_positions() {
			$positions = get_post_meta( $this->post_id, '_bm_project_tasks_positions', true );
			return $positions;
		}

		/**
		 * get the tasks.
		 */
		public function get_tasks() {
			$tasks = get_post_meta( $this->post_id, '_bm_project_tasks', true );
			return $tasks;
		}

		/**
		 * get a task.
		 */
		public function get_task() {
			$tasks = $this->get_tasks();
			if ( $tasks ) {
				foreach ( $tasks as $key => $task ) {
					if ( $task['task_id'] == $this->task_id ) {
						return $task;
					}
				}
			}
		}

		/**
		 * Output a single task.
		 */
		public function output_task( $task_id = null ) {
			if ( $task_id ) {
				$this->task_id = $task_id;
			}
			$task = $this->get_task();
			ob_start();
			include BUSINESSMANAGER_DIR . 'includes/project/tasks/single-task.php';
			$html = ob_get_clean();
			// $html = preg_replace('/^\s+|\n|\r|\s+$/m', '', $html);
			return $html;
		}

		/**
		 * Save the tasks on the dashboard.
		 */
		public function update_tasks() {
			$this->ajax_checks();

			$positions = isset( $_POST['positions'] ) ? (array) $_POST['positions'] : array();
			$post_id   = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;

			if ( ! $post_id || ! $positions ) {
				$this->ajax_return( __( 'Error! Not updated', 'business-manager' ), 'fail', '' );
			}

			array_walk_recursive( $positions, 'sanitize_text_field' );
			update_post_meta( $post_id, '_bm_project_tasks_positions', $positions );

			$this->ajax_return( __( 'Dashboard updated', 'business-manager' ), 'success', '' );
		}

		/**
		 * Hook in to actions & filters.
		 *
		 * @param string $msg
		 * @param string $result success or fail
		 * @param string $data any data or html to return
		 * @param int    $edit are we editing existing task
		 */
		public function ajax_return( $msg, $result, $data = null, $edit = 0 ) {
			$return = array(
				'message' => $msg,
				'result'  => $result,
				'data'    => $data,
				'edit'    => $edit,
			);

			echo wp_send_json( $return );
		}

		/**
		 * Check our data.
		 */
		public function ajax_checks() {
			if ( ! $_POST ) {
				exit( 'no post data' );
			}

			$nonce = ( isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : null );

			if ( ! wp_verify_nonce( $nonce, 'business_manager_nonce' ) ) {
				exit( 'fail nonce verify' );
			}
		}

		/**
		 * Check our data.
		 */
		public function permission_checks() {
			// ignore the request if the current user doesn't have
			// sufficient permissions
			// if ( current_user_can( 'edit_posts' ) ) {
			// exit( "no post data" );
		}

		/*
		 * Stops saving of empty task
		 */
		private function is_empty( $stringOrArray ) {
			if ( is_array( $stringOrArray ) ) {
				foreach ( $stringOrArray as $value ) {
					if ( ! $this->is_empty( $value ) ) {
						return false;
					}
				}
				return true;
			}
			return ! strlen( $stringOrArray );  // this properly checks on empty string ('')
		}
	}

endif;
