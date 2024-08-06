<?php

/**
 * Bm Custom Fields Add on install template.
 *
 * This template can be overridden by copying it to yourtheme/business-manager/templates/
 *
 * @version 1.5.8
 * @package business-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<!-- Custom fields Content Modal -->
<div class="bm-modal bm-custom-tabs-install-modal bm-custom-fields-install-modal">
    <div class="bm-modal-content bm-modal-content-medium">
        <div class="bm-modal-close bm-modal-close-top-right">&times;</div>
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 0 1803 466">
            <image width="1803" height="466"
                xlink:href="<?php echo esc_url( plugin_dir_url( __DIR__ ) . '/assets/images/extensions/custom-fields-modal.png' ); ?>">
            </image> <a xlink:href="https://bzmngr.com/custom-fields-extension/">
                <rect x="1527" y="327" fill="#fff" opacity="0" width="169" height="55"></rect>
            </a><a xlink:href="https://bzmngr.com/?edd_action=add_to_cart&download_id=190">
                <rect x="1131" y="294" fill="#fff" opacity="0" width="358" height="88"></rect>
            </a>
        </svg>
        <div class="bm-modal-footer">

        </div>
    </div>
</div>

<br class="clear" />
