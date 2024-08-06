<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves the full name of an employee.
 *
 * @param int|null $id         Optional. The ID of the employee. Default is null.
 * @param bool     $name_break Optional. Whether to break the name into separate parts. Default is false.
 *
 * @return string The full name of the employee.
 */
function business_manager_employee_full_name( $id = null, $name_break = false ) {
	if ( ! $id ) {
		return;
	}
	$fname  = wp_kses_post( get_post_meta( $id, '_bm_employee_first_name', true ) );
	$lname  = wp_kses_post( get_post_meta( $id, '_bm_employee_last_name', true ) );
	$return = $fname . ( $name_break == true ? '<br>' : ' ' ) . $lname;
	return $return;
}

/**
 * Retrieves the job title of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The job title of the employee.
 */
function business_manager_employee_job_title( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_title', true ) );
	return $return;
}

/**
 * Retrieves the date of birth of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The date of birth of the employee.
 */
function business_manager_employee_dob( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_dob', true ) );
	return $return;
}

/**
 * Retrieves the status of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The status of the employee.
 */
function business_manager_employee_status( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_status', true ) );
	return $return;
}

/**
 * Retrieves the type of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The type of the employee.
 */
function business_manager_employee_type( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_type', true ) );
	return $return;
}

/**
 * Retrieves the work phone number of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The work phone number of the employee.
 */
function business_manager_employee_work_phone( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_work_phone', true ) );
	return $return;
}

/**
 * Retrieves the work email of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The work email of the employee.
 */
function business_manager_employee_work_email( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_work_email', true ) );
	return $return;
}

/**
 * Retrieves the personal email of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The personal email of the employee.
 */
function business_manager_employee_personal_email( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_email', true ) );
	return $return;
}

/**
 * Retrieves the address of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The address of the employee.
 */
function business_manager_employee_address( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$return = wp_kses_post( get_post_meta( $id, '_bm_employee_address', true ) );
	return $return;
}

/**
 * Retrieves the main department of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The main department of the employee.
 */
function business_manager_employee_main_department( $id = null ) {
	if ( ! $id ) {
		return;
	}
	$terms = wp_get_object_terms( $id, 'bm-department' );
	if ( ! $terms ) {
		return;
	}
	foreach ( $terms as $key => $term ) {
		if ( 0 == $term->parent ) {
			$main_department = $term;
			break;
		} else {
			$main_department = $term;
		}
	}
	return $main_department;
}

/**
 * Retrieves the name of the main department of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The name of the main department of the employee.
 */
function business_manager_employee_main_department_name( $id = null ) {
	$department = business_manager_employee_main_department( $id );
	return $department->name;
}

/**
 * Retrieves the photo of an employee.
 *
 * @param int|null $id Optional. The ID of the employee. Default is null.
 *
 * @return string The URL of the employee's photo.
 */
function business_manager_employee_photo( $id = null ) {
	$photo = plugin_dir_url( __DIR__ ) . '/assets/images/placeholders/employee-photo.png';

	if ( $id ) {
		$image = wp_get_attachment_image_src( $id, 'medium' );
		if ( is_array( $image ) && strlen( $image[0] ) > 0 ) {
			$photo = $image[0];
		}
	}

	$return = '<div class="bm-image-circle" style="background-image:url(' . $photo . ');">&nbsp;</div>';

	return $return;
}

/**
 * Retrieves the access level of an employee.
 *
 * @param int    $user_id The ID of the employee/user.
 * @param string $access_type The type of access being queried.
 *
 * @return int The access level of the employee/user.
 */
function business_manager_employee_access( $user_id, $access_type ) {
	if ( current_user_can( 'manage_options' ) ) {
		return 'full';
	}
	if ( current_user_can( 'bm_employee' ) ) {
		$employee_id = business_manager_employee_id( $user_id );

		$employee_access = get_post_meta( $employee_id, $access_type, true );

		if ( $employee_access ) {
			return wp_kses_post( $employee_access );
		} else {
			return 'none';
		}
	}
}

/**
 * Retrieves the employee ID associated with a user ID.
 *
 * @param int $user_id The ID of the user.
 *
 * @return int|null The ID of the employee, or null if not found.
 */
function business_manager_employee_id( $user_id ) {
	global $wpdb;

	if ( current_user_can( 'bm_employee' ) ) {
		$sql_employee = $wpdb->prepare(
			'SELECT post_id 
            FROM ' . $wpdb->prefix . "postmeta 
            WHERE meta_key = '_bm_employee_user_id' 
            AND meta_value = %d
            LIMIT 1",
			$user_id
		);
		$employee     = $wpdb->get_col( $sql_employee, 0 );
		if ( ! $employee ) {
			return 0;
		}
		return ( $employee[0] ?: 0 );
	} else {
		return 0;
	}
}

/**
 * Retrieves the logo of a client.
 *
 * @param int|null $id Optional. The ID of the client. Default is null.
 *
 * @return string The URL of the client's logo.
 */
function business_manager_client_logo( $id = null ) {
	$logo = plugin_dir_url( __DIR__ ) . '/assets/images/placeholders/client-logo.png';

	if ( $id ) {
		$image = wp_get_attachment_image_src( $id, 'medium' );
		if ( is_array( $image ) && strlen( $image[0] ) > 0 ) {
			$logo = $image[0];
		}
	}

	$return = '<div class="bm-image-circle" style="background-image:url(' . esc_url( $logo ) . ');">&nbsp;</div>';

	return $return;
}

