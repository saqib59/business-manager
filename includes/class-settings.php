<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Business_Manager_Settings')):

class Business_Manager_Settings
{
    private $settings_api;

    public function __construct()
    {
        $this->settings_api = new Business_Manager_Settings_API;
        add_action( 'admin_init', [ $this, 'admin_init' ] );
        add_action( 'in_admin_header', [ $this, 'add_settings_nav_to_pages' ] );
        add_action( 'wsa_form_bottom_business-manager-email-notification', [ $this, 'get_email_fields' ] );
    }

    public function admin_init()
    {
        //set the settings
        $this->settings_api->set_sections($this->get_settings_tabs());
        $this->settings_api->set_fields($this->get_settings_fields());

        //initialize settings
        $this->settings_api->admin_init();
    }

    function add_settings_nav_to_pages() {
        $current_screen = get_current_screen();

        if ( 'bm-email' === $current_screen->post_type ) {
            $this->settings_api->show_navigation( 'email' );
        }
    }

    public function get_settings_tabs()
    {
        $tabs = [
            [
                'id' => 'business-manager-general',
                'title' => __('General', 'business-manager'),
            ],
            [
                'id' => 'business-manager-employee',
                'title' => __('Employees', 'business-manager'),
            ],
            [
                'id' => 'business-manager-client',
                'title' => __('Clients', 'business-manager'),
            ],
            [
                'id' => 'business-manager-project',
                'title' => __('Projects', 'business-manager'),
            ],
            [
                'id' => 'business-manager-document',
                'title' => __('Documents', 'business-manager'),
            ],
            // [
            //     'id' => 'business-manager-email-notification',
            //     'title' => __('Email Notifications', 'business-manager'),
            //     //'url' => 'edit.php?post_type=bm-email',
            //     //'redirect' => true,
            // ],
            [
                'id' => 'business-manager-extensions',
                'title' => __('Extensions', 'business-manager'),
            ],
        ];
        return apply_filters('business_manager_settings_tabs', $tabs);
    }

    /**
     * Merge all the settings fields.
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
        return apply_filters('business_manager_settings_fields', array_merge(
            $this->get_general_fields(),
            $this->get_employee_fields(),
            $this->get_client_fields(),
            $this->get_project_fields(),
            $this->get_document_fields(),
            $this->get_extension_fields(),
        ));
    }

    /**
     * Returns all the general settings fields.
     *
     * @return array settings fields
     */
    public function get_general_fields()
    {
        $id = 'general';
        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'modules',
                    'label' => __('Disable Modules', 'business-manager'),
                    'desc' => __('Check any modules you would like to disable. Employees can not be disabled.', 'business-manager'),
                    'type' => 'multicheck',
                    'options' => [
                        'clients' => __('Clients', 'business-manager'),
                        'projects' => __('Projects', 'business-manager'),
                        'documents' => __('Documents', 'business-manager'),
                        'leave' => __('Leave', 'business-manager'),
                        'reviews' => __('Reviews', 'business-manager'),
                    ],
                ],

                [
                    'name' => 'break_1',
                    'label' => '',
                    'desc' => '<hr>',
                    'type' => 'html',
                ],

