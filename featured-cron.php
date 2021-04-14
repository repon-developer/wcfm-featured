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
			$featured_products = get_user_meta( $user->ID, 'featured_products', true);

			$featured_products = array_filter($featured_products, function($product) {
				$expire_time = strtotime(sprintf("%s + %d days", $product['start'], $product['days']));				
				delete_post_meta($product['id'], 'wcfm_featured');
				return $expire_time > strtotime('now');
			});
		
			update_user_meta($user->ID, 'featured_products',  $featured_products);

			$featured_vendor = get_user_meta( $user->ID, 'featured_vendor', true);
			$expire_time = strtotime(sprintf("%s + %d days", $featured_vendor->start_date, $featured_vendor->days));

			if ($expire_time < strtotime('now')) {
				delete_user_meta($user->ID, 'featured_vendor');
			}
		}
	}

	function update_products($meta_id, $object_id, $meta_key, $featured_products) {
		if ( !defined( 'DOING_CRON' ) ) {
			return;
		}

		if ( 'featured_products' !== $meta_key) {
			return;
		}

		while ($product = current($featured_products)) {
			next($featured_products);
			
			$category = !empty($product['sub']) ? $product['sub'] : $product['category'];
			update_post_meta( $product['id'], 'wcfm_featured', $category);
		}
	}
	
}

