<?php

function get_wcfm_vendor_featured_url($endpoint = 'wcfm-featured') {
    return wcfm_get_endpoint_url( $endpoint, '', get_wcfm_page());
}

function get_wcfm_limit($key = 'stores') {
    $feature_limit = wp_parse_args(wcfm_get_option( 'wcfm_featured_limit' ), ['stores' => '8', 'product' => '6']);
    if ( !empty($feature_limit[$key]) ) {
        return $feature_limit[$key];
    }

    return 3;
}

function get_wcfm_feature_pricing() {
    $pricing['vendor'] = wp_parse_args(wcfm_get_option( 'wcfm_featured_vendor_pricing' ), ['home_page' => 40, 'category' => 30, 'subcategory' => 20]);
    $pricing['product'] = wp_parse_args(wcfm_get_option('wcfm_featured_product_pricing'), ['home_page' => 40, 'category' => 30, 'subcategory' => 20]);
    $pricing['processing_fee'] = absint( wcfm_get_option('wcfm_featured_processing_fee', 5) );
    return $pricing;
}

function get_wcfm_feature_vendor($user_id = false) {
    if ( !$user_id ) {
        $user_id = get_current_user_id();
    }

    $vendor_dates = get_user_meta($user_id, 'wcfm_feature_vendor', true);
    if ( !is_array($vendor_dates)) {
        $vendor_dates = [];
    }

    $vendor_dates = array_filter($vendor_dates, function($date){
        return is_array($date);
    });

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

function sanitize_wcfm_products($product) {
    if ( !is_array($product) ) return [];

    $product['post_title'] = html_entity_decode(get_the_title($product['id']));

    $category = get_term( $product['category'] );
    if ( is_a($category, 'WP_Term') ) {
        $product['category_name'] = html_entity_decode($category->name);
    }

    $subcategory = get_term( $product['subcategory'] );
    if ( is_a($subcategory, 'WP_Term') ) {
        $product['subcategory_name'] = html_entity_decode($subcategory->name);
    }

    return $product;
}

function get_wcfm_feature_products($user_id = false) {
    if ( !$user_id ) {
        $user_id = get_current_user_id();
    }

    $feature_dates = (array) get_user_meta( $user_id, 'wcfm_feature_products', true);

    if ( !is_array($feature_dates)) {
        $feature_dates = [];
    }

    $feature_dates = array_filter($feature_dates, function($product){
        return is_array($product);
    });
    

    array_walk($feature_dates, function(&$product) {
        $product = sanitize_wcfm_products($product);
    });

    return array_filter($feature_dates, function($date){
        return is_array($date);
    });
}


function get_wcfm_category_dates($dates) {
    $cate_dates = [];    
    foreach ($dates as $key => $cat) {
        $cate_dates[$cat['category']][] = $cat;
    }

    array_walk($cate_dates, function(&$dates){        
        $dates = array_count_values(array_column($dates, 'date'));
    });

    return $cate_dates;
}

function get_wcfm_vendor_filled_dates() {
    $users = get_users( ['role' => 'wcfm_vendor'] );

    $vendor_dates = [];
    foreach ($users as $key => $user) {
        $user_dates = get_user_meta($user->ID, 'wcfm_feature_vendor', true);
        if (!is_array($user_dates)) continue;

        while ($date = current($user_dates)) {
            next($user_dates);
            $vendor_dates[] = $date;
        }
    }

    $featured_vendor = array_filter($vendor_dates, function($item){
        return $item['date'] >= Date('Y-m-d');
    });

    $home_page = array_filter($featured_vendor, function($date){
        return in_array('home_page', $date['packages']);
    });

    $filled_dates['home_page'] = array_count_values(array_column($home_page, 'date'));

    //get categories and dates
    $category = array_filter($featured_vendor, function($date){
        return in_array('category', $date['packages']);
    });

    $filled_dates['category'] = get_wcfm_category_dates($category);
    

    //get subcategories and dates    
    $subcategory = array_filter($featured_vendor, function($date){
        return in_array('subcategory', $date['packages']);
    });

    $filled_dates['subcategory'] = get_wcfm_category_dates($subcategory);

    return $filled_dates;
}

function get_wcfm_products_filled_dates() {
    $users = get_users( ['role' => 'wcfm_vendor'] );

    $product_dates = [];
    foreach ($users as $key => $user) {
        $user_dates = get_user_meta($user->ID, 'wcfm_feature_products', true);
        if (!is_array($user_dates)) continue;

        while ($date = current($user_dates)) {
            next($user_dates);
            $product_dates[] = $date;
        }
    }

    $featured_products = array_filter($product_dates, function($item){
        return $item['date'] >= Date('Y-m-d');
    });

    $filled_dates = [];
    $home_page = array_filter($featured_products, function($date){
        return in_array('home_page', $date['packages']);
    });

    $filled_dates['home_page'] = array_count_values(array_column($home_page, 'date'));


    
    $category = array_filter($featured_products, function($date){
        return in_array('category', $date['packages']);
    });

    $filled_dates['category'] = get_wcfm_category_dates($category);
    
    $subcategory = array_filter($featured_products, function($date){
        return in_array('subcategory', $date['packages']);
    });

    $filled_dates['subcategory'] = get_wcfm_category_dates($subcategory);

    return $filled_dates;
}