<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Business_Manager_Dashboard', false)) :

/**
 * Business_Manager_Setup Class.
 */
class Business_Manager_Dashboard {
    public function __construct() {
    }

    /**
     * Output the dashboard.
     */
    public function page() {
        // $tabs        	= $this->get_tabs();
        // $first_tab      = array_keys( $tabs );
        // $current_tab  	= ! empty( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : $first_tab[0];
        $business = get_option('business-manager-general');

        include_once BUSINESSMANAGER_DIR.'/templates/dashboard.php';
    }
}

endif;

return new Business_Manager_Dashboard();
