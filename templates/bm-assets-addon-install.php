<?php

/**
 * Bm Assets Manager Add on install template.
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
<!-- BM Assets Add on Content Modal -->
<div class="bm-modal bm-assets-manager-install-modal">
    <div class="bm-modal-content bm-modal-content-medium">
        <div class="bm-modal-close bm-modal-close-top-right">&times;</div>
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 0 1803 465">
            <image width="1803" height="465"
                xlink:href="<?php echo esc_url( plugin_dir_url( __DIR__ ) . '/assets/images/extensions/asset-manager-modal.png' ); ?>">
            </image> <a xlink:href="https://bzmngr.com/asset-manager/">
                <rect x="1602" y="324" fill="#fff" opacity="0" width="158" height="50"></rect>
            </a><a xlink:href="https://bzmngr.com/?edd_action=add_to_cart&download_id=84">
                <rect x="1208" y="294" fill="#fff" opacity="0" width="364" height="84"></rect>
            </a>
        </svg>
        <div class="bm-modal-footer">

        </div>
    </div>
</div>

<br class="clear" />
