<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Project_Columns', false)) :

/**
 * Business_Manager_Project_Columns Class.
 */
class Business_Manager_Project_Columns {
    public $post_type = 'bm-project';
    public $cols = null;

    public $access_type = 'bm_access_projects';
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

        if ($this->bm_access == 'limited') {
            $this->cols->remove_column('cb');
        }
    }

    /**
     * Define columns.
     *
     * @since 1.0.0
     */
    public function columns() {
        $this->cols->remove_column('taxonomy-bm-type');
        $this->cols->remove_column('taxonomy-bm-status');
        $this->cols->remove_column('date');
        $this->cols->remove_column('author');

        $columns = [
            '_bm_project_client' => [
                'label' => __('Client', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'client_meta',
                'meta_key' => '_bm_project_client',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_type' => [
                'label' => __('Type', 'business-manager'),
                'type' => 'custom_tax',
                'taxonomy' => 'bm-type',
            ],

            '_bm_status' => [
                'label' => __('Status', 'business-manager'),
                'type' => 'custom_tax',
                'taxonomy' => 'bm-status',
                'sortable' => true,
            ],

            '_bm_project_assigned_to' => [
                'label' => __('Assigned To', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'person_meta',
                'meta_fields' => [
                    'first_name' => '_bm_employee_first_name',
                    'last_name' => '_bm_employee_last_name',
                    'photo_id' => '_bm_employee_photo_id',
                ],
                'meta_key' => '_bm_project_assigned_to',
                'orderby' => 'meta_value',
                'sortable' => false,
            ],

            '_bm_project_complete' => [
                'label' => __(' Complete', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_project_complete',
                'orderby' => 'meta_value',
                'sortable' => true,
                'def' => '0',
                'suffix' => '%',
            ],

            '_bm_project_start_date' => [
                'label' => __('Start Date', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_project_start_date',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'date',
            ],

            '_bm_project_end_date' => [
                'label' => __('End Date', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_project_end_date',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'date',
            ],
        ];

        if (! business_manager_is_enabled('clients')) {
            unset($columns['client']);
        }

        $columns = apply_filters('business_manager_columns_project', $columns);

        foreach ($columns as $key => $col) {
            $this->cols->add_column($key, $col);
        }
    }

    public function filters() {
        if (isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == $this->post_type) {
            $this->cols->remove_filter('date');

            $filters = [
                'taxonomy-bm-type' => [
                    'label' => __('Types', 'business-manager'),
                    'format' => 'taxonomies',
                    'taxonomy' => 'bm-type',
                    'access' => [
                        'type' => 'bm_access_projects',
                        'field' => '_bm_project_assigned_to',
                    ],
                ],

                'taxonomy-bm-status' => [
                    'label' => __('Statuses', 'business-manager'),
                    'format' => 'taxonomies',
                    'taxonomy' => 'bm-status',
                    'access' => [
                        'type' => 'bm_access_projects',
                        'field' => '_bm_project_assigned_to',
                    ],
                ],

                '_bm_project_assigned_to' => [
                    'label' => __('Assigned To', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_project_assigned_to',
                    'format' => 'employees',
                    'access' => [
                        'type' => 'bm_access_projects',
                        'field' => '_bm_project_assigned_to',
                    ],
                ],

                '_bm_project_client' => [
                    'label' => __('Clients', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_project_client',
                    'format' => 'clients',
                    'access' => [
                        'type' => 'bm_access_projects',
                        'field' => '_bm_project_assigned_to',
                    ],
                ],

                '_bm_project_complete' => [
                    'label' => __('Complete %', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_project_complete',
                    'format' => 'percentages',
                    'access' => [
                        'type' => 'bm_access_projects',
                        'field' => '_bm_project_assigned_to',
                    ],
                ],
            ];

            foreach ($filters as $key => $filter) {
                $this->cols->add_filter($key, $filter);
            }
        }
    }
}

endif;

return new Business_Manager_Project_Columns();