/**
 * Retrieves posts based on provided query arguments.
 *
 * @param array $query_args Optional. The query parameters to retrieve posts. Default is an empty array.
 *
 * @return array An array containing post data with IDs and meta information.
 */
function business_manager_post_items( $query_args = [] ) {
	$args = wp_parse_args(
		$query_args,
		[
			'numberposts' => -1,
			'post_status' => 'publish',
		]
	);

	$posts = get_posts( $args );

	$data = [];
	if ( $posts ) {
		$i = 0;
		foreach ( $posts as $post ) {
			$data[ $i ]['id']   = $post->ID;
			$data[ $i ]['meta'] = get_post_meta( $post->ID, '', true );
			++$i;
		}
	}

	return $data;
}

/**
 * Retrieves the text representing the count of items for a given post type.
 *
 * @param string $post_type Optional. The post type for which to retrieve the count text. Default is empty string.
 *
 * @return string|null The text representing the count of items, or null if the post type is empty.
 */
function business_manager_item_count_text( $post_type = '' ) {
	if ( ! $post_type ) {
		return;
	}

	$query = business_manager_generate_item_count_meta_query( $post_type );

	$args  = [
		'numberposts' => -1,
		'post_status' => 'publish',
		'post_type'   => $post_type,
		'meta_query'  => [
			$query,
		],
	];
	$posts = count( get_posts( $args ) );
	$obj   = get_post_type_object( $post_type );
	$text  = sprintf( _n( '%s' . $obj->labels->singular_name, '%s' . $obj->labels->name, $posts, 'business-manager' ), '<span>' . esc_html( $posts ) . '</span> ' );

	return $text;
}

/**
 * Generates a meta query to count items for a given post type.
 *
 * @param string $post_type The post type for which to generate the meta query.
 *
 * @return array The generated meta query.
 */
function business_manager_generate_item_count_meta_query( $post_type ) {
	$query = [];
	switch ( $post_type ) {
		case 'bm-project':
			$user             = get_current_user_id();
			$user_bm_employee = get_user_meta( $user, 'bm_employee', true );

			if ( ! current_user_can( 'manage_options' ) ) { // for administrator show count of all projects.
				$query = [
					'key'     => '_bm_project_assigned_to',
					'value'   => wp_json_encode( strval( $user_bm_employee ) ),
					'compare' => 'LIKE',
				];
			}

		case 'bm-announcement':
			if ( ! current_user_can( 'manage_options' ) ) {

				$employee_id          = business_manager_employee_id( get_current_user_id() );
				$employees_department = business_manager_employee_main_department( $employee_id );
				$query                = [
					'relation' => 'OR',
                    [
						'key'     => '_bm_announcement_employee_departments',
						'value'   => $employees_department->term_id,
						'compare' => '=',
                    ],
					[
						'key'     => '_bm_announcement_employee_departments',
						'value'   => 'all',
						'compare' => '=',
                    ],
				];
			}
	}
	return $query;
}

/**
 * Outputs data for the upcoming leave on the dashboard for all employees.
 *
 * @return html
 */
function business_manager_upcoming_leave_html_dashboard() {
	$employee = new Business_Manager_Employee();
	return $employee->upcoming_leave_html_dashboard();
}

/**
 * Outputs data for the upcoming birthdays on the dashboard for all employees.
 *
 * @return html
 */
function business_manager_upcoming_birthdays_html_dashboard() {
	$employee = new Business_Manager_Employee();
	return $employee->upcoming_birthdays_html_dashboard();
}

/**
 * Outputs data for the upcoming deadlines on the dashboard for all projects.
 *
 * @return html
 */
function business_manager_upcoming_deadlines_html_dashboard() {
	$projects = new Business_Manager_Project();
	return $projects->upcoming_deadlines_html_dashboard();
}

/**
 * Outputs data for projects on the dashboard.
 *
 * @return html
 */
function business_manager_projects_html_dashboard() {
	$projects = new Business_Manager_Project();
	return $projects->projects_html_dashboard();
}

/**
 * Outputs data for employees on the dashboard.
 *
 * @return html
 */
function business_manager_employees_html_dashboard() {
	$employees = new Business_Manager_Employee();
	return $employees->employees_html_dashboard();
}

/**
 * Outputs data for clients on the dashboard.
 *
 * @return html
 */
function business_manager_clients_html_dashboard() {
	$clients = new Business_Manager_Client();
	return $clients->clients_html_dashboard();
}

/**
 * Outputs data for announcements on the dashboard.
 *
 * @return html
 */
function business_manager_announcements_html_dashboard() {
	$announcements = new Business_Manager_Announcements();
	return $announcements->announcements_html_dashboard();
}

/*
============================= LABEL FUNCTIONS ==============================
*/

/**
 * Retrieves the label for a single employee entity.
 *
 * @return string The label for a single employee.
 */
function business_manager_label_employee_single() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['singular'] ) ? esc_html( $option['singular'] ) : __( 'Employee', 'business-manager' );
}

/**
 * Retrieves the label for multiple employee entities.
 *
 * @return string The label for multiple employee entities.
 */
function business_manager_label_employee_plural() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['plural'] ) ? esc_html( $option['plural'] ) : __( 'Employees', 'business-manager' );
}

/**
 * Retrieves the label for a single review entity.
 *
 * @return string The label for a single review entity.
 */
function business_manager_label_review_single() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['review_singular'] ) ? esc_html( $option['review_singular'] ) : __( 'Review', 'business-manager' );
}

