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
    }

    private function load() {
        require_once WCFM_FEATURED_PATH . 'helpers.php';
        
        include_once 'wc-multivendor-featured-settings.php';
        $this->settings = new WCFM_Multivendor_Featured_Settings();
        
        include_once 'class.wc-multivendor-featured-endpoint.php';
        $this->endpoints = new WCFM_Multivendor_Featured_Endpoint();
    }

    function enqueue_scripts() {
        wp_enqueue_style( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style( 'wc-multivendor-featured', WCFM_FEATURED_URI . 'assets/wc-multivendor-featured.css');

        wp_register_script( 'flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        wp_register_script( 'babel', 'https://unpkg.com/@babel/standalone/babel.min.js', [], null, true);
        wp_register_script( 'moment', WCFM_FEATURED_URI . 'assets/moment.min.js', [], null, true);
    }

    function get_featured_data() {

        $vendor_featured = get_user_meta( get_current_user_id(), 'store_feature_info', true);

        if ( isset($vendor_featured->category) ) {
            $term = get_term_by( 'id', $vendor_featured->category, 'product_cat');
            $vendor_featured->category = $term->name;
        }

        wp_send_json([
            'vendor_featured' => $vendor_featured,
            'nonce_featured_products' => wp_create_nonce('vendor_featured_products'),
            'vendor_featured_products' => []
        ]);

    }
    
}

