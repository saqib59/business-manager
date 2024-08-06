<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Document_Columns', false)) :

/**
 * Business_Manager_Document_Columns Class.
 */
class Business_Manager_Document_Columns {
    public $post_type = 'bm-document';
    public $cols = null;

    public $access_type = 'bm_access_documents';
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
        $this->cols->remove_column('date');
        $this->cols->remove_column('author');

        $columns = [
            'version' => [
                'label' => __('Version', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_document_latest',
                'orderby' => 'meta_value',
                'sortable' => false,
                'format' => 'doc_version',
            ],

            'doc_date' => [
                'label' => __('Date', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_document_latest',
                'orderby' => 'meta_value',
                'sortable' => false,
                'format' => 'doc_date',
            ],

            'bm_employee' => [
                'label' => __('Updated By', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'doc_employee_name',
                'meta_fields' => [
                    'first_name' => '_bm_employee_first_name',
                    'last_name' => '_bm_employee_last_name',
                    'photo_id' => '_bm_employee_photo_id',
                ],
                'meta_key' => '_bm_document_latest',
                'orderby' => 'meta_value',
                'sortable' => false,
            ],

            'file' => [
                'label' => __('File', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_document_latest',
                'orderby' => 'meta_value',
                'sortable' => false,
                'format' => 'doc_file',
            ],

            'dwonload' => [
                'label' => __('Download', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_document_latest',
                'orderby' => 'meta_value',
                'sortable' => false,
                'format' => 'doc_download',
            ],
        ];

        $columns = apply_filters('business_manager_columns_document', $columns);

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
            ];

            foreach ($filters as $key => $filter) {
                $this->cols->add_filter($key, $filter);
            }
        }
    }
}

endif;

return new Business_Manager_Document_Columns();
