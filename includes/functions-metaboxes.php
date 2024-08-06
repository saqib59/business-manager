<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Set the date format for the metabox datepickers
 * Be wary of changing this. PHP to JS conversion is not great and may provide unexpected results.
 *
 * @return string date format
 */
function business_manager_datepicker_format() {
    $return = 'Y-m-d';
    return apply_filters( 'business_manager_datepicker_format', $return );
}

/**
 * Returns dropdown options for selecting employment types.
 *
 * @param string $field The field name for the dropdown menu.
 * @return array
 */
function business_manager_dropdown_employment_type( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_employment_type',
        [
			'Full Time' => __( 'Full Time', 'business-manager' ),
			'Part Time' => __( 'Part Time', 'business-manager' ),
			'Casual'    => __( 'Casual', 'business-manager' ),
			'Vacation'  => __( 'Vacation', 'business-manager' ),
			'Freelance' => __( 'Freelance', 'business-manager' ),
		]
    );
    return $options;
}
/**
 * Gets ratings and displays them as options.
 *
 * @return array An array of options
 */
function business_manager_dropdown_ratings() {
    $options = apply_filters(
        'business_manager_dropdown_ratings',
        [
			'5' => __( '5 - Excellent', 'business-manager' ),
			'4' => __( '4 - Very Good', 'business-manager' ),
			'3' => __( '3 - Good', 'business-manager' ),
			'2' => __( '2 - Fair', 'business-manager' ),
			'1' => __( '1 - Poor', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Returns dropdown options for selecting employment statuses.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_employment_status( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_employment_status',
        [
			'Active'     => __( 'Active', 'business-manager' ),
			'Retrenched' => __( 'Retrenched', 'business-manager' ),
			'Dismissed'  => __( 'Dismissed', 'business-manager' ),
			'Resigned'   => __( 'Resigned', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Returns dropdown options for marital statuses.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_marital_status( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_marital_status',
        [
			''         => '',
			'Single'   => __( 'Single', 'business-manager' ),
			'Married'  => __( 'Married', 'business-manager' ),
			'Widowed'  => __( 'Widowed', 'business-manager' ),
			'Divorced' => __( 'Divorced', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Returns dropdown options for employee user accounts.
 *
 * This function creates a dropdown menu containing employee user accounts based on the provided field.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_employee_user_accounts( $field ) {
    global $post;

    $bm_employee_user_accounts = [];
    $options_employees[0]      = '';

    // Find all Business Manager Employee Users.
    $args                   = [
        'role'    => 'bm_employee',
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ];
    $employee_user_accounts = get_users( $args );

    // Find all Business Manager Employees with User Accounts assigned.
    $bm_employees = get_posts(
        [
            'post_type'      => 'bm-employee',
            'meta_key'       => '_bm_employee_user_id',
            'exclude'        => [ $post->ID ],
            'posts_per_page' => -1,
        ]
    );

    foreach ( $bm_employees as $e ) {
        $bm_employee_user_accounts[] = get_post_meta( $e->ID, '_bm_employee_user_id', true );
    }

    // Strip out currently used Business Manager Employee Users, unless it matches the current post ID.
    foreach ( $employee_user_accounts as $eua ) {
        if ( ! in_array( $eua->ID, $bm_employee_user_accounts ) ) {
            $options_employees[ $eua->ID ] = $eua->display_name . ' (' . $eua->user_login . ')';
        }
    }

    $options = apply_filters( 'business_manager_dropdown_employee_user_accounts', $options_employees );

    return $options;
}

/**
 * Returns dropdown menu for leave types.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_leave_type( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_leave_type',
        [
			'Annual'      => __( 'Annual Leave', 'business-manager' ),
			'Personal'    => __( 'Personal Leave', 'business-manager' ),
			'Carers'      => __( 'Carers Leave', 'business-manager' ),
			'Bereavement' => __( 'Bereavement Leave', 'business-manager' ),
			'Maternity'   => __( 'Maternity Leave', 'business-manager' ),
			'Paternity'   => __( 'Paternity Leave', 'business-manager' ),
			'Sick'        => __( 'Sick Leave', 'business-manager' ),
			'Unpaid'      => __( 'Unpaid Leave', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Returns dropdown menu for leave statuses.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_leave_status( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_leave_status',
        [
			'Requested' => __( 'Requested', 'business-manager' ),
			'Approved'  => __( 'Approved', 'business-manager' ),
			'Denied'    => __( 'Denied', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Returns dropdown menu for percentage values.
 *
 * @param string $field The field name | object for the dropdown menu.
 * @return array An array of options
 */
function business_manager_dropdown_percentages( $field ) {
    $options = apply_filters(
        'business_manager_dropdown_percentages',
        [
			'0'   => __( '0%', 'business-manager' ),
			'5'   => __( '5%', 'business-manager' ),
			'10'  => __( '10%', 'business-manager' ),
			'15'  => __( '15%', 'business-manager' ),
			'20'  => __( '20%', 'business-manager' ),
			'25'  => __( '25%', 'business-manager' ),
			'30'  => __( '30%', 'business-manager' ),
			'35'  => __( '35%', 'business-manager' ),
			'40'  => __( '40%', 'business-manager' ),
			'45'  => __( '45%', 'business-manager' ),
			'50'  => __( '50%', 'business-manager' ),
			'55'  => __( '55%', 'business-manager' ),
			'60'  => __( '60%', 'business-manager' ),
			'65'  => __( '65%', 'business-manager' ),
			'70'  => __( '70%', 'business-manager' ),
			'75'  => __( '75%', 'business-manager' ),
			'80'  => __( '80%', 'business-manager' ),
			'85'  => __( '85%', 'business-manager' ),
			'90'  => __( '90%', 'business-manager' ),
			'95'  => __( '95%', 'business-manager' ),
			'99'  => __( '99%', 'business-manager' ),
			'100' => __( '100%', 'business-manager' ),
		]
    );
    return $options;
}

/**
 * Gets a number of posts and displays them as options.
 *
 * @param  array $query_args Optional. Overrides defaults.
 * @return array             An array of options that matches the CMB2 options array (id and post title)
 */
function business_manager_get_post_options( $query_args ) {
    if ( ! isset( $query_args['post_type'] ) ) {
        return;
    }

    $args = wp_parse_args(
        $query_args,
        [
			'numberposts' => -1,
			'post_status' => 'publish',
		]
    );

    $posts = get_posts( $args );

    $post_options = [];
    if ( $posts ) {
        $post_options[] = '';
        foreach ( $posts as $post ) {
            $post_options[ $post->ID ] = $post->post_title;
        }
    }

    return $post_options;
}

/**
 * Gets posts for locations and displays them as options.
 *
 * @return array An array of options that matches the CMB2 options array
 */
function business_manager_dropdown_get_employee() {
    $employees = business_manager_get_post_options( [ 'post_type' => 'bm-employee' ] );
    foreach ( $employees as $id => $title ) {
        $status = get_post_meta( $id, '_bm_employee_status', true );
        if ( $status && in_array( $status, [ 'Retrenched', 'Dismissed', 'Resigned' ] ) ) {
            $employees[ $id ] = $title . ' (' . $status . ')';
            business_manager_move_item_to_bottom( $employees, $id );
        }
    }
    unset( $employees[0] );
    return $employees;
}

/**
 * Moves an item to the bottom of an array.
 *
 * @param array  $array The array in which the item is to be moved.
 * @param string $key   The key of the item to be moved.
 * @return void
 */
function business_manager_move_item_to_bottom( &$array, $key ) {
    $value = $array[ $key ];
    unset( $array[ $key ] );
    $array[ $key ] = $value;
}

/**
 * Gets posts for locations and displays them as options.
 *
 * @return array An array of options that matches the CMB2 options array
 */
function business_manager_dropdown_get_client() {
    return business_manager_get_post_options( [ 'post_type' => 'bm-client' ] );
}

/**
 * Outputs data for the upcoming leave metabox within an employee.
 *
 * @return html
 */
function business_manager_upcoming_leave_html_metabox() {
    $employee = new Business_Manager_Employee();
    return $employee->upcoming_leave_html_metabox();
}

/**
 * Outputs data for the latest document metabox.
 *
 * @return html
 */
function business_manager_latest_document_html_metabox() {
    $employee = new Business_Manager_Document();
    return $employee->latest_document_html_metabox();
}

/**
 * Outputs data for the project tasks metabox.
 *
 * @return html
 */
function business_manager_project_tasks_html_metabox() {
    $employee = new Business_Manager_Project();
    return $employee->tasks_html_metabox();
}

/**
 * Displays an upsell section for extensions.
 *
 * @param WP_Post $post The WordPress post object.
 * @param array   $args Additional arguments for customizing the upsell section.
 * @return void
 */
function business_manager_extension_upsell( $post, $args ) {
    $bm_post_types    = $args['args']['bm_post_types'];
    $post_type        = $args['args']['post_type'];
    $extension_upsell = '';

    // Asset Manager.
    if ( ! is_plugin_active( 'business-manager-asset-manager/business-manager-asset-manager.php' ) && in_array( $post->post_type, [ 'bm-client' ] ) ) {
        $extension_upsell .= '<div>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/asset-manager/" class="">';
        $extension_upsell .= '<img src="' . plugin_dir_url( __DIR__ ) . '/assets/images/extensions/asset-manager.png' . '">';
        $extension_upsell .= '</a>';
        $extension_upsell .= '<h1> ' . __( 'Asset Manager', 'business-manager' ) . ' </h1>';
        $extension_upsell .= '<p>';
        $extension_upsell .= sprintf( __( 'Track your company\'s equipment with the Asset Manager extension for Business Manager.', 'business-manager' ), $post_type->labels->singular_name );
        $extension_upsell .= '</p>';

        $extension_upsell .= '<p>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/asset-manager/">';
        $extension_upsell .= __( 'Learn more at bzmngr.com', 'business-manager' );
        $extension_upsell .= '</a>';
        $extension_upsell .= '</p>';
        $extension_upsell .= '</div>';
    }

    // Contractors.
    if ( ! is_plugin_active( 'business-manager-contractors/business-manager-contractors.php' ) && in_array( $post->post_type, [ 'bm-employee' ] ) ) {
        $extension_upsell .= '<div>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/contractors/">';
        $extension_upsell .= '<img src="' . plugin_dir_url( __DIR__ ) . '/assets/images/extensions/contractors.png' . '">';
        $extension_upsell .= '</a>';

        $extension_upsell .= '<h1> ' . __( 'Contractors', 'business-manager' ) . ' </h1>';

        $extension_upsell .= '<p>';
        $extension_upsell .= sprintf( __( 'Does your company hire contractors? Keep their records separate from employees with the Contractors extension for Business Manager.', 'business-manager' ), $post_type->labels->singular_name );
        $extension_upsell .= '</p>';

        $extension_upsell .= '<p>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/contractors/">';
        $extension_upsell .= __( 'Learn more at bzmngr.com', 'business-manager' );
        $extension_upsell .= '</a>';
        $extension_upsell .= '</p>';
        $extension_upsell .= '</div>';
    }

    // Custom Fields.
    if ( ! is_plugin_active( 'business-manager-custom-fields/business-manager-custom-fields.php' ) && in_array( $post->post_type, $bm_post_types ) ) {
        $extension_upsell .= '<div>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/custom-fields-extension/">';
        $extension_upsell .= '<img src="' . plugin_dir_url( __DIR__ ) . '/assets/images/extensions/custom-fields.png' . '">';
        $extension_upsell .= '</a>';

        $extension_upsell .= '<h1> ' . __( 'Custom Fields', 'business-manager' ) . ' </h1>';

        $extension_upsell .= '<p>';
        // translators: %s represents the singular name of the post type.
        $extension_upsell .= sprintf( __( 'Add your own fields to any %s record with the Custom Fields extension for Business Manager.', 'business-manager' ), $post_type->labels->singular_name );
        $extension_upsell .= '</p>';

        $extension_upsell .= '<p>';
        $extension_upsell .= '<a target="_blank" href="https://bzmngr.com/custom-fields-extension/">';
        $extension_upsell .= __( 'Learn more at bzmngr.com', 'business-manager' );
        $extension_upsell .= '</a>';
        $extension_upsell .= '</p>';
        $extension_upsell .= '</div>';
    }

    echo wp_kses_post( $extension_upsell );
}
/**
 * Outputs data for the assets associated within an employee.
 *
 * @return html
 */
function business_manager_assets_html_metabox() {
    $employee = new Business_Manager_Employee();
    return $employee->assets_html_metabox();
}

/**
 * Retrieves the all terms in the 'bm-department' taxonomy.
 *
 * @return array An array of term IDs with names belonging to the 'bm-department' taxonomy.
 */
function business_manager_dropdown_get_departments() {
    $departments['all'] = __( 'Send to All employees', 'business-manager' );

    $get_departments = new WP_Term_Query(
        [
			'taxonomy'   => 'bm-department',
			'fields'     => 'all',
			'hide_empty' => false,
		]
    );

    if ( ! empty( $get_departments->terms ) ) {
        foreach ( $get_departments->terms as $department ) {
            $departments[ $department->term_id ] = $department->name;
        }
    }

    $departments = apply_filters( 'business_manager_dropdown_departments', $departments );

    return $departments;
}
