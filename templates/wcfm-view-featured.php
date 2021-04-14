<?php
/**
 * WCFM plugin view
 *
 * WCFM Shop Customers View
 *
 * @author 		WC Lovers
 * @package 	wcfm/views/customers
 * @version   3.5.0
 */

global $WCFM, $featured_error; 

$store_featured = get_user_meta(get_current_user_id(  ), 'store_feature_info', true );

?>

<div class="collapse wcfm-collapse" id="wcfm_shop_listing">
    <div class="wcfm-page-headig">
        <span class="wcfmfa fa-user-circle fa-user-tie"></span>
        <span class="wcfm-page-heading-text"><?php _e( 'Featured Settings', 'wc-multivendor-featured' ); ?></span>
        <?php do_action( 'wcfm_page_heading' ); ?>
    </div>
    <div class="wcfm-collapse-content">
        <div class="wcfm-container wcfm-top-element-container">
            <h2><?php _e( 'Store & Product Featured Settings', 'wc-multivendor-featured' ); ?></h2>
        </div>

        <div class="gap-30"></div>
        <div id="wc-multivendor-featured"></div>
    </div>
</div>