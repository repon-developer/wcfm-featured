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
        require_once WCFM_FEATURED_PATH . 'filters.php';

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
        
        wp_send_json([
            'nonce_vendor_featured' => wp_create_nonce('vendor_featured'),
            'nonce_featured_products' => wp_create_nonce('vendor_featured_products'),
            'featured_products' => get_wcfm_feature_products(),
        ]);

    }

    function featured_pricing() {
		global $WCFM, $WCFMmp;
		include_once 'templates/featured-pricing.php';
	}

	function featured_pricing_update($wcfm_settings_form) {
        wcfm_update_option( 'wc_featured_payment_form', $wcfm_settings_form['wc_featured_payment_form'] );
		wcfm_update_option( 'wcfm_featured_vendor_pricing', $wcfm_settings_form['wcfm_featured_vendor_pricing'] );
		wcfm_update_option( 'wcfm_featured_product_pricing', $wcfm_settings_form['wcfm_featured_product_pricing'] );
	}   
}

add_action('wcfmmp_store_list_after_store_info', function($store_id){
    $meta_key = !empty($_SESSION['wcfm_query_data']['key']) ? $_SESSION['wcfm_query_data']['key'] : false;
    if (!$meta_key) return;
    
    $meta_value = get_user_meta( $store_id, $meta_key, true);
    $session_value = !empty($_SESSION['wcfm_query_data']['value']) ? $_SESSION['wcfm_query_data']['value'] : '';

    if ( $meta_value == $session_value ) {
        echo '<span class="wcfm-featured-store">Featured</span>';
    }
}, 40);