/**
 * Retrieves the label for multiple review entities.
 *
 * @return string The label for multiple review entities.
 */
function business_manager_label_review_plural() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['review_plural'] ) ? esc_html( $option['review_plural'] ) : __( 'Reviews', 'business-manager' );
}

/**
 * Retrieves the label for a single leave entity.
 *
 * @return string The label for a single leave entity.
 */
function business_manager_label_leave_single() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['leave_singular'] ) ? esc_html( $option['leave_singular'] ) : __( 'Leave', 'business-manager' );
}

/**
 * Retrieves the label for multiple leave entities.
 *
 * @return string The label for multiple leave entities.
 */
function business_manager_label_leave_plural() {
	$option = get_option( 'business-manager-employee' );
	return isset( $option['leave_plural'] ) ? esc_html( $option['leave_plural'] ) : __( 'Leave', 'business-manager' );
}

/**
 * Retrieves the label for a single client entity.
 *
 * @return string The label for a single client entity.
 */
function business_manager_label_client_single() {
	$option = get_option( 'business-manager-client' );
	return isset( $option['singular'] ) ? esc_html( $option['singular'] ) : __( 'Client', 'business-manager' );
}

/**
 * Retrieves the label for multiple client entities.
 *
 * @return string The label for multiple client entities.
 */
function business_manager_label_client_plural() {
	$option = get_option( 'business-manager-client' );
	return isset( $option['plural'] ) ? esc_html( $option['plural'] ) : __( 'Clients', 'business-manager' );
}

/**
 * Retrieves the label for a single project entity.
 *
 * @return string The label for a single project entity.
 */
function business_manager_label_project_single() {
	$option = get_option( 'business-manager-project' );
	return isset( $option['singular'] ) ? esc_html( $option['singular'] ) : __( 'Project', 'business-manager' );
}

/**
 * Retrieves the label for multiple project entities.
 *
 * @return string The label for multiple project entities.
 */
function business_manager_label_project_plural() {
	$option = get_option( 'business-manager-project' );
	return isset( $option['plural'] ) ? esc_html( $option['plural'] ) : __( 'Projects', 'business-manager' );
}

/**
 * Retrieves the label for a single document entity.
 *
 * @return string The label for a single document entity.
 */
function business_manager_label_document_single() {
	$option = get_option( 'business-manager-document' );
	return isset( $option['singular'] ) ? esc_html( $option['singular'] ) : __( 'Document', 'business-manager' );
}

/**
 * Retrieves the label for multiple document entities.
 *
 * @return string The label for multiple document entities.
 */
function business_manager_label_document_plural() {
	$option = get_option( 'business-manager-document' );
	return isset( $option['plural'] ) ? esc_html( $option['plural'] ) : __( 'Documents', 'business-manager' );
}

/*
============================= HELPER FUNCTIONS ==============================
*/

/**
 * Checks if a specific module is enabled in business manager.
 *
 * @param string $module The name of the module to check.
 * @return bool True if the module is enabled, false otherwise.
 */
function business_manager_is_enabled( $module ) {
	$option = get_option( 'business-manager-general' );
	$return = true;
	if ( isset( $option['modules'][ $module ] ) ) {
		$return = false;
	}
	return $return;
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash.
 *
 * @since  1.0
 * @return string $path Absolute path to the upload directory
 */
function business_manager_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/business-manager' );
	$path = $wp_upload_dir['basedir'] . '/business-manager';

	return apply_filters( 'business_manager_upload_dir', $path );
}

/**
 * Formats the provided amount as a monetary value.
 *
 * @param float $amount The amount to be formatted.
 * @return string The formatted monetary value.
 */
function business_manager_money_format( $amount ) {
	if ( strlen( $amount ) == 0 ) {
		return;
	}
	$locale   = get_locale();
	$option   = get_option( 'business-manager-payroll' );
	$currency = isset( $option['currency'] ) ? $option['currency'] : 'USD';
	$fmt      = new NumberFormatter( $locale, NumberFormatter::CURRENCY );
	$return   = $fmt->formatCurrency( $amount, $currency );
	return trim( $return );
}

/**
 * Formats the provided date according to the business manager system's date format.
 *
 * @param string $date The date to be formatted.
 * @return string The formatted date string.
 */
function business_manager_date_format( $date ) {
	if ( ! $date ) {
		return;
	}
	$date = date_i18n( get_option( 'date_format' ), $date );
	return $date;
}

/**
 * Retrieves the value of a specified option within a given section of Business Manager settings.
 *
 * @param string      $section The section name where the option resides.
 * @param string|null $option Optional. The name of the option to retrieve. If not provided, returns the entire section.
 *
 * @return mixed|null The value of the specified option, or null if the section or option doesn't exist.
 */
function business_manager_option( $section, $option = null ) {
	if ( ! $section ) {
		return null;
	}
	$opt    = get_option( $section );
	$return = isset( $opt[ $option ] ) ? $opt[ $option ] : null;
	return $return;
}

/**
 * Inserts a new key-value pair into an associative array before a specified key.
 *
 * @param mixed $key The key before which the new key-value pair should be inserted.
 * @param array $array The associative array into which the new key-value pair will be inserted.
 * @param mixed $new_key The key of the new element to be inserted.
 * @param mixed $new_value The value of the new element to be inserted.
 * @return bool
 */
