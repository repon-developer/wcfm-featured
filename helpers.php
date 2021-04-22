<?php

function get_wcfm_feature_table($table = 'vendors') {
    global $wpdb;
    return $wpdb->prefix . 'wcfm_feature_' . $table;
}

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

    return $feature_dates;
}