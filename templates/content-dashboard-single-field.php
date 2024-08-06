<?php
/**
 * The template for displaying job content in the single-job.php template
 *
 * This template can be overridden by copying it to yourtheme/business-manager-recruiting-management/content-single-job.php.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hook: bm_dashboard_before_single_field.
 */
do_action( 'bm_dashboard_before_single_field' );

?>

        <?php
        /**
         * Hook: bm_dashboard_single_field.
         *
         */
        do_action( 'bm_dashboard_single_field' );
        ?>


<?php do_action( 'bm_dashboard_after_single_field' ); ?>