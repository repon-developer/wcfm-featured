<?php
/**
 * Plugin Name: WCFM - WooCommerce Multivendor Featured
 * Plugin URI: 
 * Description: Featured store and product
 * Author: Repon Hossain
 * Version: 1.0.1
 * Author URI: https://repon.me
 *
 * Text Domain: wc-multivendor-featured
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 5.1.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

define('WCFM_FEATURED_PATH', plugin_dir_path( __FILE__ ));

define('WCFM_FEATURED_URI', plugin_dir_url( __FILE__ ));


add_action( 'wcfmmp_loaded', function(){
	global $WCFM_Marketplace_Featured;

	require_once( 'class-wcfmmp-fetured.php' );
	$GLOBALS['WCFM_Multivendor_Featured'] = new WCFM_Multivendor_Featured();
});
