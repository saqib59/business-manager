<?php
/**
 * @see bm_dashboard_field_html()
 * @see bm_dashboard_before_field_loop_html()
 * @see bm_dashboard_before_field_html()
 * @see bm_dashboard_after_field_html()
 * @see bm_dashboard_after_field_loop_html()
 */
add_action('bm_dashboard_single_field','bm_dashboard_field_html');
add_action('bm_dashboard_before_fields_loop', 'bm_dashboard_before_field_loop_html');
add_action('bm_dashboard_before_box_field', 'bm_dashboard_before_field_html');
add_action('bm_dashboard_after_box_field', 'bm_dashboard_after_field_html');
add_action('bm_dashboard_after_fields_loop', 'bm_dashboard_after_field_loop_html');