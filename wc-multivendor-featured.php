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

require_once( 'class-wcfmmp-fetured.php' );
$GLOBALS['WCFM_Multivendor_Featured'] = new WCFM_Multivendor_Featured();

register_activation_hook( __FILE__, function() {
	if (! wp_next_scheduled ( 'check_featured_data' )) {
		wp_schedule_event( time(), 'hourly', 'check_featured_data' );
	}
});

register_deactivation_hook( __FILE__, function() {
	wp_clear_scheduled_hook( 'check_featured_data' );
});


add_action( 'wcfmmp_loaded', function(){
	include_once 'featured-endpoint.php';
    new WCFM_Multivendor_Featured_Endpoint();
});


add_action( 'init', function(){
	if (!isset($_GET['dev']) ) return;
	
	$data = get_wcfm_limit('products');
	var_dump($data);
	exit;
});