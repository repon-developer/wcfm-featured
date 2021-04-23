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

    function wcfm_featured_stores($query_data, $atts) {
        if ( $atts['wcfm_featured'] === false ) {
            return $query_data;
        }
        
        // $disabled = ['search', 'filter', 'has_orderby', 'map'];
        // while ($key = current($disabled)) {
            //     next($disabled);
            //     $query_data[$key] = false;
            // }
            


        $query_args = array('role' => 'wcfm_vendor', 'offset' => $query_data['offset'], 'meta_key' => 'wcfm_featured');

        if ( empty($query_data['search_category']) ) {
            $query_args['meta_key'] = 'wcfm_featured_home_page';
        }

        if ( $query_data['search_category'] ) {
            $query_args['meta_key'] = 'wcfm_featured_category';
            $query_args['meta_value'] = $query_data['search_category'];                
        }

        $vendors = get_users($query_args);
        //$query_data['stores'] = array_combine( wp_list_pluck($vendors, 'ID'), wp_list_pluck($vendors, 'user_login') );
        return $query_data;
    }

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

    function woocommerce_featured_shortcode_products_query($query_args, $attributes) {
        if ( $attributes['visibility'] !== 'featured' ) return $query_args;

        $meta_query = array( 'key' => 'wcfm_featured_home_page', 'compare' => 'EXISTS' );

        $query_args['meta_query'] = array($meta_query);
        $tax_query = array_filter($query_args['tax_query'], function($tax){
            return !($tax['taxonomy'] == 'product_visibility' && $tax['terms'] == 'featured' && $tax['operator'] == 'IN');
        });

        $query_args['tax_query'] = $tax_query;
       
        return $query_args;
    }
}


function wcfm_featured_product_query($query) {
    if ( is_admin() && $query->is_main_query() ) {
        return;
    }

    if ( is_post_type_archive('product') && $query->is_main_query() ) {
        $query->set('meta_query', array(
            'relation' => 'OR',
            'wcfm_featured_not' => array(
                'key' => 'wcfm_featured_home_page',
                'compare' => 'NOT EXISTS',
            ),
            'wcfm_featured' => array(
                'key' => 'wcfm_featured_home_page',
                'compare' => 'EXISTS',
            )
        ));
    }
}
add_action( 'pre_get_postsd', 'wcfm_featured_product_query' );

add_filter( 'posts_orderbyd', function($orderby, $query ){
    global $wpdb;
    if ( is_post_type_archive('product') && $query->is_main_query() ) {
        $orderby = "{$wpdb->postmeta}.meta_key DESC, " . $orderby;
    }

    return $orderby;
}, 23, 2);