                [
                    'name' => 'business_name',
                    'label' => __('Business Name', 'business-manager'),
                    'desc' => __('The name of your business/company', 'business-manager'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'placeholder' => get_option('blogname'),
                ],

                [
                    'name' => 'business_address',
                    'label' => __('Business Address', 'business-manager'),
                    'desc' => __('The address of your business/company', 'business-manager'),
                    'type' => 'textarea',
                ],
                [
                    'name' => 'business_phone',
                    'label' => __('Business Phone', 'business-manager'),
                    'desc' => __('The phone number of your business/company', 'business-manager'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                [
                    'name' => 'business_email',
                    'label' => __('Business Email', 'business-manager'),
                    'desc' => __('The main email address of your business/company', 'business-manager'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'placeholder' => get_option('admin_email'),
                ],
                [
                    'name' => 'business_website',
                    'label' => __('Business Website', 'business-manager'),
                    'desc' => __('The website URL of your business/company', 'business-manager'),
                    'type' => 'text',
                    'sanitize_callback' => 'sanitize_text_field',
                    'placeholder' => get_option('home'),
                ],

                [
                    'name' => 'logo',
                    'label' => __('Logo', 'business-manager'),
                    'desc' => __('Your business logo', 'business-manager'),
                    'type' => 'file',
                    'default' => '',
                    'options' => [
                        'button_label' => __('Choose Image', 'business-manager'),
                    ],
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    /**
     * Returns all the settings fields.
     *
     * @return array settings fields
     */
    public function get_employee_fields()
    {
        $id = 'employee';
        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'singular',
                    'label' => __('Employee Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Employee', 'business-manager'),
                ],
                [
                    'name' => 'plural',
                    'label' => __('Employee Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Employees', 'business-manager'),
                ],
                [
                    'name' => 'leave_singular',
                    'label' => __('Leave Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Leave', 'business-manager'),
                ],
                [
                    'name' => 'leave_plural',
                    'label' => __('Leave Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Leave', 'business-manager'),
                ],
                [
                    'name' => 'review_singular',
                    'label' => __('Review Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Review', 'business-manager'),
                ],
                [
                    'name' => 'review_plural',
                    'label' => __('Review Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Reviews', 'business-manager'),
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    /**
     * Returns all the settings fields.
     *
     * @return array settings fields
     */
    public function get_client_fields()
    {
        $id = 'client';
        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'singular',
                    'label' => __('Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Client', 'business-manager'),
                ],
                [
                    'name' => 'plural',
                    'label' => __('Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Clients', 'business-manager'),
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    /**
     * Returns all the settings fields.
     *
     * @return array settings fields
     */
    public function get_project_fields()
    {
        $id = 'project';
        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'singular',
                    'label' => __('Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Project', 'business-manager'),
                ],
                [
                    'name' => 'plural',
                    'label' => __('Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Projects', 'business-manager'),
                ],
                [
                    'name' => 'col_0_title',
                    'label' => __('Tasks Column 1 Title', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('', 'business-manager'),
                ],
                [
                    'name' => 'col_1_title',
                    'label' => __('Tasks Column 2 Title', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('', 'business-manager'),
                ],
                [
                    'name' => 'col_2_title',
                    'label' => __('Tasks Column 3 Title', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('', 'business-manager'),
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    /**
     * Returns all the settings fields.
     *
     * @return array settings fields
     */
    public function get_document_fields()
    {
        $id = 'document';
        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'singular',
                    'label' => __('Singular Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Document', 'business-manager'),
                ],
                [
                    'name' => 'plural',
                    'label' => __('Plural Label', 'business-manager'),
                    'desc' => __('', 'business-manager'),
                    'type' => 'text',
                    'default' => __('Documents', 'business-manager'),
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    /**
     * Returns all the email fields.
     *
     * @return array settings fields
     */
    public function get_email_fields()
    {

        if ( ! class_exists( 'WP_List_Table' ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        }

        require dirname( __FILE__ ) . '/email/class-email-list-table.php';

        // Create an instance of our package class.
        $test_list_table = new TT_Example_List_Table();

        // Fetch, prepare, sort, and filter our data.
        $test_list_table->prepare_items();

        ?>
        <div class="wrap">
            <?php $test_list_table->display(); ?>
        </div>
        <?php
    }

    /**
     * Returns all the extension fields.
     *
     * @return array settings fields
     */
    public function get_extension_fields()
    {
        $id = 'extensions';

        $settings_fields = [
            "business-manager-{$id}" => [
                [
                    'name' => 'extensions',
                    'label' => '<a href="http://bzmngr.com/extensions/?utm_source=extensions-tab&utm_medium=plugin&utm_content=extensions" class="button" target="_blank">Browse All Extensions</a>',
                    'desc' => '<p>Extensions <em><strong>add functionality</strong></em> to the Business Manager plugin.</p>',
                    'type' => 'html',
                ],
            ],
        ];

        return apply_filters("business_manager_{$id}_settings_fields", $settings_fields);
    }

    public function plugin_page()
    {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages.
     *
     * @return array page names with key value pairs
     */
    public function get_pages()
    {
        $pages = get_pages();
        $pages_options = [];
        if ($pages) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }
}
endif;