function business_manager_array_insert_before( $key, array &$array, $new_key, $new_value ) {
	if ( array_key_exists( $key, $array ) ) {
		$new = [];
		foreach ( $array as $k => $value ) {
			if ( $k === $key ) {
				$new[ $new_key ] = $new_value;
			}
			$new[ $k ] = $value;
		}
		return $new;
	}
	return false;
}

/**
 * Inserts a new key-value pair into an associative array after a specified key.
 *
 * @param mixed $key The key after which the new key-value pair should be inserted.
 * @param array $array The associative array into which the new key-value pair will be inserted.
 * @param mixed $new_key The key of the new element to be inserted.
 * @param mixed $new_value The value of the new element to be inserted.
 * @return bool
 */
function business_manager_array_insert_after( $key, array &$array, $new_key, $new_value ) {
	if ( array_key_exists( $key, $array ) ) {
		$new = [];
		foreach ( $array as $k => $value ) {
			$new[ $k ] = $value;
			if ( $k === $key ) {
				$new[ $new_key ] = $new_value;
			}
		}
		return $new;
	}
	return false;
}

/**
 * Retrieves the name of a country based on its ID.
 *
 * @param int $id The ID of the country.
 * @return string The name of the country if found, or null if the country ID is invalid.
 */
function business_manager_get_country( $id ) {
	return business_manager_countries_array()[ $id ];
}

/**
 * Retrieves an array of countries with their country codes and names.
 *
 * @return array An array of countries where the keys are the country IDs and the values are the country names.
 */
