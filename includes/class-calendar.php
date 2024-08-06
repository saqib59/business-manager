<?php
/**
 * Employee calendar Class.
 */
class Business_Manager_Calendar {
    public function __construct() {
    }

    public function template() { ?>
    <div class="wrap">
        <div class="bm-calendar-top">
            <h1 class="wp-heading-inline"><?php _e('Employees Leaves Calendar', 'business-manager'); ?></h1>
            <a href="<?php echo esc_url(admin_url( "post-new.php?post_type=bm-leave" )); ?>" class="page-title-action"><?php _e('New Leave', 'business-manager'); ?></a>
        </div>
        <hr />
        <div id="business-manager-calendar" style="position: relative;"></div>
    </div>
    <?php }
}