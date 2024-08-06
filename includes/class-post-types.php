<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The main class.
 *
 * @since 1.0.0
 */
class Business_Manager_Post_Types {
    /**
     * Main constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Hook into actions & filters.
        $this->hooks();
    }

    /**
     * Hook in to actions & filters.
     *
     * @since 1.0.0
     */
    public function hooks() {
        // post types.
        add_action( 'init', [ $this, 'register_employee' ] );
        add_action( 'init', [ $this, 'register_project' ] );
        add_action( 'init', [ $this, 'register_client' ] );
        add_action( 'init', [ $this, 'register_review' ] );
        add_action( 'init', [ $this, 'register_leave' ] );
        add_action( 'init', [ $this, 'register_document' ] );
        add_action( 'init', [ $this, 'register_email_notification' ] );
        add_action( 'init', [ $this, 'register_announcements' ] );

        // taxonomies.
        add_action( 'init', [ $this, 'register_department_taxonomy' ] );
        add_action( 'init', [ $this, 'register_project_type_taxonomy' ] );
        add_action( 'init', [ $this, 'register_project_status_taxonomy' ] );
        add_action( 'init', [ $this, 'register_client_status_taxonomy' ] );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_employee() {
        $labels = apply_filters(
            'business_manager_employee_labels',
            [
                // translators: %2$s represents the post type name placeholder.
				'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the singular name placeholder for the post type.
				'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a specific item.
				'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a specific item.
				'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a specific item.
				'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a specific item.
				'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
				'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a specific item.
				'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
				'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for a specific item.
				'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for a specific item.
				'items_list'            => __( '%2$s list', 'business-manager' ),
			]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_employee_single(), business_manager_label_employee_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ '' ],
        ];

        register_post_type( 'bm-employee', apply_filters( 'business_manager_employee_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_project() {

        $labels = apply_filters(
            'business_manager_project_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_project_single(), business_manager_label_project_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title' ],
        ];

        register_post_type( 'bm-project', apply_filters( 'business_manager_project_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_client() {

        $labels = apply_filters(
            'business_manager_client_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_client_single(), business_manager_label_client_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title' ],
        ];

        register_post_type( 'bm-client', apply_filters( 'business_manager_client_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_review() {

        $labels = apply_filters(
            'business_manager_review_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_review_single(), business_manager_label_review_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ '' ],
        ];

        register_post_type( 'bm-review', apply_filters( 'business_manager_review_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_leave() {

        $labels = apply_filters(
            'business_manager_leave_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_leave_single(), business_manager_label_leave_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ '' ],
        ];

        register_post_type( 'bm-leave', apply_filters( 'business_manager_leave_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_document() {

        $labels = apply_filters(
            'business_manager_document_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, business_manager_label_document_single(), business_manager_label_document_plural() );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title' ],
        ];

        register_post_type( 'bm-document', apply_filters( 'business_manager_document_post_type_args', $args ) );
    }


    /**
     * Registers and sets up "email notification" custom post types.
     *
     * @since 1.0
     * @return void
     */
    public function register_email_notification() {

        $labels = apply_filters(
            'business_manager_email_notification_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, 'Email Notification', 'Email Notifications' );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor' ],
        ];

        register_post_type( 'bm-email', apply_filters( 'business_manager_email_notification_post_type_args', $args ) );
    }


    public function register_announcements() {

        $labels = apply_filters(
            'business_manager_announcements_labels',
            [
                // translators: %2$s represents the post type general name.
                'name'                  => _x( '%2$s', 'post type general name', 'business-manager' ),
                // translators: %1$s represents the post type singular name.
                'singular_name'         => _x( '%1$s', 'post type singular name', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'add_new'               => __( 'New %1s', 'business-manager' ),
                // translators: %1$s represents a placeholder for adding a new item.
                'add_new_item'          => __( 'Add New %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for editing an item.
                'edit_item'             => __( 'Edit %1$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for a new item.
                'new_item'              => __( 'New %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for all items.
                'all_items'             => __( '%2$s', 'business-manager' ),
                // translators: %1$s represents a placeholder for viewing an item.
                'view_item'             => __( 'View %1$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for searching items.
                'search_items'          => __( 'Search %2$s', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found.
                'not_found'             => __( 'No %2$s found', 'business-manager' ),
                // translators: %2$s represents a placeholder for items not found in Trash.
                'not_found_in_trash'    => __( 'No %2$s found in Trash', 'business-manager' ),
                'parent_item_colon'     => '',
                // translators: %2$s represents a placeholder for the admin menu.
                'menu_name'             => _x( '%2$s', 'admin menu', 'business-manager' ),
                // translators: %2$s represents a placeholder for filtering items list.
                'filter_items_list'     => __( 'Filter %2$s list', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list navigation.
                'items_list_navigation' => __( '%2$s list navigation', 'business-manager' ),
                // translators: %2$s represents a placeholder for items list.
                'items_list'            => __( '%2$s list', 'business-manager' ),
            ]
        );

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, __( 'Announcement', 'business-manager' ), __( 'Announcements', 'business-manager' ) );
        }

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'show_in_rest'        => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // we are using custom add_submenu_page.
            'query_var'           => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => false,
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor' ],
        ];

        register_post_type( 'bm-announcement', apply_filters( 'business_manager_announcements_post_type_args', $args ) );
    }

    /**
     * Registers and sets up the taxonomy.
     *
     * @since 1.0
     * @return void
     */
    public function register_department_taxonomy() {

        $labels = [
            // translators: %2s represents the taxonomy general name.
            'name'              => _x( '%2s', 'taxonomy general name', 'business-manager' ),
            // translators: %1s represents the taxonomy singular name.
            'singular_name'     => _x( '%1s', 'taxonomy singular name', 'business-manager' ),
            // translators: %2s represents a placeholder for searching items.
            'search_items'      => __( 'Search %2s', 'business-manager' ),
            // translators: %2s represents a placeholder for all items.
            'all_items'         => __( 'All %2s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item.
            'parent_item'       => __( 'Parent %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item with colon.
            'parent_item_colon' => __( 'Parent %1s:', 'business-manager' ),
            // translators: %1s represents a placeholder for editing an item.
            'edit_item'         => __( 'Edit %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for updating an item.
            'update_item'       => __( 'Update %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for adding a new item.
            'add_new_item'      => __( 'Add New %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for a new item name.
            'new_item_name'     => __( 'New %1s Name', 'business-manager' ),
            // translators: %1s represents a placeholder for the menu name.
            'menu_name'         => __( '%1s', 'business-manager' ),
        ];

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, __( 'Department', 'business-manager' ), __( 'Departments', 'business-manager' ) );
        }

        $args = [
            'hierarchical'        => true,
            'labels'              => $labels,
            'show_ui'             => true,
            'show_admin_column'   => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => false,
            'public'              => false,
            'show_in_rest'        => false,
            'query_var'           => true,
            'show_in_nav_menus'   => false,
            'rewrite'             => false,
        ];
        register_taxonomy(
            'bm-department',
            apply_filters( 'business_manager_department_taxonomy_post_types', [ 'bm-employee', 'bm-document' ] ),
            $args
        );
    }


    /**
     * Registers and sets up the taxonomy for project status.
     *
     * @since 1.0
     * @return void
     */
    public function register_project_status_taxonomy() {

        $labels = [
            // translators: %2s represents the taxonomy general name.
            'name'              => _x( '%2s', 'taxonomy general name', 'business-manager' ),
            // translators: %1s represents the taxonomy singular name.
            'singular_name'     => _x( '%1s', 'taxonomy singular name', 'business-manager' ),
            // translators: %2s represents a placeholder for searching items.
            'search_items'      => __( 'Search %2s', 'business-manager' ),
            // translators: %2s represents a placeholder for all items.
            'all_items'         => __( 'All %2s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item.
            'parent_item'       => __( 'Parent %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item with colon.
            'parent_item_colon' => __( 'Parent %1s:', 'business-manager' ),
            // translators: %1s represents a placeholder for editing an item.
            'edit_item'         => __( 'Edit %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for updating an item.
            'update_item'       => __( 'Update %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for adding a new item.
            'add_new_item'      => __( 'Add New %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for a new item name.
            'new_item_name'     => __( 'New %1s Name', 'business-manager' ),
            // translators: %1s represents a placeholder for the menu name.
            'menu_name'         => __( '%1s', 'business-manager' ),
        ];

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, __( 'Status', 'business-manager' ), __( 'Statuses', 'business-manager' ) );
        }

        $args = [
            'hierarchical'       => true,
            'labels'             => $labels,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'query_var'          => true,
            'show_in_nav_menus'  => false,
            'publicly_queryable' => false,
            'rewrite'            => false,
        ];

        register_taxonomy(
            'bm-status',
            [ 'bm-project' ],
            $args
        );
    }

    /**
     * Registers and sets up the taxonomy for project type.
     *
     * @since 1.0
     * @return void
     */
    public function register_project_type_taxonomy() {

        $labels = [
            // translators: %2s represents the taxonomy general name.
            'name'              => _x( '%2s', 'taxonomy general name', 'business-manager' ),
            // translators: %1s represents the taxonomy singular name.
            'singular_name'     => _x( '%1s', 'taxonomy singular name', 'business-manager' ),
            // translators: %2s represents a placeholder for searching items.
            'search_items'      => __( 'Search %2s', 'business-manager' ),
            // translators: %2s represents a placeholder for all items.
            'all_items'         => __( 'All %2s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item.
            'parent_item'       => __( 'Parent %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item with colon.
            'parent_item_colon' => __( 'Parent %1s:', 'business-manager' ),
            // translators: %1s represents a placeholder for editing an item.
            'edit_item'         => __( 'Edit %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for updating an item.
            'update_item'       => __( 'Update %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for adding a new item.
            'add_new_item'      => __( 'Add New %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for a new item name.
            'new_item_name'     => __( 'New %1s Name', 'business-manager' ),
            // translators: %1s represents a placeholder for the menu name.
            'menu_name'         => __( '%1s', 'business-manager' ),
        ];

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, __( 'Type', 'business-manager' ), __( 'Types', 'business-manager' ) );
        }

        $args = [
            'hierarchical'       => true,
            'labels'             => $labels,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'query_var'          => true,
            'show_in_nav_menus'  => false,
            'publicly_queryable' => false,
            'rewrite'            => false,
        ];

        register_taxonomy(
            'bm-type',
            [ 'bm-project' ],
            $args
        );
    }

    /**
     * Registers and sets up the taxonomy for client status.
     *
     * @since 1.4.2
     * @return void
     */
    public function register_client_status_taxonomy() {

        $labels = [
            // translators: %2s represents the taxonomy general name.
            'name'              => _x( '%2s', 'taxonomy general name', 'business-manager' ),
            // translators: %1s represents the taxonomy singular name.
            'singular_name'     => _x( '%1s', 'taxonomy singular name', 'business-manager' ),
            // translators: %2s represents a placeholder for searching items.
            'search_items'      => __( 'Search %2s', 'business-manager' ),
            // translators: %2s represents a placeholder for all items.
            'all_items'         => __( 'All %2s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item.
            'parent_item'       => __( 'Parent %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for the parent item with colon.
            'parent_item_colon' => __( 'Parent %1s:', 'business-manager' ),
            // translators: %1s represents a placeholder for editing an item.
            'edit_item'         => __( 'Edit %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for updating an item.
            'update_item'       => __( 'Update %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for adding a new item.
            'add_new_item'      => __( 'Add New %1s', 'business-manager' ),
            // translators: %1s represents a placeholder for a new item name.
            'new_item_name'     => __( 'New %1s Name', 'business-manager' ),
            // translators: %1s represents a placeholder for the menu name.
            'menu_name'         => __( '%1s', 'business-manager' ),
        ];

        foreach ( $labels as $key => $value ) {
            $labels[ $key ] = sprintf( $value, __( 'Status', 'business-manager' ), __( 'Statuses', 'business-manager' ) );
        }

        $args = [
            'hierarchical'       => true,
            'labels'             => $labels,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'query_var'          => true,
            'show_in_nav_menus'  => false,
            'publicly_queryable' => false,
            'rewrite'            => false,
        ];

        register_taxonomy(
            'bm-status-client',
            [ 'bm-client' ],
            $args
        );
    }
}

return new Business_Manager_Post_Types();