function business_manager_countries_array() {
	return [
		''   => __( 'None', 'business-manager' ),
		'AF' => __( 'Afghanistan', 'business-manager' ),
		'AL' => __( 'Albania', 'business-manager' ),
		'DZ' => __( 'Algeria', 'business-manager' ),
		'AS' => __( 'American Samoa', 'business-manager' ),
		'AD' => __( 'Andorra', 'business-manager' ),
		'AO' => __( 'Angola', 'business-manager' ),
		'AI' => __( 'Anguilla', 'business-manager' ),
		'AQ' => __( 'Antarctica', 'business-manager' ),
		'AG' => __( 'Antigua and Barbuda', 'business-manager' ),
		'AR' => __( 'Argentina', 'business-manager' ),
		'AM' => __( 'Armenia', 'business-manager' ),
		'AW' => __( 'Aruba', 'business-manager' ),
		'AU' => __( 'Australia', 'business-manager' ),
		'AT' => __( 'Austria', 'business-manager' ),
		'AZ' => __( 'Azerbaijan', 'business-manager' ),
		'BS' => __( 'Bahamas', 'business-manager' ),
		'BH' => __( 'Bahrain', 'business-manager' ),
		'BD' => __( 'Bangladesh', 'business-manager' ),
		'BB' => __( 'Barbados', 'business-manager' ),
		'BY' => __( 'Belarus', 'business-manager' ),
		'BE' => __( 'Belgium', 'business-manager' ),
		'BZ' => __( 'Belize', 'business-manager' ),
		'BJ' => __( 'Benin', 'business-manager' ),
		'BM' => __( 'Bermuda', 'business-manager' ),
		'BT' => __( 'Bhutan', 'business-manager' ),
		'BO' => __( 'Bolivia', 'business-manager' ),
		'BA' => __( 'Bosnia and Herzegovina', 'business-manager' ),
		'BW' => __( 'Botswana', 'business-manager' ),
		'BV' => __( 'Bouvet Island', 'business-manager' ),
		'BR' => __( 'Brazil', 'business-manager' ),
		'BQ' => __( 'British Antarctic Territory', 'business-manager' ),
		'IO' => __( 'British Indian Ocean Territory', 'business-manager' ),
		'VG' => __( 'British Virgin Islands', 'business-manager' ),
		'BN' => __( 'Brunei', 'business-manager' ),
		'BG' => __( 'Bulgaria', 'business-manager' ),
		'BF' => __( 'Burkina Faso', 'business-manager' ),
		'BI' => __( 'Burundi', 'business-manager' ),
		'KH' => __( 'Cambodia', 'business-manager' ),
		'CM' => __( 'Cameroon', 'business-manager' ),
		'CA' => __( 'Canada', 'business-manager' ),
		'CT' => __( 'Canton and Enderbury Islands', 'business-manager' ),
		'CV' => __( 'Cape Verde', 'business-manager' ),
		'KY' => __( 'Cayman Islands', 'business-manager' ),
		'CF' => __( 'Central African Republic', 'business-manager' ),
		'TD' => __( 'Chad', 'business-manager' ),
		'CL' => __( 'Chile', 'business-manager' ),
		'CN' => __( 'China', 'business-manager' ),
		'CX' => __( 'Christmas Island', 'business-manager' ),
		'CC' => __( 'Cocos [Keeling] Islands', 'business-manager' ),
		'CO' => __( 'Colombia', 'business-manager' ),
		'KM' => __( 'Comoros', 'business-manager' ),
		'CG' => __( 'Congo - Brazzaville', 'business-manager' ),
		'CD' => __( 'Congo - Kinshasa', 'business-manager' ),
		'CK' => __( 'Cook Islands', 'business-manager' ),
		'CR' => __( 'Costa Rica', 'business-manager' ),
		'HR' => __( 'Croatia', 'business-manager' ),
		'CU' => __( 'Cuba', 'business-manager' ),
		'CY' => __( 'Cyprus', 'business-manager' ),
		'CZ' => __( 'Czech Republic', 'business-manager' ),
		'CI' => __( 'Côte d’Ivoire', 'business-manager' ),
		'DK' => __( 'Denmark', 'business-manager' ),
		'DJ' => __( 'Djibouti', 'business-manager' ),
		'DM' => __( 'Dominica', 'business-manager' ),
		'DO' => __( 'Dominican Republic', 'business-manager' ),
		'NQ' => __( 'Dronning Maud Land', 'business-manager' ),
		'DD' => __( 'East Germany', 'business-manager' ),
		'EC' => __( 'Ecuador', 'business-manager' ),
		'EG' => __( 'Egypt', 'business-manager' ),
		'SV' => __( 'El Salvador', 'business-manager' ),
		'GQ' => __( 'Equatorial Guinea', 'business-manager' ),
		'ER' => __( 'Eritrea', 'business-manager' ),
		'EE' => __( 'Estonia', 'business-manager' ),
		'ET' => __( 'Ethiopia', 'business-manager' ),
		'FK' => __( 'Falkland Islands', 'business-manager' ),
		'FO' => __( 'Faroe Islands', 'business-manager' ),
		'FJ' => __( 'Fiji', 'business-manager' ),
		'FI' => __( 'Finland', 'business-manager' ),
		'FR' => __( 'France', 'business-manager' ),
		'GF' => __( 'French Guiana', 'business-manager' ),
		'PF' => __( 'French Polynesia', 'business-manager' ),
		'TF' => __( 'French Southern Territories', 'business-manager' ),
		'FQ' => __( 'French Southern and Antarctic Territories', 'business-manager' ),
		'GA' => __( 'Gabon', 'business-manager' ),
		'GM' => __( 'Gambia', 'business-manager' ),
		'GE' => __( 'Georgia', 'business-manager' ),
		'DE' => __( 'Germany', 'business-manager' ),
		'GH' => __( 'Ghana', 'business-manager' ),
		'GI' => __( 'Gibraltar', 'business-manager' ),
		'GR' => __( 'Greece', 'business-manager' ),
		'GL' => __( 'Greenland', 'business-manager' ),
		'GD' => __( 'Grenada', 'business-manager' ),
		'GP' => __( 'Guadeloupe', 'business-manager' ),
		'GU' => __( 'Guam', 'business-manager' ),
		'GT' => __( 'Guatemala', 'business-manager' ),
		'GG' => __( 'Guernsey', 'business-manager' ),
		'GN' => __( 'Guinea', 'business-manager' ),
		'GW' => __( 'Guinea-Bissau', 'business-manager' ),
		'GY' => __( 'Guyana', 'business-manager' ),
		'HT' => __( 'Haiti', 'business-manager' ),
		'HM' => __( 'Heard Island and McDonald Islands', 'business-manager' ),
		'HN' => __( 'Honduras', 'business-manager' ),
		'HK' => __( 'Hong Kong SAR China', 'business-manager' ),
		'HU' => __( 'Hungary', 'business-manager' ),
		'IS' => __( 'Iceland', 'business-manager' ),
		'IN' => __( 'India', 'business-manager' ),
		'ID' => __( 'Indonesia', 'business-manager' ),
		'IR' => __( 'Iran', 'business-manager' ),
		'IQ' => __( 'Iraq', 'business-manager' ),
		'IE' => __( 'Ireland', 'business-manager' ),
		'IM' => __( 'Isle of Man', 'business-manager' ),
		'IL' => __( 'Israel', 'business-manager' ),
		'IT' => __( 'Italy', 'business-manager' ),
		'JM' => __( 'Jamaica', 'business-manager' ),
		'JP' => __( 'Japan', 'business-manager' ),
		'JE' => __( 'Jersey', 'business-manager' ),
		'JT' => __( 'Johnston Island', 'business-manager' ),
		'JO' => __( 'Jordan', 'business-manager' ),
		'KZ' => __( 'Kazakhstan', 'business-manager' ),
		'KE' => __( 'Kenya', 'business-manager' ),
		'KI' => __( 'Kiribati', 'business-manager' ),
		'KW' => __( 'Kuwait', 'business-manager' ),
		'KG' => __( 'Kyrgyzstan', 'business-manager' ),
		'LA' => __( 'Laos', 'business-manager' ),
		'LV' => __( 'Latvia', 'business-manager' ),
		'LB' => __( 'Lebanon', 'business-manager' ),
		'LS' => __( 'Lesotho', 'business-manager' ),
		'LR' => __( 'Liberia', 'business-manager' ),
		'LY' => __( 'Libya', 'business-manager' ),
		'LI' => __( 'Liechtenstein', 'business-manager' ),
		'LT' => __( 'Lithuania', 'business-manager' ),
		'LU' => __( 'Luxembourg', 'business-manager' ),
		'MO' => __( 'Macau SAR China', 'business-manager' ),
		'MK' => __( 'Macedonia', 'business-manager' ),
		'MG' => __( 'Madagascar', 'business-manager' ),
		'MW' => __( 'Malawi', 'business-manager' ),
		'MY' => __( 'Malaysia', 'business-manager' ),
		'MV' => __( 'Maldives', 'business-manager' ),
		'ML' => __( 'Mali', 'business-manager' ),
		'MT' => __( 'Malta', 'business-manager' ),
		'MH' => __( 'Marshall Islands', 'business-manager' ),
		'MQ' => __( 'Martinique', 'business-manager' ),
		'MR' => __( 'Mauritania', 'business-manager' ),
		'MU' => __( 'Mauritius', 'business-manager' ),
		'YT' => __( 'Mayotte', 'business-manager' ),
		'FX' => __( 'Metropolitan France', 'business-manager' ),
		'MX' => __( 'Mexico', 'business-manager' ),
		'FM' => __( 'Micronesia', 'business-manager' ),
		'MI' => __( 'Midway Islands', 'business-manager' ),
		'MD' => __( 'Moldova', 'business-manager' ),
		'MC' => __( 'Monaco', 'business-manager' ),
		'MN' => __( 'Mongolia', 'business-manager' ),
		'ME' => __( 'Montenegro', 'business-manager' ),
		'MS' => __( 'Montserrat', 'business-manager' ),
		'MA' => __( 'Morocco', 'business-manager' ),
		'MZ' => __( 'Mozambique', 'business-manager' ),
		'MM' => __( 'Myanmar [Burma]', 'business-manager' ),
		'NA' => __( 'Namibia', 'business-manager' ),
		'NR' => __( 'Nauru', 'business-manager' ),
		'NP' => __( 'Nepal', 'business-manager' ),
		'NL' => __( 'Netherlands', 'business-manager' ),
		'AN' => __( 'Netherlands Antilles', 'business-manager' ),
		'NT' => __( 'Neutral Zone', 'business-manager' ),
		'NC' => __( 'New Caledonia', 'business-manager' ),
		'NZ' => __( 'New Zealand', 'business-manager' ),
		'NI' => __( 'Nicaragua', 'business-manager' ),
		'NE' => __( 'Niger', 'business-manager' ),
		'NG' => __( 'Nigeria', 'business-manager' ),
		'NU' => __( 'Niue', 'business-manager' ),
		'NF' => __( 'Norfolk Island', 'business-manager' ),
		'KP' => __( 'North Korea', 'business-manager' ),
		'VD' => __( 'North Vietnam', 'business-manager' ),
		'MP' => __( 'Northern Mariana Islands', 'business-manager' ),
		'NO' => __( 'Norway', 'business-manager' ),
		'OM' => __( 'Oman', 'business-manager' ),
		'PC' => __( 'Pacific Islands Trust Territory', 'business-manager' ),
		'PK' => __( 'Pakistan', 'business-manager' ),
		'PW' => __( 'Palau', 'business-manager' ),
		'PS' => __( 'Palestinian Territories', 'business-manager' ),
		'PA' => __( 'Panama', 'business-manager' ),
		'PZ' => __( 'Panama Canal Zone', 'business-manager' ),
		'PG' => __( 'Papua New Guinea', 'business-manager' ),
		'PY' => __( 'Paraguay', 'business-manager' ),
		'YD' => __( "People's Democratic Republic of Yemen", 'business-manager' ),
		'PE' => __( 'Peru', 'business-manager' ),
		'PH' => __( 'Philippines', 'business-manager' ),
		'PN' => __( 'Pitcairn Islands', 'business-manager' ),
		'PL' => __( 'Poland', 'business-manager' ),
		'PT' => __( 'Portugal', 'business-manager' ),
		'PR' => __( 'Puerto Rico', 'business-manager' ),
		'QA' => __( 'Qatar', 'business-manager' ),
		'RO' => __( 'Romania', 'business-manager' ),
		'RU' => __( 'Russia', 'business-manager' ),
		'RW' => __( 'Rwanda', 'business-manager' ),
		'RE' => __( 'Réunion', 'business-manager' ),
		'BL' => __( 'Saint Barthélemy', 'business-manager' ),
		'SH' => __( 'Saint Helena', 'business-manager' ),
		'KN' => __( 'Saint Kitts and Nevis', 'business-manager' ),
		'LC' => __( 'Saint Lucia', 'business-manager' ),
		'MF' => __( 'Saint Martin', 'business-manager' ),
		'PM' => __( 'Saint Pierre and Miquelon', 'business-manager' ),
		'VC' => __( 'Saint Vincent and the Grenadines', 'business-manager' ),
		'WS' => __( 'Samoa', 'business-manager' ),
		'SM' => __( 'San Marino', 'business-manager' ),
		'SA' => __( 'Saudi Arabia', 'business-manager' ),
		'SN' => __( 'Senegal', 'business-manager' ),
		'RS' => __( 'Serbia', 'business-manager' ),
		'CS' => __( 'Serbia and Montenegro', 'business-manager' ),
		'SC' => __( 'Seychelles', 'business-manager' ),
		'SL' => __( 'Sierra Leone', 'business-manager' ),
		'SG' => __( 'Singapore', 'business-manager' ),
		'SK' => __( 'Slovakia', 'business-manager' ),
		'SI' => __( 'Slovenia', 'business-manager' ),
		'SB' => __( 'Solomon Islands', 'business-manager' ),
		'SO' => __( 'Somalia', 'business-manager' ),
		'ZA' => __( 'South Africa', 'business-manager' ),
		'GS' => __( 'South Georgia and the South Sandwich Islands', 'business-manager' ),
		'KR' => __( 'South Korea', 'business-manager' ),
		'ES' => __( 'Spain', 'business-manager' ),
		'LK' => __( 'Sri Lanka', 'business-manager' ),
		'SD' => __( 'Sudan', 'business-manager' ),
		'SR' => __( 'Suriname', 'business-manager' ),
		'SJ' => __( 'Svalbard and Jan Mayen', 'business-manager' ),
		'SZ' => __( 'Swaziland', 'business-manager' ),
		'SE' => __( 'Sweden', 'business-manager' ),
		'CH' => __( 'Switzerland', 'business-manager' ),
		'SY' => __( 'Syria', 'business-manager' ),
		'ST' => __( 'São Tomé and Príncipe', 'business-manager' ),
		'TW' => __( 'Taiwan', 'business-manager' ),
		'TJ' => __( 'Tajikistan', 'business-manager' ),
		'TZ' => __( 'Tanzania', 'business-manager' ),
		'TH' => __( 'Thailand', 'business-manager' ),
		'TL' => __( 'Timor-Leste', 'business-manager' ),
		'TG' => __( 'Togo', 'business-manager' ),
		'TK' => __( 'Tokelau', 'business-manager' ),
		'TO' => __( 'Tonga', 'business-manager' ),
		'TT' => __( 'Trinidad and Tobago', 'business-manager' ),
		'TN' => __( 'Tunisia', 'business-manager' ),
		'TR' => __( 'Turkey', 'business-manager' ),
		'TM' => __( 'Turkmenistan', 'business-manager' ),
		'TC' => __( 'Turks and Caicos Islands', 'business-manager' ),
		'TV' => __( 'Tuvalu', 'business-manager' ),
		'UM' => __( 'U.S. Minor Outlying Islands', 'business-manager' ),
		'PU' => __( 'U.S. Miscellaneous Pacific Islands', 'business-manager' ),
		'VI' => __( 'U.S. Virgin Islands', 'business-manager' ),
		'UG' => __( 'Uganda', 'business-manager' ),
		'UA' => __( 'Ukraine', 'business-manager' ),
		'SU' => __( 'Union of Soviet Socialist Republics', 'business-manager' ),
		'AE' => __( 'United Arab Emirates', 'business-manager' ),
		'GB' => __( 'United Kingdom', 'business-manager' ),
		'US' => __( 'United States', 'business-manager' ),
		'ZZ' => __( 'Unknown or Invalid Region', 'business-manager' ),
		'UY' => __( 'Uruguay', 'business-manager' ),
		'UZ' => __( 'Uzbekistan', 'business-manager' ),
		'VU' => __( 'Vanuatu', 'business-manager' ),
		'VA' => __( 'Vatican City', 'business-manager' ),
		'VE' => __( 'Venezuela', 'business-manager' ),
		'VN' => __( 'Vietnam', 'business-manager' ),
		'WK' => __( 'Wake Island', 'business-manager' ),
		'WF' => __( 'Wallis and Futuna', 'business-manager' ),
		'EH' => __( 'Western Sahara', 'business-manager' ),
		'YE' => __( 'Yemen', 'business-manager' ),
		'ZM' => __( 'Zambia', 'business-manager' ),
		'ZW' => __( 'Zimbabwe', 'business-manager' ),
		'AX' => __( 'Åland Islands', 'business-manager' ),
	];
}

