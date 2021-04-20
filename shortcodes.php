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
        add_filter( 'wcfmmp_stores_args', [$this, 'wcfm_featured_stores'], 23, 2);
        add_filter( 'woocommerce_shortcode_products_query', [$this, 'woocommerce_shortcode_products_query'], 23, 2);

        add_filter( "shortcode_atts_products", [$this, 'support_default_shortcode_attr'], 34, 4);
        add_filter( "wcfmmp_stores_default_args", function($atts){
            $atts['wcfm_featured'] = false;
            return $atts;
        });
    }

    function support_default_shortcode_attr($out, $pairs, $atts, $shortcode) {
        if ( !isset($atts['wcfm_featured']) ) {
            return $out;         
        }

        $out['wcfm_featured'] = $atts['wcfm_featured'];
        return $out;
    }

    function wcfm_featured_stores($query_data, $atts) {        
        if ( $atts['wcfm_featured'] === false ) {
            return $query_data;
        }
                   
        $disabled = ['search', 'filter', 'has_orderby', 'map'];
        while ($key = current($disabled)) {
            next($disabled);
            $query_data[$key] = false;
        }

        $query_args = array(
            'role' => 'wcfm_vendor', 
            'offset' => $query_data['offset'],
            'meta_key' => 'wcfm_featured'
        );

        if ( !empty($atts['wcfm_featured']) ) {
            $term = get_term_by('slug', $atts['wcfm_featured'], 'product_cat');        
            if ( is_a($term, 'WP_Term') ) {
                $query_args['meta_value'] = $term->term_id;
            }
        }

        $vendors = get_users($query_args);
        $query_data['stores'] = array_combine( wp_list_pluck($vendors, 'ID'), wp_list_pluck($vendors, 'user_login') );
        return $query_data;
    }

    function woocommerce_shortcode_products_query($query_args, $attributes) {
        if ( !isset($attributes['wcfm_featured']) ) {
            return $query_args;
        }

        $query_args['meta_query'] = array(array( 'key' => 'wcfm_featured', 'compare' => 'EXISTS' ));
        if (empty($attributes['wcfm_featured'])) {
            return $query_args;
        }

        $term = get_term_by('slug', trim($attributes['wcfm_featured']), 'product_cat');
        if ( !is_a($term, 'WP_Term') ) {
            return $query_args;
        }

        $query_args['meta_query'] = array('relation' => 'AND', array( 'key' => 'wcfm_featured', 'value' => "$term->term_id"));
        return $query_args;
    }
}
