<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Setup', false ) ) :

	/**
	 * Business_Manager_Setup Class.
	 */
	class Business_Manager_Setup {

		/**
		 * Constructor for the Business_Manager_Setup class.
		 *
		 * Registers hooks to handle specific events during the setup.
		 */
		public function __construct() {
			$this->hooks();
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
			add_action( 'admin_footer', [ $this, 'bm_addons_installer_modal' ] );
            add_action( 'init', [ $this, 'do_filters' ], 11 );
            add_action( 'admin_init', [ $this, 'bm_plugin_activation_tracking' ] );
            add_action( 'wp_ajax_bm_ratings_nag_dismiss', [ $this, 'bm_ratings_nag_dismiss' ] );
			add_filter( 'use_block_editor_for_post_type', [ $this, 'bm_disable_gutenberg' ], 999, 2 );
		}

		/**
		 * Perform custom filters..
		 *
		 * Business_manager_metabox_$metabox_id filter to add custom field button.
		 */
		public function do_filters() {
			// Get all post types that exists in bm and loop through its first metabox's first tab to add the custom field button.
			$bm_post_types = business_manager_post_types();
			foreach ( $bm_post_types as $cpt_slug => $cpt_title ) {
				$metabox_ids = business_manager_cpt_metabox_ids( $cpt_slug );
				foreach ( $metabox_ids as $metabox_id => $metabox_title ) {
					add_filter( "business_manager_metabox_{$metabox_id}", [ $this, 'bm_custom_field_installer_field' ], 10, 3 );
					break;
				}
			}
		}

		/**
		 * Enqueue styles for the admin area.
		 */
		public function admin_styles() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$url       = BUSINESSMANAGER_URL;
			$v         = BUSINESSMANAGER_VERSION;

			wp_enqueue_style( 'business-manager-ui', $url . 'assets/css/jquery-ui.min.css', [], $v );
			wp_enqueue_style( 'bm-select2', $url . 'assets/css/select2.min.css', [], $v );
			wp_enqueue_style( 'business-manager', $url . 'assets/css/bm-admin.css', [], $v );

			if ( 'business-manager_page_business-manager-calendar' === $screen_id ) {
				wp_enqueue_style( 'bm-main', $url . 'dist/main.css', [], $v );
			}
		}

		/**
		 * Enqueue scripts for the admin area.
         */
		public function admin_scripts() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$url       = BUSINESSMANAGER_URL;
			$v         = BUSINESSMANAGER_VERSION;

			wp_enqueue_script( 'bm-select2', $url . 'assets/js/select2.min.js', [ 'jquery' ], $v, true );
			wp_enqueue_script( 'bm-parsley', $url . 'assets/js/parsley.min.js', [ 'jquery' ], '2.9.2', true );

			// our last script.
			wp_enqueue_script(
                'business-manager',
                $url . 'assets/js/business-manager.js',
                [
					'jquery',
					'bm-select2',
					'bm-parsley',
                ],
                $v,
                true
            );

			// js options and i18n.
			$options = [
				'ajax_url'                   => admin_url( 'admin-ajax.php' ),
				'nonce'                      => wp_create_nonce( 'bm_nonce' ),
				'is_active_bm_custom_fields' => business_manager_addon_is_active( 'business-manager-custom-fields' ),
			];

			$i18n = [
				'custom_tabs_text' => __( 'Add Custom Tab', 'business-manager' ),
			];

    		wp_localize_script( 'business-manager', 'bm_l10', array_merge( $options, $i18n ) );

			if ( 'business-manager_page_business-manager-calendar' === $screen_id ) {
				wp_enqueue_script( 'bm-main', $url . 'dist/main.js', [], $v, true );
				wp_localize_script( 'bm-main', 'bmApp', [ 'rest_url' => get_rest_url() ] );
			}
		}


		/**
		 * Message wrapper
		 */
		public function message_html() {
			?>
				<div class="business-manager-message" style="display:none"></div>
			<?php
		}

		/**
		 * Displays the template for the custom field installer popup.
		 *
		 * This function is responsible for rendering the template that represents the
		 * custom field installer popup in the user interface.
		 */
		public function bm_addons_installer_modal() {
			bm_get_template_part( 'bm', 'cf-addon-install', BUSINESSMANAGER_DIR . 'templates/' );
			bm_get_template_part( 'bm', 'assets-addon-install', BUSINESSMANAGER_DIR . 'templates/' );
			bm_get_template_part( 'bm', 'contractors-addon-install', BUSINESSMANAGER_DIR . 'templates/' );
		}

		/**
		 * Adds custom field button in the specified metabox.
		 *
		 * @param array $fields    The array of fields to be customized.
		 * @return array           The modified array of fields.
		 */
		public function bm_custom_field_installer_field( $fields ) {
			// including plugin.php because is_plugin_active() does not work with init hook.
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( business_manager_addon_is_active( 'business-manager-custom-fields' ) ) {
				return $fields;
			}

			$add_field = [
				'id'      => 'my_custom_field_new',
				'type'    => 'title',
				'after'   => function () {
					return "<button class='button bm-add-custom-field-btn disabled' type='button'>" . __( 'Add Custom Fields', 'business-manager' ) . '</button>';
				},
				'classes' => 'bm-add-custom-field',
			];

			if ( isset( $fields['general'] ) ) {
				$fields['general'][] = $add_field;
			}
			if ( isset( $fields['personal'] ) ) {
				$fields['personal'][] = $add_field;
			}
			if ( isset( $fields['main'] ) ) {
				$fields['main'][] = $add_field;
			}

			return $fields;
		}

		/**
		 * Track business manager plugin activation.
		 */
		public function bm_plugin_activation_tracking() {
			if ( ! get_option( 'bm_rating_nag_install_date' ) ) {
				update_option( 'bm_rating_nag_install_date', time() );
			}
		}

		/**
		 * Dismiss the ratings nag.
		 */
		public function bm_ratings_nag_dismiss() {
            $is_dismissed = filter_var( $_POST['isDismissed'], FILTER_VALIDATE_BOOLEAN );
			// Check the nonce.
			check_ajax_referer( 'bm_nonce', 'nonce' );
			// Set user meta to dismiss the ratings nag.
			update_option( 'bm_ratings_submitted', true );

			if ( $is_dismissed ) {
				update_option( 'bm_ratings_submitted', false );
				update_option( 'bm_ratings_nag_dismissed', true );
				update_option( 'bm_rating_nag_install_date', time() );
			}

			// Return success response.
			bm_ajax_return( __( 'Success', 'business-manager' ), 200 );
		}

		/**
		 * Disable Gutenberg editor for specific post types in bm.
		 *
		 * @param bool   $use_block_editor Whether the post type supports the block editor.
		 * @param string $post_type        The post type being checked.
		 * @return bool Whether to use the block editor.
		 */
		public function bm_disable_gutenberg( $use_block_editor, $post_type ) {
			$post_types = [ 'bm-announcement' ];
			if ( in_array( $post_type, $post_types, true ) ) {
				$use_block_editor = false;
			}

			return $use_block_editor;
		}
	}

endif;

return new Business_Manager_Setup();