/**
 * Returns all post types exists in business manager.
 */
function business_manager_post_types() {
	$post_types = get_post_types( [], 'objects' );

	$bm_post_types = [];
	$ignore_cpts   = [
		'bm-custom-field',
		'bm-custom-tabs',
		'bm-email',
	];

	foreach ( $post_types as $slug => $post_type ) {
		if ( in_array( $slug, $ignore_cpts, true ) ) {
			continue;
		}
		if ( 0 === strpos( $slug, 'bm-' ) ) {
			$bm_post_types[ $slug ] = $post_type->label;
		}
	}

	return $bm_post_types;
}

/**
 * Retrieve the metabox IDs associated with a specific custom post type.
 *
 * @param string $post_type The custom post type slug for which to retrieve metabox IDs.
 * @return array An array of metabox IDs associated with the specified custom post type.
 */
function business_manager_cpt_metabox_ids( $post_type ) {
	$metabox_ids = [
		'bm-employee' => [
			'_bm_employee_details_box' => __( 'Employee - Details', 'business-manager' ),
			'_bm_employee_files_box'   => __( 'Employee - Files & Documents', 'business-manager' ),
			'_bm_employee_notes_box'   => __( 'Employee - Notes', 'business-manager' ),
		],
		'bm-review'   => [
			'_bm_review_details_box'   => __( 'Review - Details', 'business-manager' ),
			'_bm_review_notes_box'     => __( 'Review - Notes', 'business-manager' ),
			'_bm_review_file_list_box' => __( 'Review - Files', 'business-manager' ),
			'_bm_review_ratings_box'   => __( 'Review - Ratings', 'business-manager' ),
			'_bm_review_summary_box'   => __( 'Review - Summary', 'business-manager' ),
			'_bm_review_goals_box'     => __( 'Review - Goals', 'business-manager' ),
		],
		'bm-leave'    => [
			'_bm_leave_details_box' => __( 'Leave - Details', 'business-manager' ),
		],
		'bm-project'  => [
			'_bm_project_settings_box' => __( 'Project - Settings', 'business-manager' ),
			'_bm_project_notes_box'    => __( 'Project - Notes', 'business-manager' ),
			'_bm_project_timeline_box' => __( 'Project - Timeline', 'business-manager' ),
			'_bm_project_tasks_box'    => __( 'Project - Tasks', 'business-manager' ),
			'_bm_project_files_box'    => __( 'Project - Files & Documents', 'business-manager' ),
		],
		'bm-client'   => [
			'_bm_client_details_box' => __( 'Client - Details', 'business-manager' ),
			'_bm_client_notes_box'   => __( 'Client - Notes', 'business-manager' ),
			'_bm_client_files_box'   => __( 'Client - Files & Documents', 'business-manager' ),
		],
		'bm-document' => [
			'_bm_document_file_list_box' => __( 'Document - Versions', 'business-manager' ),
			'_bm_document_notes_box'     => __( 'Document - Notes', 'business-manager' ),
		],
	];

	$metabox = apply_filters( 'business_manager_custom_field_metabox_ids', $metabox_ids );

	return $metabox[ $post_type ] ?? [];
}

