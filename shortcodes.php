<?php

/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Shortcodes {

    function __construct() {
        add_filter( 'wcfmmp_stores_args', [$this, 'wcfm_featured_stores']);
        add_filter( 'woocommerce_shortcode_products_query', [$this, 'woocommerce_shortcode_products_query'], 23, 2);

        add_filter( "shortcode_atts_products", [$this, 'support_default_shortcode_attr'], 34, 4);
        add_filter( "shortcode_atts_wcfm_stores", [$this, 'support_default_shortcode_attr'], 34, 4);
    }

    function support_default_shortcode_attr($out, $pairs, $atts, $shortcode) {
        if ( !isset($atts['wcfm_featured']) ) {
            return $out;         
        }

        $out['wcfm_featured'] = $atts['wcfm_featured'];
        return $out;
    }

    function wcfm_featured_stores($atts) {
        if ( !in_array('featured', $atts['includes']) ) {
            return $atts;    
        }
    
        $atts['includes'] = [];
    
        $disabled = ['search', 'filter', 'has_orderby', 'map'];
        while ($key = current($disabled)) {
            next($disabled);
            $atts[$key] = false;
        }
        
        $vendors = get_users(array('role' => 'wcfm_vendor', 'meta_key' => 'featured_vendor'));
        $atts['stores'] = array_combine( wp_list_pluck($vendors, 'ID'), wp_list_pluck($vendors, 'user_login') );
        return $atts;
    }

    function woocommerce_shortcode_products_query($query_args, $attributes) {
        if ( !isset($attributes['wcfm_featured']) ) {
            return $query_args;
        }

        $query_args['meta_key'] = 'wcfm_featured';        
        if (empty($attributes['wcfm_featured'])) {
            return $query_args;
        }

        $term = get_term_by('slug', $attributes['wcfm_featured'], 'product_cat');
        
        if ( !is_a($term, 'WP_Term') ) {
            return $query_args;
        }
        
        $query_args['meta_value'] = $term->term_id;        
        return $query_args;
    }
}
