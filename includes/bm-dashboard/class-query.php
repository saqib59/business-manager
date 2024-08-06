<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Dashboard_Query', false)) :

    /**
     * Business_Manager_Setup Class.
     */
    class Business_Manager_Dashboard_Query {

        /**
         * @var
         * array list of metaboxes
         */
        public $fields = [];

        public $current_field = -1;

        public $box_count;

        public function __construct($fields) {
            $this->fields = $fields;
            $this->box_count = count($this->fields);
        }

        public function have_fields(){
            if ( $this->current_field + 1 < $this->box_count ) {
                return true;
            }
            return false;
        }

        public function the_field(){
            $this->current_field++;
            bm_dashboard_set_the_field($this->fields[$this->current_field]);
        }
    }

endif;