<?php

if (!defined('ABSPATH')) {
    exit;
}
//args is provided from get_template
if ( $args->have_fields() ) {
    /**
     * Hook: bm_before_jobs_loop.
     */
        ?>
        <div class="wrap bm-dashboard">
    <?php
    do_action('bm_dashboard_before_fields_loop');
    while ( $args->have_fields() ) :  $args->the_field();
        /**
         * Hook: bm_dashboard_fields_loop.
         */
        do_action( 'bm_dashboard_fields_loop' );
        bm_get_template_part( 'content-dashboard', 'single-field' );
    endwhile;

    /**
     * Hook: bm_after_dashboard_loop.
     *
     */
    do_action('bm_dashboard_after_fields_loop');
        ?>
    </div><!--<div class="wrap">-->
    <?php
} else {
    /**
     * Hook: bm_no_jobs_found.
     */
    echo apply_filters( 'bm_dashboard_no_meta_box_found',  __('No meta box found', 'business-manager-recruiting'));
}
