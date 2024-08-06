<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Client_Columns', false)) :

/**
 * Business_Manager_Client_Columns Class.
 */
class Business_Manager_Client_Columns {
    public $post_type = 'bm-client';
    public $cols = null;

    public $access_type = 'bm_access_clients';
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
        $this->cols->remove_column('title');
        $this->cols->remove_column('taxonomy-bm-status-client');
        $this->cols->remove_column('date');
        $this->cols->remove_column('author');

        $columns = [
            '_bm_client' => [
                'label' => __('Client', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'client',
                'sortable' => true,
            ],

            '_bm_client_status' => [
                'label' => __('Status', 'business-manager'),
                'type' => 'custom_tax',
                'taxonomy' => 'bm-status-client',
            ],

            '_bm_client_address' => [
                'label' => __('Address', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'address',
                'meta_fields' => [
                    'address' => '_bm_client_address',
                    'city' => '_bm_client_city',
                    'state_province' => '_bm_client_state_province',
                    'zipcode' => '_bm_client_zipcode',
                    'country' => '_bm_client_country',
                ],
                'sortable' => false,
            ],

            '_bm_client_phone' => [
                'label' => __('Phone', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_client_phone',
                'orderby' => 'meta_value',
                'sortable' => true,
                'def' => '',
            ],

            '_bm_client_email' => [
                'label' => __('Email', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_client_email',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'email',
                'def' => '',
            ],

            '_bm_client_website' => [
                'label' => __('Website', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_client_website',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'link',
                'def' => '',
            ],
        ];

        $columns = apply_filters('business_manager_columns_client', $columns);

        foreach ($columns as $key => $col) {
            $this->cols->add_column($key, $col);
        }
    }

    public function filters() {
        if (isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == $this->post_type) {
            $this->cols->remove_filter('date');

            $filters = [
                'taxonomy-bm-status-client' => [
                    'label' => __('Statuses', 'business-manager'),
                    'format' => 'taxonomies',
                    'taxonomy' => 'bm-status-client',
                ],
            ];

            foreach ($filters as $key => $filter) {
                $this->cols->add_filter($key, $filter);
            }
        }
    }
}

endif;

return new Business_Manager_Client_Columns();
