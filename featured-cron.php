<?php

/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Cron {
	var $users = [];

    function __construct() {
		add_action( 'check_featured_data', [$this, 'check_featured_data_callback']);
    }

	function check_featured_data_callback() {
		$this->update_vendor_feature();
		$this->update_feature_products();
	}

	function update_vendor_feature() {
		global $wpdb;
		$wpdb->delete($wpdb->prefix.'usermeta', array('meta_key' => 'wcfm_featured'));


		$feature_table = get_wcfm_feature_table();

		$vendors_limit = 8;

		$featured_dates = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE feature_date = DATE(NOW()) LIMIT %d", $feature_table, $vendors_limit));

		while ( $date = current($featured_dates) ) {
			next($featured_dates);
			update_user_meta( $date->vendor_id, 'wcfm_featured', $date->term_id);
		}
	}

	function update_feature_products() {
		global $wpdb;
		$wpdb->delete($wpdb->prefix.'postmeta', array('meta_key' => 'wcfm_featured'));

		$feature_table = get_wcfm_feature_table('products');
		$per_day_limit = 12;

		$products = $wpdb->get_results(sprintf("SELECT * FROM $feature_table WHERE feature_date = DATE(NOW()) LIMIT %d", $per_day_limit));
		while ($product = current($products)) {
			next($products);
			$category = !empty($product->sub_term) ? $product->sub_term : $product->term_id;
			update_post_meta( $product->product_id, 'wcfm_featured', $category);
		}
	}
}

add_action( 'initd', function(){
	do_action( 'check_featured_data' );
	exit;
});