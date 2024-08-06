<?php
if ( ! class_exists( 'Business_Manager_Menus', false ) ) :

	/**
	 * Business_Manager_Menus Class.
	 */
	class Business_Manager_Menus {

		/**
		 * Employee ID associated with the Business Manager.
		 *
		 * @var string
		 */
		public $bm_employee_id;

		/**
		 * Class constructor for initializing the Business_Manager_Menus object.
		 */
		public function __construct() {
			add_action( 'admin_menu', [ $this, 'admin_menu' ], 9 );
			add_action( 'admin_head', [ $this, 'menu_highlight' ] );
			add_action( 'admin_init', [ $this, 'bm_menu_control' ] );
			add_action( 'admin_bar_menu', [ $this, 'bm_admin_bar_control' ], 999 );

			add_action( 'plugins_loaded', [ $this, 'bm_pluggable' ] );
		}

		/**
		 *
		 * Set Business Manager Variables that rely on pluggable functions.
		 *
		 * @since 1.4.1
		 */
		public function bm_pluggable() {
			$this->bm_employee_id = business_manager_employee_id( get_current_user_id() );
		}

		/**
		 * Add menu items.
		 */
		public function admin_menu() {
			$business = get_option( 'business-manager-general' );
			$title    = ( current_user_can( 'bm_employee' ) && isset( $business['business_name'] ) && ! empty( ( $business['business_name'] ) ) ? $business['business_name'] : __( 'Business Manager', 'business-manager' ) );
			$calendar = new Business_Manager_Calendar();

			$parent = add_menu_page(
				$title, // page title.
				$title, // menu title..
				'business_manager_access', // capability..
				'business-manager', // menu slug..
				null,  // callback..
				'dashicons-editor-bold', // icon url.
				55 // position.
			);

			$submenu = [];

			$dash = new Business_Manager_Dashboard();

			$submenu['dashboard'] = [
				'business-manager', // parent slug.
				$title, // page title.
				__( 'Dashboard', 'business-manager' ), // menu title..
				'business_manager_access', // capability..
				'business-manager', // menu slug..
				[ $dash, 'page' ], // callback..
			];

			if ( $this->bm_employee_id ) {
				$submenu['profile'] = [
					'business-manager', // parent slug.
					__( 'Profile', 'business-manager' ), // page title.
					__( 'Profile', 'business-manager' ), // menu title..
					'business_manager_access', // capability..
					"post.php?post={$this->bm_employee_id}&action=edit",
					null, // callback..
				];
			}

			$submenu['announcement'] = [
				'business-manager', // parent slug.
				'Announcements', // page title.
				'Announcements', // menu title.
				'business_manager_access', // capability.
				'edit.php?post_type=bm-announcement&orderby=title&order=asc', // menu slug.
				null, // callback.
			];

			$submenu['employees'] = [
				'business-manager', // parent slug.
				business_manager_label_employee_plural(), // page title.
				business_manager_label_employee_plural(), // menu title.
				'business_manager_access', // capability.
				'edit.php?post_type=bm-employee&orderby=_bm_employee_last_name&order=asc', // menu slug.
				null, // callback.
			];

			if ( business_manager_is_enabled( 'leave' ) ) {
				$submenu['calendar'] = [
					'business-manager',
					// parent slug.
					business_manager_label_leave_plural(),
					// page title.
					( current_user_can( 'bm_employee' ) ? '' : '&nbsp; ' ) . __( 'Calendar', 'business-manager' ),
					// menu title.
					'business_manager_access',
					// capability.
					'business-manager-calendar',
					// menu slug.
					[ $calendar, 'template' ],
					// callback.
				];

				$submenu['leave'] = [
					'business-manager',
					// parent slug.
					business_manager_label_leave_plural(),
					// page title.
					( current_user_can( 'bm_employee' ) ? '' : '&nbsp; ' ) . business_manager_label_leave_plural(),
					// menu title.
					'business_manager_access',
					// capability.
					'edit.php?post_type=bm-leave&orderby=_bm_leave_start&order=desc',
					// menu slug.
					null,
					// callback.
				];
			}

			if ( business_manager_is_enabled( 'reviews' ) ) {
				$submenu['reviews'] = [
					'business-manager',
					// parent slug.
					business_manager_label_review_plural(),
					// page title.
					( current_user_can( 'bm_employee' ) ? '' : '&nbsp; ' ) . business_manager_label_review_plural(),
					// menu title.
					'business_manager_access',
					// capability.
					'edit.php?post_type=bm-review&orderby=_bm_review_date&order=desc',
					// menu slug.
					null,
					// callback.
				];
			}

			if ( business_manager_is_enabled( 'projects' ) ) {
				$submenu['projects'] = [
					'business-manager', // parent slug.
					business_manager_label_project_plural(), // page title.
					business_manager_label_project_plural(), // menu title.
					'business_manager_access', // capability.
					'edit.php?post_type=bm-project&orderby=title&order=asc', // menu slug.
					null, // callback.
				];
			}

			if ( business_manager_is_enabled( 'clients' ) ) {
				$submenu['clients'] = [
					'business-manager', // parent slug.
					business_manager_label_client_plural(), // page title.
					business_manager_label_client_plural(), // menu title.
					'business_manager_access', // capability.
					'edit.php?post_type=bm-client&orderby=_bm_client&order=asc', // menu slug.
					null, // callback.
				];
			}

			$submenu['departments'] = [
				'business-manager', // parent slug.
				__( 'Departments', 'business-manager' ), // page title.
				__( 'Departments', 'business-manager' ), // menu title.
				'business_manager_access', // capability.
				'edit-tags.php?taxonomy=bm-department&post_type=bm-employee', // menu slug.
				null, // callback.
			];

			if ( business_manager_is_enabled( 'documents' ) ) {
				$submenu['documents'] = [
					'business-manager', // parent slug.
					business_manager_label_document_plural(), // page title.
					business_manager_label_document_plural(), // menu title.
					'business_manager_access', // capability.
					'edit.php?post_type=bm-document&orderby=title&order=asc', // menu slug.
					null, // callback.
				];
			}

			$submenu['settings'] = [
				'business-manager', // parent slug.
				__( 'Settings', 'business-manager' ), // page title.
				__( 'Settings', 'business-manager' ), // menu title.
				'business_manager_access', // capability.
				'business-manager-general', // menu slug.
				[ business_manager()->settings, 'plugin_page' ],
			];

			$submenu = apply_filters( 'business_manager_menu', $submenu );

			$roles_can_access = apply_filters( 'business_manager_menu_roles_can_access', [ 'full', 'limited' ] );
			$none_can_access  = apply_filters( 'business_manager_menu_none_can_access', [ 'dashboard', 'profile' ] );
			foreach ( $submenu as $key => $value ) {
				// Remove menu items based on employee access.
				if ( in_array( $key, $none_can_access ) || in_array( business_manager_employee_access( get_current_user_id(), 'bm_access_' . $key ), $roles_can_access ) ) {
					add_submenu_page( $value[0], $value[1], $value[2], $value[3], $value[4], $value[5] );
				}
			}
		}

		/**
		 * Keep menu open.
		 *
		 * Highlights the wanted admin (sub-) menu items for the CPT.
		 */
		public function menu_highlight() {
			global $parent_file, $submenu_file, $post_type, $post, $pagenow;

			if ( 'bm-employee' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-employee&orderby=_bm_employee_last_name&order=asc';
			}
			if ( 'bm-employee' === $post_type && isset( $post->ID ) && $post->ID == $this->bm_employee_id && $pagenow === 'post.php' ) {
				$parent_file  = 'business-manager';
				$submenu_file = "post.php?post={$this->bm_employee_id}&action=edit";
			}

			if ( 'bm-leave' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-leave&orderby=_bm_leave_start&order=desc';
			}
			if ( 'bm-review' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-review&orderby=_bm_review_date&order=desc';
			}
			if ( 'bm-project' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-project&orderby=title&order=asc';
			}
			if ( 'bm-client' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-client&orderby=_bm_client&order=asc';

			}
			if ( 'bm-document' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-document&orderby=title&order=asc';
			}
			if ( 'bm-employee' === $post_type && isset( $_GET['taxonomy'] ) && sanitize_text_field( $_GET['taxonomy'] ) == 'bm-department' ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit-tags.php?taxonomy=bm-department&post_type=bm-employee';
			}
			if ( 'bm-announcement' === $post_type ) {
				$parent_file  = 'business-manager';
				$submenu_file = 'edit.php?post_type=bm-announcement&orderby=title&order=asc';
			}
		}

		/**
		 * Remove menu options for users with the 'bm_employee' role.
         *
		 * @since  1.4.0
		 */
		public function bm_menu_control() {
			global $user_ID;

			if ( current_user_can( 'bm_employee' ) ) {
				@remove_menu_page( 'index.php' );
				@remove_menu_page( 'edit.php' );
				@remove_menu_page( 'upload.php' );
				@remove_menu_page( 'edit-comments.php' );
				@remove_menu_page( 'tools.php' );
				@remove_menu_page( 'profile.php' );
			}
		}

		/**
		 * Remove admin bar options for users with the 'bm_employee' role.
         *
		 * @param WP_Admin_Bar $wp_admin_bar The WordPress admin bar object.
		 *
		 * @since  1.4.0
		 */
		public function bm_admin_bar_control( $wp_admin_bar ) {
			if ( current_user_can( 'bm_employee' ) ) {
				$wp_admin_bar->remove_node( 'wp-logo' );
				$wp_admin_bar->remove_node( 'site-name' );
				$wp_admin_bar->remove_node( 'comments' );
				$wp_admin_bar->remove_node( 'new-content' );
			}
		}
	}

endif;

return new Business_Manager_Menus();
