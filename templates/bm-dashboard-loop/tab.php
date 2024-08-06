<?php

if (!defined('ABSPATH')) {
    exit;
}
$tab = isset($_GET['tab']) ? $_GET['tab'] : null;
?>
    <div class="wrap">
        <nav class="nav-tab-wrapper">
            <?php
            foreach(business_manager_recruiting_dashboard_get_tab_fields() as $field){
                ?>
                <a href="?page=business-manager-recruiting&tab=<?php echo business_manager_recruiting_dashboard_get_the_key()  ?>" class="nav-tab <?php if($tab===business_manager_recruiting_dashboard_get_the_key()):?>nav-tab-active<?php endif; ?>"><?php echo business_manager_recruiting_dashboard_get_the_key() ?></a>
                <?php
            }
            ?>
            <a href="?page=business-manager-recruiting" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Default Tab</a>
            <a href="?page=business-manager-recruiting&tab=settings" class="nav-tab <?php if($tab==='settings'):?>nav-tab-active<?php endif; ?>">Settings</a>
            <a href="?page=business-manager-recruiting&tab=tools" class="nav-tab <?php if($tab==='tools'):?>nav-tab-active<?php endif; ?>">Tools</a>
        </nav>
        <div class="tab-content">
            <?php switch($tab) :
                case 'settings':
                    echo 'Settings'; //Put your HTML here
                    break;
                case 'tools':
                    echo 'Tools';
                    break;
                default:
                    echo 'Default tab';
                    break;
            endswitch; ?>
        </div>
    </div>
<?php

//$loop = business_manager_recruiting_dashboard_get_meta_query();
//if ( $loop->have_fields() ) {
//    /**
//     * Hook: business_manager_recruiting_before_jobs_loop.
//     */
//    do_action( 'business_manager_recruiting_before_dashboard_loop' );
//
//    while ( $loop->have_fields() ) :  $loop->the_field();
//        /**
//         * Hook: business_manager_recruiting_job_loop.
//         */
//        do_action( 'business_manager_recruiting_dashboard_loop' );
//        business_manager_recruiting_get_template_part( 'content', 'single-dashboard-field' );
//    endwhile;
//
//    /**
//     * Hook: business_manager_recruiting_after_job_loop.
//     *
//     * @hooked  business_manager_recruiting_pagination- 10
//     */
//    do_action( 'business_manager_recruiting_after_dashboard_loop' );
//
////    wp_reset_postdata();
//
//} else {
//    /**
//     * Hook: business_manager_recruiting_dashboard_no_field_found.
//     */
//    echo apply_filters( 'business_manager_recruiting_dashboard_no_field_found',  __('No meta box found', 'business-manager-recruiting'));
//}
