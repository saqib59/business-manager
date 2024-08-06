<?php
/**
 * Plugin Name: Business Manager
 * Description: Business Manager is a HR, ERP, CRM, and Project Management plugin that allows you to easily manage your employees, projects, clients, documents and more.
 * Author: Business Manager
 * Author URI: https://bzmngr.com
 * Version: 1.5.9
 * Text Domain: 'business-manager'
 * Domain Path: languages
 * License: GPL2 or later.
 *
 * @package business-manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Helper function for quick debugging
 */
if ( ! function_exists( 'pp' ) ) {
	/**
	 * Pretty print an array for debugging purposes.
	 *
	 * @param array $array The array to be printed.
	 */
	function pp( $array ) {
		echo '<pre style="white-space:pre-wrap;">';
		print_r( $array );
		echo '</pre>' . "\n";
	}
}

/**
 * Main Class.
 *
 * @since 1.0.0
 */
final class Business_Manager {
	/**
	 * Main instance of the Business_Manager class.
	 *
	 * @var Business_Manager|null
	 *
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Version number of the Business Manager plugin.
	 *
	 * @var string
	 */
	public $version = '1.5.9';

	/**
	 * Settings array to store configuration options.
	 *
	 * @var array
	 */
	public $settings = [];

	/**
	 * Class constructor for the Business_Manager class.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->localisation();
		$this->init();

		do_action( 'business_manager_loaded' );
	}

	/**
	 * Define Constants.
     *
	 * @since  1.0.0
	 */
	private function define_constants() {
		$this->define( 'BUSINESSMANAGER_DIR', plugin_dir_path( __FILE__ ) );
		$this->define( 'BUSINESSMANAGER_URL', plugin_dir_url( __FILE__ ) );
		$this->define( 'BUSINESSMANAGER_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'BUSINESSMANAGER_VERSION', $this->version );
	}

	/**
	 * Define a constant with a specified name and value.
	 *
	 * @param string $name  The name of the constant.
	 * @param mixed  $value The value of the constant.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required files.
     *
	 * @since  1.0.0
	 */
	public function includes() {
		// cmb.
		include_once 'includes/lib/cmb2/init.php';
		include_once 'includes/lib/cmb2-tabs-master/plugin.php';
		include_once 'includes/lib/cmb2-attached-posts/cmb2-attached-posts-field.php';
		include_once 'includes/lib/cmb2-conditionals/cmb2-conditionals.php';
		include_once 'includes/lib/cmb2-field-select2/cmb-field-select2.php';

		// calendar.
		include_once 'includes/class-calendar.php';

		// lib.
		include_once 'includes/lib/class-columns.php';
		include_once 'includes/lib/class-settings-api.php';

		include_once 'includes/class-setup.php';
		include_once 'includes/class-post-types.php';
		include_once 'includes/class-menus.php';
		include_once 'includes/class-settings.php';

		include_once 'includes/functions.php';
		include_once 'includes/functions-permissions.php';
		include_once 'includes/functions-metaboxes.php';
		include_once 'includes/functions-columns.php';
		include_once 'includes/functions-template.php';

		include_once 'includes/template-hooks.php';

		// bm-dashboard control.
		include_once 'includes/bm-dashboard/class-list-table.php';
		include_once 'includes/bm-dashboard/functions.php';
		include_once 'includes/bm-dashboard/class-query.php';

		// dashboard.
		include_once 'includes/dashboard/class-dashboard.php';

		// project.
		include_once 'includes/project/class-project.php';
		include_once 'includes/project/class-metaboxes.php';
		include_once 'includes/project/class-columns.php';

		// client.
		include_once 'includes/client/class-client.php';
		include_once 'includes/client/class-metaboxes.php';
		include_once 'includes/client/class-columns.php';

		// employee.
		include_once 'includes/employee/class-employee.php';
		include_once 'includes/employee/class-metaboxes.php';
		include_once 'includes/employee/class-columns.php';

		// leave.
		include_once 'includes/leave/class-leave.php';
		include_once 'includes/leave/class-metaboxes.php';
		include_once 'includes/leave/class-columns.php';

		// review.
		include_once 'includes/review/class-review.php';
		include_once 'includes/review/class-metaboxes.php';
		include_once 'includes/review/class-columns.php';

		// document.
		include_once 'includes/document/class-document.php';
		include_once 'includes/document/class-metaboxes.php';
		include_once 'includes/document/class-columns.php';

		// email.
		include_once 'includes/email/class-metaboxes.php';

		// announcement.
		include_once 'includes/bm-announcement/class-announcement.php';
		include_once 'includes/bm-announcement/class-metaboxes.php';

		include_once 'includes/class-disable-post-lock.php';
	}

	/**
	 * Load Localisation files.
     *
	 * @since  1.0.0
	 */
	public function localisation() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'business-manager' );

