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
        add_filter( 'woocommerce_shortcode_products_query', [$this, 'woocommerce_wcfm_featured_products_query'], 23, 2);
        add_filter( 'woocommerce_shortcode_products_query', [$this, 'woocommerce_featured_shortcode_products_query'], 23, 2);

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

    //This function use for shortcode where client want to show featured products in custom category
    function woocommerce_wcfm_featured_products_query($query_args, $attributes) {
        if ( !isset($attributes['wcfm_featured']) ) {
            return $query_args;
        }

        $meta_query = [];
        if ( $attributes['wcfm_featured'] === 'home' ) {
            $meta_query = array( 'key' => 'wcfm_featured_home_page', 'compare' => 'EXISTS' );
            $query_args['meta_query'] = array($meta_query);
            return $query_args;
        }

        if ( empty($attributes['wcfm_featured']) ) {
            // $meta_query = array( 'key' => 'wcfm_featured_home_page', 'compare' => 'EXISTS' );
            // $query_args['meta_query'] = array($meta_query);

            return $query_args;
        }

        $term = get_term_by('slug', trim($attributes['wcfm_featured']), 'product_cat');
        if ( !is_a($term, 'WP_Term') ) {
            return $query_args;
        }

        $query_args['meta_query'] = array('relation' => 'AND', array( 'key' => 'wcfm_featured', 'value' => "$term->term_id"));
        return $query_args;
    }

    //Show WCFM feature product instead of woocommerce feature product
    function woocommerce_featured_shortcode_products_query($query_args, $attributes) {
        if ( $attributes['visibility'] !== 'featured' ) return $query_args;

        $meta_query = array( 'key' => 'wcfm_featured_home_page', 'compare' => 'EXISTS' );


        
        $query_args['meta_query'] = array($meta_query);
        $tax_query = array_filter($query_args['tax_query'], function($tax){
            return !($tax['taxonomy'] == 'product_visibility' && $tax['terms'] == 'featured' && $tax['operator'] == 'IN');
        });

        //Remove default functionality of wordpress
        unset($query_args['post__not_in']);
        
        
        $query_args['tax_query'] = $tax_query;

        $query_args['wcfm_feature_products'] = true;
        
        return $query_args;
    }
}

