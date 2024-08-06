<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Review_Columns', false)) :

/**
 * Business_Manager_Review_Columns Class.
 */
class Business_Manager_Review_Columns {
    public $post_type = 'bm-review';
    public $cols = null;

    public $access_type = 'bm_access_reviews';
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
        $this->cols->remove_column('date');
        $this->cols->remove_column('author');

        $columns = [
            '_bm_review_employee' => [
                'label' => __('Employee', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'person_meta',
                'meta_fields' => [
                    'first_name' => '_bm_employee_first_name',
                    'last_name' => '_bm_employee_last_name',
                    'photo_id' => '_bm_employee_photo_id',
                ],
                'meta_key' => '_bm_review_employee',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_review_reviewer' => [
                'label' => __('Reviewer', 'business-manager'),
                'type' => 'post_meta',
                'format' => 'person_meta',
                'meta_fields' => [
                    'first_name' => '_bm_employee_first_name',
                    'last_name' => '_bm_employee_last_name',
                    'photo_id' => '_bm_employee_photo_id',
                ],
                'meta_key' => '_bm_review_reviewer',
                'orderby' => 'meta_value',
                'sortable' => true,
            ],

            '_bm_review_date' => [
                'label' => __('Review Date', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_review_date',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'date',
            ],

            '_bm_review_next_date' => [
                'label' => __('Next Review', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_review_next_date',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'date',
            ],

            '_bm_review_goals' => [
                'label' => __('Goals', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_review_goals',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'count',
                'suffix' => __(' Goals Set', 'business-manager'),
            ],

            '_bm_review_ratings' => [
                'label' => __('Ratings', 'business-manager'),
                'type' => 'post_meta',
                'meta_key' => '_bm_review_ratings',
                'orderby' => 'meta_value',
                'sortable' => true,
                'format' => 'count',
                'suffix' => __(' Items Rated', 'business-manager'),
            ],
        ];

        $columns = apply_filters('business_manager_columns_review', $columns);

        foreach ($columns as $key => $col) {
            $this->cols->add_column($key, $col);
        }
    }

    public function filters() {
        if (isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == $this->post_type) {
            $this->cols->remove_filter('date');

            $filters = [
                '_bm_review_employee' => [
                    'label' => __('Employees', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_review_employee',
                    'format' => 'employees',
                    'access' => [
                        'type' => 'bm_access_reviews',
                        'field' => '_bm_review_employee',
                    ],
                ],

                '_bm_review_reviewer' => [
                    'label' => __('Reviewers', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_review_reviewer',
                    'format' => 'employees',
                    'access' => [
                        'type' => 'bm_access_reviews',
                        'field' => '_bm_review_employee',
                    ],
                ],

                '_bm_review_date' => [
                    'label' => __('Review Dates', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_review_date',
                    'format' => 'dates',
                    'access' => [
                        'type' => 'bm_access_reviews',
                        'field' => '_bm_review_employee',
                    ],
                ],

                '_bm_review_next_date' => [
                    'label' => __('Next Review Dates', 'business-manager'),
                    'type' => 'post_meta',
                    'meta_key' => '_bm_review_next_date',
                    'format' => 'dates',
                    'access' => [
                        'type' => 'bm_access_reviews',
                        'field' => '_bm_review_employee',
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

return new Business_Manager_Review_Columns();
