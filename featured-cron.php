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
		$this->clear_data();
		$this->update_vendor_feature();
		$this->update_feature_products();
	}

	function clear_data() {
		global $wpdb;
		$packages = ['home_page', 'category', 'subcategory'];
		foreach ($packages as $key => $package) {
			$wpdb->delete($wpdb->prefix.'postmeta', array('meta_key' => 'wcfm_featured_'.$package));
			$wpdb->delete($wpdb->prefix.'usermeta', array('meta_key' => 'wcfm_featured_'.$package));
		}
	}

	function get_term_value($package, $item) {
		$term_id = null;
		switch ($package) {
			case 'category':
				$term_id = $item['category'];
				break;
			
			case 'subcategory':
				$term_id = $item['subcategory'];
				break;
			
			default:
				$term_id = 'home';
				break;
		}

		return $term_id;
	}

	function update_vendor_feature() {
		$vendor_dates = array_filter(get_wcfm_feature_vendor(), function($date){
			return $date['date'] == Date('Y-m-d');
		});

		array_walk($vendor_dates, function($item){
			$packages = $item['packages'];
			while ($pack = current($packages)) {
				next($packages);
				update_user_meta(get_current_user_id(), 'wcfm_featured_' . $pack, $this->get_term_value($pack, $item));
			}
		});
	}

	function update_feature_products() {
		$feature_dates = array_filter(get_wcfm_feature_products(), function($date){
			return $date['date'] == Date('Y-m-d');
		});

		array_walk($feature_dates, function($item){
			$packages = $item['packages'];
			while ($pack = current($packages)) {
				next($packages);
				update_post_meta( $item['id'], 'wcfm_featured_' . $pack, $this->get_term_value($pack, $item));				
			}
		});
	}
}

add_action( 'initd', function(){
	do_action( 'check_featured_data' );
	exit;
});