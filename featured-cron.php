<?php

/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Cron {

    function __construct() {
		add_action( 'clear_featured_data', [$this, 'clear_featured_data']);

		//update product id after updating user featured products
		add_action( 'update_user_meta', [$this, 'update_products'], 23, 4);

		register_activation_hook( __FILE__, function() {
			if (! wp_next_scheduled ( 'clear_featured_data' )) {
				wp_schedule_event( time(), 'hourly', 'clear_featured_data' );
			}
		});

		register_deactivation_hook( __FILE__, function() {
			wp_clear_scheduled_hook( 'clear_featured_data' );
		});
    }

	function clear_featured_data() {
		$users = get_users(array('role' => 'wcfm_vendor'));


		while ($user = current($users)) {
			next($users);

			$featured_vendor = get_user_meta( $user->ID, 'featured_vendor', true);
			
			$start_time = strtotime($featured_vendor->start_date);
			$expire_time = $start_time + ($featured_vendor->days * DAY_IN_SECONDS);

			
			if (is_a($featured_vendor, 'stdClass')) {
				if (strtotime($start_time) <= strtotime('now') ) {
					update_user_meta( $user->ID, 'wcfm_featured_category', $featured_vendor->category);
				}
				
				if ($expire_time < strtotime('now')) {
					delete_user_meta($user->ID, 'featured_vendor');
					delete_user_meta($user->ID, 'wcfm_featured_category');
				}
			}			

			$featured_products = get_user_meta( $user->ID, 'featured_products', true);

			if ( !is_array($featured_products) ) continue;

			$featured_products = array_filter($featured_products, function($product) {
				$expire_time = strtotime(sprintf("%s + %d days", $product['start'], $product['days']));	
				if ( $expire_time <= strtotime('now') ) {
					delete_post_meta($product['id'], 'wcfm_featured');
				}
				
				return $expire_time > strtotime('now');
			});

			update_user_meta($user->ID, 'featured_products',  $featured_products);
			$this->update_products($featured_products);

			if ( empty($featured_products) ) {
				delete_user_meta($user->ID, 'featured_products');
			}		
		}
	}

	function update_products($featured_products) {
		if (!is_array($featured_products)) return;

		while ($product = current($featured_products)) {
			next($featured_products);			
			if ( strtotime($product['start']) > strtotime('now') ) {
				continue;
			}

			$category = !empty($product['sub']) ? $product['sub'] : $product['category'];
			update_post_meta( $product['id'], 'wcfm_featured', $category);
		}
	}
}

add_action( 'initt', function(){
	do_action( 'clear_featured_data' );
	exit;
});