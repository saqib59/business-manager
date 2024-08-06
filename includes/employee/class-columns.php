<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Employee_Columns', false)) :

class Business_Manager_Employee_Columns {
    public $post_type = 'bm-employee';
    public $cols = null;

    public $access_type = 'bm_access_employees';
    public $bm_employee_id;
    public $bm_access;

    public function __construct() {
        $this->init();
        $this->columns();
        $this->filters();
    }

    /**
     * Init.
     *
     * @since 1.0.0
     */
    public function init() {
        $this->cols = new BM_columns($this->post_type);

        add_action('plugins_loaded', [$this, 'bm_pluggable']);
    }

    /**
    *
    * Set Business Manager Variables that rely on pluggable functions.
    *
    * @since 1.4.1
    */
    public function bm_pluggable() {
        $this->bm_employee_id = business_manager_employee_id(get_current_user_id());
        $this->bm_access = business_manager_employee_access(get_current_user_id(), $this->access_type);

        if ($this->bm_access != 'full') {
            $this->cols->remove_column('cb');
        }
    }

    /**
     * Define columns.
     *
     * @since 1.0.0
     */
    public function columns() {
        if ($this->bm_access != 'full') {
            $this->cols->remove_column('cb');
        }

        $this->cols->remove_column('title');
        $this->cols->remove_column('taxonomy-bm-department');
        $this->cols->remove_column('date');
        $this->cols->remove_column('author');

        $columns = [
            '_bm_employee_last_name' => [
                'label' => __('Employee', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'person',
                'meta_fields' => [
                    'first_name' => '_bm_employee_first_name',
                    'last_name' => '_bm_employee_last_name',
                    'photo_id' => '_bm_employee_photo_id',
                ],
                'meta_key' => '_bm_employee_last_name',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_employee_department' => [
                'label' => __('Departments', 'business-manager'),
                'type' => 'custom_tax',
                'taxonomy' => 'bm-department',
            ],

            '_bm_employee_title' => [
                'label' => __('Job Title', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_employee_title',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_employee_status' => [
                'label' => __('Status', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_employee_status',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'status',
            ],

            '_bm_employee_type' => [
                'label' => __('Type', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_employee_type',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_employee_work_phone' => [
                'label' => __('Phone', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_employee_work_phone',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_employee_work_email' => [
                'label' => __('Email', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_employee_work_email',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'email',
            ],
        ];

        if (! business_manager_is_enabled('leave')) {
            unset($columns['leave']);
        }

        $columns = apply_filters('business_manager_columns_employee', $columns);

        foreach ($columns as $key => $col) {
            $this->cols->add_column($key, $col);
        }
    }

    public function filters() {
        if (isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == $this->post_type) {
            $this->cols->remove_filter('date');

            $filters = [
                'taxonomy-bm-department' => [
                    'label' => __('Departments', 'business-manager'),
                    'format' => 'taxonomies',
                    'taxonomy' => 'bm-department',
                ],

                '_bm_employee_title' => [
                    'label' => __('Job Titles', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_employee_title',
                ],

                '_bm_employee_status' => [
                    'label' => __('Statuses', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_employee_status',
                ],

                '_bm_employee_type' => [
                    'label' => __('Types', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_employee_type',
                ],
            ];

            foreach ($filters as $key => $filter) {
                $this->cols->add_filter($key, $filter);
            }
        }
    }
}

endif;

return new Business_Manager_Employee_Columns();
