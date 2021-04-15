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

global $WCFM; ?>

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
        <div class="wcfm-container">
            <div class="wcfm-content">

                <?php 
                    $payment_form = absint( wcfm_get_option( 'wc_featured_payment_form' ) );

                    if ( $payment_form == 0 ) {
                        echo '<h3>Please contact with administrator.</h3>';
                    } else {
                        if ( isset($_SESSION['featured_vendor']) && is_object($_SESSION['featured_vendor']) ) {
                            include_once 'featured-checkout-vendor.php';
                        }
                    
    
                        if ( isset($_SESSION['featured_products']) && is_array($_SESSION['featured_products']) ) {
                            include_once 'featured-checkout-products.php';
                        }
                    } ?>
            </div>
        </div>
    </div>
</div>