/**
 * Retrieve the tabs associated with a specific metabox in the business manager.
 *
 * @param string $metabox_id The ID of the metabox for which to retrieve tabs.
 * @return array An array of tabs associated with the specified metabox.
 */
function business_manager_metabox_tabs( $metabox_id ) {
	$metabox_tabs = [
		// Employee CPT.
		'_bm_employee_details_box'   => [
			'personal'   => __( 'Personal Tab', 'business-manager' ),
			'employment' => __( 'Employment Tab', 'business-manager' ),
		],
		'_bm_employee_files_box'     => [
			'general'      => __( 'General Tab', 'business-manager' ),
			'contracts'    => __( 'Contract Tab', 'business-manager' ),
			'application'  => __( 'Application Tab', 'business-manager' ),
			'training'     => __( 'Training Tab', 'business-manager' ),
			'disciplinary' => __( 'Disciplinary Tab', 'business-manager' ),
		],
		'_bm_employee_notes_box'     => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		// Leave CPT.
		'_bm_leave_details_box'      => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		// Reviews CPT.
		'_bm_review_details_box'     => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_review_ratings_box'     => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_review_summary_box'     => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_review_goals_box'       => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_review_file_list_box'   => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_review_notes_box'       => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		// Projects CPT.
		'_bm_project_settings_box'   => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_project_timeline_box'   => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_project_files_box'      => [
			'general'   => __( 'General Tab', 'business-manager' ),
			'contracts' => __( 'Contract Tab', 'business-manager' ),
		],
		'_bm_project_tasks_box'      => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_project_notes_box'      => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		// Clients CPT.
		'_bm_client_details_box'     => [
			'main'  => __( 'Main Tab', 'business-manager' ),
			'staff' => __( 'Staff Tab', 'business-manager' ),
		],
		'_bm_client_files_box'       => [
			'general'   => __( 'General Tab', 'business-manager' ),
			'contracts' => __( 'Contract Tab', 'business-manager' ),
		],
		'_bm_client_notes_box'       => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		// Documents CPT.
		'_bm_document_file_list_box' => [
			'general' => __( 'General tab', 'business-manager' ),
		],
		'_bm_document_notes_box'     => [
			'general' => __( 'General tab', 'business-manager' ),
		],
	];

	$metabox_tabs = apply_filters( "business_manager_metabox_id_tabs_{$metabox_id}", $metabox_tabs, $metabox_id );

	// If there is only one tab, rename the "general" label to "None."
	// This indicates that the metabox has no additional tabs.

	if ( isset( $metabox_tabs[ $metabox_id ] ) && count( $metabox_tabs[ $metabox_id ] ) === 1 ) {
		$metabox_tabs[ $metabox_id ]['general'] = 'None';
		return $metabox_tabs[ $metabox_id ];
	} elseif ( count( $metabox_tabs ) === 1 ) {
		$metabox_tabs['general'] = 'None';
		return $metabox_tabs;
	}

	return $metabox_tabs[ $metabox_id ] ?? $metabox_tabs;
}

