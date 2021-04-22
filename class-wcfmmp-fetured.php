<?php

/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured {

    function __construct() {
        $this->load();
        
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action( 'wp_ajax_get_featured_data', [$this, 'get_featured_data']);
        add_action( 'wp_ajax_nopriv_get_featured_data', [$this, 'get_featured_data']);

        add_action( 'begin_wcfm_settings_form_style', 	array($this, 'featured_pricing'), 14);
		add_action( 'wcfm_settings_update', 			array($this, 'featured_pricing_update'), 14);  
    }

    private function load() {
        require_once WCFM_FEATURED_PATH . 'helpers.php';

        include_once 'featured-cron.php';
        $this->cron = new WCFM_Multivendor_Featured_Cron();

        include_once 'shortcodes.php';
        $this->endpoints = new WCFM_Multivendor_Featured_Shortcodes();

        include_once 'featured-payments.php';
        $this->payments = new WCFM_Multivendor_Featured_Payments();
    }

    function enqueue_scripts() {
        global $WCFMmp;
        wp_enqueue_style( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style( 'wc-multivendor-featured', WCFM_FEATURED_URI . 'assets/wc-multivendor-featured.css');

        wp_register_script( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_register_script( 'babel', 'https://unpkg.com/@babel/standalone/babel.min.js', [], null, true);
        wp_register_script( 'moment', WCFM_FEATURED_URI . 'assets/moment.min.js', [], null, true);

        wp_enqueue_style( 'wcfmmp_store_list_css',  $WCFMmp->library->css_lib_url_min . 'store-lists/wcfmmp-style-stores-list.css', array(), $WCFMmp->version );
    }

    function get_featured_data() {
        global $wpdb;
        $feature_table = get_wcfm_feature_table();

        
        $featured_dates = $wpdb->get_results(sprintf("SELECT term_id, feature_date FROM %s WHERE vendor_id = %s", $feature_table, get_current_user_id()));

        array_walk($featured_dates, function(&$row) {
            $term = get_term($row->term_id, 'product_cat');
            if ( !is_wp_error( $term )) {
                $row->term_name = html_entity_decode($term->name);
            }
        });


        $table_products = get_wcfm_feature_table('products');
	    $category_dates = $wpdb->get_results("SELECT term_id, feature_date, COUNT(*) as total FROM $table_products WHERE feature_date >= DATE(NOW()) GROUP BY term_id, feature_date");

        array_walk($category_dates, function(&$category){
            $category->total = absint( $category->total );
        });
        
        wp_send_json([
            'featured_dates' => $featured_dates,
            'nonce_vendor_featured' => wp_create_nonce('vendor_featured'),

            'feature_dates' => get_wcfm_feature_products(),
            'category_dates' => $category_dates,
            'nonce_featured_products' => wp_create_nonce('vendor_featured_products'),
        ]);

    }

    function featured_pricing() {
		global $WCFM, $WCFMmp;
		include_once 'templates/featured-pricing.php';
	}

	function featured_pricing_update($wcfm_settings_form) {
        wcfm_update_option( 'wc_featured_payment_form', $wcfm_settings_form['wc_featured_payment_form'] );
		wcfm_update_option( 'wcfm_featured_pricing', $wcfm_settings_form['wcfm_featured_pricing'] );
	}   
}


add_action('wcfmmp_store_list_after_store_info', function($store_id){

    $featured_vendor = get_user_meta( $store_id, 'featured_vendor', true);

    if ( $featured_vendor ) {
        echo '<span class="wcfm-featured-store">Featured</span>';
    }
}, 40);