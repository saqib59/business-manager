<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

function business_manager_is_an_employee() {
    if ( current_user_can( 'bm_employee' ) ) {
        return true;
    }

    return false;
}