/**
 * Checks if a Business Manager addon with the given slug is active.
 *
 * @param string $slug The slug of the Business Manager addon.
 * @return bool True if the addon is active, false otherwise.
 */
function business_manager_addon_is_active( $slug ) {
	$plugins = get_option( 'active_plugins', [] );

    foreach ( $plugins as $plugin ) {
        if ( false !== strpos( $plugin, $slug ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Generates HTML for the "Add Contractor" button in Business Manager.
 *
 * @return string The HTML markup for the "Add Contractor" button.
 */
function bm_add_contractor_btn() {
	$is_bm_contractors_active = business_manager_addon_is_active( 'business-manager-contractors' );
	$add_new_contractor_link  = $is_bm_contractors_active ? admin_url( 'post-new.php?post_type=bm-contractor' ) : '#';
	$button_class             = $is_bm_contractors_active ? '' : 'bm-add-contractors-btn disabled button';
	$button                   = '<a href="' . esc_url( $add_new_contractor_link ) . '" class="bm-contractors-action-button page-title-action ' . esc_attr( $button_class ) . '">' . __( 'New Contractor', 'business-manager' ) . '</a>';

	return $button;
}


if ( ! function_exists( 'bm_ajax_return' ) ) {
	/**
	 * Returns a JSON response for AJAX requests.
	 *
	 * @param string $msg    The message to send.
	 * @param string $status The status code.
	 * @param mixed  $data   Any data to return.
	 * @param int    $edit   Whether editing an existing task.
	 */
	function bm_ajax_return( $msg, $status, $data = null, $edit = 0 ) {
		$return = [
			'message' => $msg,
			'status'  => $status,
			'data'    => $data,
			'edit'    => $edit,
		];

		wp_send_json( $return );
	}
}
