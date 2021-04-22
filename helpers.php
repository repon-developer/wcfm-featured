<?php

function get_wcfm_vendor_featured_url($endpoint = 'wcfm-featured') {
    return wcfm_get_endpoint_url( $endpoint, '', get_wcfm_page());
}

function get_wcfm_limit($type = 'vendor') {
    if ( $type == 'products') return 3; //6
    return 3; //8
}

function get_wcfm_featured_pricing() {
    $pricing = wp_parse_args(wcfm_get_option( 'wcfm_featured_pricing' ), ['vendor' => 4.99, 'category' => 2.99, 'subcategory' => 2.29]);
    array_walk($pricing, function(&$price){
        if ( !$price) {
            $price = 0;
        }

        $price = number_format($price, 2);
    });

    return $pricing;
}

function get_wcfm_feature_vendor() {
    $vendor_dates = get_user_meta( get_current_user_id(), 'wcfm_feature_vendor', true);
    if ( !is_array($vendor_dates)) {
        $vendor_dates = [];
    }

    array_walk($vendor_dates, function(&$vendor) {
        $category = get_term( $vendor['category'] );
        if ( is_a($category, 'WP_Term') ) {
            $vendor['category_name'] = html_entity_decode($category->name);
        }

        $subcategory = get_term( $vendor['subcategory'] );
        if ( is_a($subcategory, 'WP_Term') ) {
           $vendor['subcategory_name'] = html_entity_decode($subcategory->name);
        }
    });

    return $vendor_dates;
}

function get_wcfm_feature_products() {
    $feature_dates = (array) get_user_meta( get_current_user_id(), 'wcfm_feature_products', true);

    if ( !is_array($feature_dates)) {
        $feature_dates = [];
    }

    array_walk($feature_dates, function(&$product) {
        $product['post_title'] = html_entity_decode(get_the_title($product['id']));

        $category = get_term( $product['category'] );
        if ( is_a($category, 'WP_Term') ) {
            $product['category_name'] = html_entity_decode($category->name);
        }

        $subcategory = get_term( $product['subcategory'] );
        if ( is_a($subcategory, 'WP_Term') ) {
           $product['subcategory_name'] = html_entity_decode($subcategory->name);
        }
    });

    return array_filter($feature_dates, function($date){
        return is_array($date);
    });
}

function get_wcfm_vendor_dates() {
    $featured_vendor = array_filter(get_wcfm_feature_vendor(), function($item){
        return $item['date'] >= Date('Y-m-d');
    });

    $vendor_filled_dates = [];

    $home_page = array_filter($featured_vendor, function($date){
        return in_array('home_page', $date['packages']);
    });

    $vendor_filled_dates['home_page'] = array_count_values(array_column($home_page, 'date'));

    
    $category = array_filter($featured_vendor, function($date){
        return in_array('category', $date['packages']);
    });

    $vendor_filled_dates['category'] = array_count_values(array_column($category, 'date'));
    
    
    $subcategory = array_filter($featured_vendor, function($date){
        return in_array('subcategory', $date['packages']);
    });

    $vendor_filled_dates['subcategory'] = array_count_values(array_column($subcategory, 'date'));

    return $vendor_filled_dates;
}

function get_wcfm_products_dates() {
    $featured_products = array_filter(get_wcfm_feature_products(), function($item){
        return $item['date'] >= Date('Y-m-d');
    });

    $products_filled_dates = [];

    $home_page = array_filter($featured_products, function($date){
        return in_array('home_page', $date['packages']);
    });

    $products_filled_dates['home_page'] = array_count_values(array_column($home_page, 'date'));

    
    $category = array_filter($featured_products, function($date){
        return in_array('category', $date['packages']);
    });

    $products_filled_dates['category'] = array_count_values(array_column($category, 'date'));
    
    
    $subcategory = array_filter($featured_products, function($date){
        return in_array('subcategory', $date['packages']);
    });

    $products_filled_dates['subcategory'] = array_count_values(array_column($subcategory, 'date'));

    return $products_filled_dates;
}