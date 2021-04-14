<?php

/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Settings {

    function __construct() {
		add_action( 'begin_wcfm_settings_form_style', 	array($this, 'featured_pricing'), 14);
		add_action( 'wcfm_settings_update', 			array($this, 'featured_pricing_update'), 14);
    }

	function featured_pricing() {
		global $WCFM, $WCFMmp;
		include_once 'featured-pricing.php';
	}

	function featured_pricing_update($wcfm_settings_form) {

		$featured_store_price = number_format( $wcfm_settings_form['featured_store_price'], 2 );
		wcfm_update_option( 'featured_store_price', 	$featured_store_price );

		$featured_product_price = number_format ( $wcfm_settings_form['featured_product_price'], 2 );
		wcfm_update_option( 'featured_product_price', 	$featured_product_price );
	}
}