		load_textdomain( 'business-manager', WP_LANG_DIR . '/business-manager/business-manager-' . $locale . '.mo' );
		load_plugin_textdomain( 'business-manager', false, plugin_basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Initialize the Business Manager plugin.
	 */
	public function init() {
		$this->settings = new Business_Manager_Settings();

		register_activation_hook( __FILE__, [ $this, 'bm_activation_employee_post_titles' ] );
		add_action( 'plugins_loaded', [ $this, 'upgrade' ] );

		$this->bm_roles();

		add_action( 'add_meta_boxes', [ $this, 'bm_metabox_extension_upsell' ] );

		add_filter( 'admin_init', [ $this, 'bm_redirect' ] );
		add_filter( 'admin_body_class', [ $this, 'bm_body_class' ] );
		add_action( 'admin_notices', [ $this, 'bm_compatibility_notifications' ] );
		add_action( 'admin_notices', [ $this, 'bm_rating_nag' ] );
	}

	/**
	 * Create Business Manager roles for WordPress users.
     *
	 * @since  1.4.0
	 */
	public function bm_roles() {
		$wp_roles = wp_roles();
		if ( isset( $wp_roles ) && $wp_roles->is_role( 'bm_employee' ) === false ) {
			add_role(
				'bm_employee',
				'Business Manager Employee',
				[
					'read'                   => true,
					'publish_posts'          => true,
					'edit_posts'             => true,
					'edit_others_posts'      => true,
					'edit_published_posts'   => true,
					'delete_posts'           => true,
					'delete_others_posts'    => true,
					'delete_published_posts' => true,
					'upload_files'           => true,
				]
			);
		}

		$administrators = get_role( 'administrator' );
		if ( ! $administrators->has_cap( 'business_manager_access' ) ) {
			$administrators->add_cap( 'business_manager_access' );
		}

		$bm_employees = get_role( 'bm_employee' );
		if ( ! $bm_employees->has_cap( 'business_manager_access' ) ) {
			$bm_employees->add_cap( 'business_manager_access' );
		}
	}

	/**
	 * Main Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * @return void
	 * @since 1.0.0
	 * @access protected
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'business-manager' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
     *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'business-manager' ), '1.0.0' );
	}

	/**
	 * Perform any necessary upgrade tasks for the Business_Manager plugin.
	 */
	public function upgrade() {
		$installed_version = get_option( 'business_manager_db_version' );

		if ( $installed_version !== $this->version ) {
			if ( $this->version <= '1.5.3' ) {
				$projects = get_posts(
                    [
						'post_type'   => 'bm-project',
						'post_status' => 'any',
						'numberposts' => - 1,
					]
                );

				if ( $projects ) {
					foreach ( $projects as $post ) {
						$project_assigned_to = get_post_meta( $post->ID, '_bm_project_assigned_to', true );

						if ( ! empty( $project_assigned_to ) && ! is_array( $project_assigned_to ) ) {
							$project_assigned_to = explode( ' ', $project_assigned_to );

							update_post_meta( $post->ID, '_bm_project_assigned_to', $project_assigned_to );
						}
					}
				}

				wp_reset_postdata();
			}

			if ( version_compare( $this->version, '1.5.7', '>' ) ) {

				$employee = new Business_Manager_Employee();
				$leaves   = $employee->get_leave_data();

				if ( ! empty( $leaves ) ) {
					foreach ( $leaves as $key => $data ) {
						$meta  = $data['meta'];
						$start = $meta['_bm_leave_start'][0];
						$end   = $meta['_bm_leave_end'][0];

						// Calculate the number of days excluding weekends.
						$days       = 0;
						$start_date = strtotime( date( 'Y-m-d', $start ) );

						while ( $start_date <= strtotime( date( 'Y-m-d', $end ) ) ) {
							$day_of_week = date( 'N', $start_date );
							if ( '6' !== $day_of_week && '7' !== $day_of_week ) {
								++$days;
							}
							$start_date = strtotime( '+1 day', $start_date );
						}

						update_post_meta( $data['id'], '_bm_leave_total_days', $days );
					}
				}

				wp_reset_postdata();
			}

			update_option( 'business_manager_db_version', $this->version );
		}
	}

	/**
	 * Create Business Manager roles for WordPress users.
     *
	 * @since  1.3.3
	 */
	public function bm_activation_employee_post_titles() {
		$employees = new WP_Query(
            [
				'post_type'      => 'bm-employee',
				'post_status'    => 'publish',
				'posts_per_page' => 999999,
			]
        );

		while ( $employees->have_posts() ) {
			$employees->the_post();
			$employee = new Business_Manager_Employee( get_the_ID() );
			$employee->update_title();
		}

		wp_reset_query();
	}

	/**
	 * Display the upsell content for the Business Manager metabox extension.
	 */
	public function bm_metabox_extension_upsell() {
		global $post;

		$bm_post_types = [
			'bm-employee',
			'bm-leave',
			'bm-review',
			'bm-project',
			'bm-client',
			'bm-document',
			'bm-asset',
			'bm-contractor',
		];
		$post_type     = get_post_type_object( $post->post_type );

		$extensions         = [
			'business-manager-asset-manager',
			'business-manager-contractors',
			'business-manager-custom-fields',
		];
		$missing_extensions = false;

		foreach ( $extensions as $extension ) {
			if ( ! is_plugin_active( $extension . '/' . $extension . '.php' ) ) {
				$missing_extensions = true;
				break;
			}
		}

		if ( true === $missing_extensions && in_array( $post->post_type, $bm_post_types, true ) ) {
			$bm_access = business_manager_employee_access( get_current_user_id(), 'bm_access_' . strtolower( $post_type->label ) );

			if ( current_user_can( 'manage_options' ) || 'full' === $bm_access ) {
				add_meta_box(
					'business-manager-extension-upsell',
					__( 'Extensions', 'business-manager' ),
					'business_manager_extension_upsell',
					$bm_post_types,
					'side',
					'core',
					[
						'bm_post_types' => $bm_post_types,
						'post_type'     => $post_type,
					]
				);
			}
		}
	}

	/**
	 * Redirect users with the 'bm_employee' when required.
     *
	 * @since  1.4.0
	 */
	public function bm_redirect() {
		global $pagenow;

		$user             = get_current_user_id();
		$user_bm_employee = get_user_meta( $user, 'bm_employee', true );
		$post             = ( isset( $_GET['post'] ) ? sanitize_text_field( $_GET['post'] ) : null );

		if ( current_user_can( 'bm_employee' ) ) {
			if ( 'index.php' === $pagenow ) {
				wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
				exit;
			}

			if ( isset( $_GET['post_type'] ) ) {
				$post_type = sanitize_text_field( $_GET['post_type'] );
			} elseif ( isset( $post ) && is_numeric( $post ) ) {
				$post_type = get_post_type( $post );
			}

			// Employees.
			if ( ( isset( $post_type ) && 'bm-employee' === $post_type ) || ( isset( $post_type ) && 'bm-employee' === get_post_type( $post ) ) ) {
				$roles_can_view   = [ 'full', 'limited' ];
				$roles_can_create = [ 'full' ];
				// redirect if user with NO full access tries to create a new employee.
				if ( ! in_array( business_manager_employee_access( $user, 'bm_access_employees' ), $roles_can_create, true ) && 'post-new.php' === $pagenow ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
				// redirect if user with NO full access tries to create a new employee.
				if ( ! in_array( business_manager_employee_access( $user, 'bm_access_employees' ), $roles_can_view, true ) && $user_bm_employee != $post ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
			}

			// Leave.
			if ( ( isset( $post_type ) && 'bm-leave' === $post_type ) || ( isset( $post_type ) && 'bm-leave' === get_post_type( $post ) ) ) {
				if ( business_manager_employee_access( $user, 'bm_access_leave' ) === 'none' ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				} elseif ( business_manager_employee_access( $user, 'bm_access_leave' ) === 'limited' && ( $post !== null && $user_bm_employee !== get_post_meta( $post, '_bm_leave_employee', true ) ) ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
			}

			// Reviews.
			if ( ( isset( $post_type ) && 'bm-review' === $post_type ) || ( isset( $post_type ) && 'bm-review' === get_post_type( $post ) ) ) {
				if ( business_manager_employee_access( $user, 'bm_access_reviews' ) === 'none' ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				} elseif ( business_manager_employee_access( $user, 'bm_access_reviews' ) === 'limited' && ( 'post-new.php' === $pagenow || ( $post !== null && $user_bm_employee != get_post_meta( $post, '_bm_review_employee', true ) ) ) ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
			}

			// Projects.
			if ( ( isset( $post_type ) && 'bm-project' === $post_type ) || ( isset( $post_type ) && get_post_type( $post ) === 'bm-project' ) ) {
				if ( business_manager_employee_access( $user, 'bm_access_projects' ) === 'none' ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				} elseif ( business_manager_employee_access( $user, 'bm_access_projects' ) === 'limited' && ( 'post-new.php' === $pagenow || ( $post !== null ) ) ) {
					$project_assigned_to_array = get_post_meta( $post, '_bm_project_assigned_to', true );
					if ( ! in_array( $user_bm_employee, $project_assigned_to_array, true ) ) {
						wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
						exit;
					}
				}
			}

			// Clients.
			if ( ( isset( $post_type ) && 'bm-client' === $post_type ) || ( isset( $post_type ) && get_post_type( $post ) === 'bm-client' ) ) {
				if ( business_manager_employee_access( $user, 'bm_access_clients' ) === 'none' ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				} elseif ( business_manager_employee_access( $user, 'bm_access_clients' ) === 'limited' && 'post-new.php' === $pagenow ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
			}

			// Documents.
			if ( ( isset( $post_type ) && 'bm-document' === $post_type ) || ( isset( $post_type ) && get_post_type( $post ) === 'bm-document' ) ) {
				if ( business_manager_employee_access( $user, 'bm_access_documents' ) === 'none' ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				} elseif ( business_manager_employee_access( $user, 'bm_access_documents' ) === 'limited' && 'post-new.php' === $pagenow ) {
					wp_redirect( admin_url( 'admin.php?page=business-manager' ), 301 );
					exit;
				}
			}
		}
	}

	/**
	 * Filter the list of CSS body classes for the Business Manager plugin.
	 *
	 * @param array $classes An array of body classes.
	 * @return array Modified array of body classes.
	 */
	public function bm_body_class( $classes ) {
		global $post_type;

		if ( ! empty( $post_type ) && substr( $post_type, 0, 3 ) === 'bm-' ) {
			$classes .= ' business-manager';
		}

		return $classes;
	}

	/**
	 * Display compatibility notifications for the Business Manager plugin.
	 */
	public function bm_compatibility_notifications() {
		$addons_to_be_updated = '';
		// Check and append Custom Fields update link.
		if ( defined( 'BM_CUSTOMFIELDS_DIR' ) ) {
			$bm_cf_plugin_data = get_plugin_data( BM_CUSTOMFIELDS_DIR . '/business-manager-custom-fields.php' );
			if ( version_compare( $bm_cf_plugin_data['Version'], '1.3.1', '<' ) ) {
				$addons_to_be_updated .= $this->get_plugin_update_link( 'Custom Fields' );
			}
		}

		// Check and append Assets Manager update link.
		if ( defined( 'BM_ASSETMANAGER_DIR' ) ) {
			$bm_asset_mngr_plugin_data = get_plugin_data( BM_ASSETMANAGER_DIR . '/business-manager-asset-manager.php' );
			if ( version_compare( $bm_asset_mngr_plugin_data['Version'], '1.3.2', '<' ) ) {
				$addons_to_be_updated .= $this->get_plugin_update_link( 'Assets Manager' );
			}
		}

		// Check and append Business Manager Contractors update link.
		if ( defined( 'BM_CONTRACTORS_DIR' ) ) {
			$bm_asset_mngr_plugin_data = get_plugin_data( BM_CONTRACTORS_DIR . '/business-manager-contractors.php' );
			if ( version_compare( $bm_asset_mngr_plugin_data['Version'], '1.1.1', '<' ) ) {
				$addons_to_be_updated .= $this->get_plugin_update_link( 'Contractors' );
			}
		}

		// Display the notice if updates are required.
		if ( $addons_to_be_updated ) {
			$notification_message = sprintf(
				wp_kses(
					/* Translators: %s is a placeholder representing the required Business Manager version.*/
					__( 'Update %s plugin(s) to ensure compatibility with the current version of Business Manager.', 'business-manager' ),
					[ 'a' => [ 'href' => [] ] ]
				),
				$addons_to_be_updated
			);
			$this->display_admin_notice( 'warning', $notification_message );
		}
	}

	/**
	 * Display a nag for rating the business manager.
	 */
	public function bm_rating_nag() {
		$screen = get_current_screen();
		if ( 'business-manager' !== $screen->parent_base ) {
			return;
		}

		$ratings_nag_submitted = (bool) get_option( 'bm_ratings_submitted' );
		if ( true === $ratings_nag_submitted ) {
			return;
		}

		// Get the install date. Exit if it doesn't exist.
		$install_date = get_option( 'bm_rating_nag_install_date' );
		if ( ! $install_date ) {
			return;
		}

		// Get the number of days since the plugin was installed.
		$days_installed = round( ( time() - $install_date ) / DAY_IN_SECONDS );
		// If dismiseed display notice again after 90 days or else display it after 30 days.
		$days_to_show_notice = (bool) get_option( 'bm_ratings_nag_dismissed' ) ? 90 : 30;

		if ( $days_installed < $days_to_show_notice ) {
			return;
		}

		// Display the ratings nag.
		?>
		<div class="notice notice-info bm-notice-rating is-dismissible">
			<p>
			<?php
				printf(
					/* translators: %s: Plugin name */
					esc_html__(
                        'Thank you for using %1$s, we are a very small 
								team and your review can help this plugin reach more people, 
								so we can continue rolling out more features and updates to this free plugin.',
                        'business-manager'
                    ),
					'<strong>' . esc_html__( 'Business Manager', 'business-manager' ) . '</strong>'
				);
			?>
			</p>
			<p>
				<a href="https://wordpress.org/support/plugin/business-manager/reviews/#new-post" class="button button-primary yes-leave-review" target="_blank">
					<?php esc_html_e( 'Yes, I\'d love to!', 'business-manager' ); ?>
				</a>
				<a href="#" class="button button-secondary dismissable-button">
					<?php esc_html_e( 'No, thanks.', 'business-manager' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Generates the update link for a specific plugin.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @return string The generated update link.
	 */
	private function get_plugin_update_link( $plugin_name ) {
		return '<a href="' . admin_url( 'plugins.php' ) . '">' . esc_html( $plugin_name ) . '</a> ';
	}

	/**
	 * Displays an admin notice of the specified type with the given message.
	 *
	 * @param string $type    The type of the admin notice (e.g., 'success', 'warning', 'error').
	 * @param string $message The message to be displayed in the admin notice.
	 */
	private function display_admin_notice( $type, $message ) {
		echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
	}
}

/**
 * Run the plugin.
 */
function business_manager() {
	return Business_Manager::instance();
}

business_manager();
