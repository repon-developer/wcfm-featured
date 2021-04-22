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
    $pricing = wp_parse_args(wcfm_get_option( 'wcfm_featured_pricing' ), ['vendor' => 4.99, 'category' => 2.99, 'sub_category' => 2.29]);
    array_walk($pricing, function(&$price){
        if ( !$price) {
            $price = 0;
        }

        $price = number_format($price, 2);
    });

    return $pricing;
}

function get_wcfm_feature_products() {
    $feature_dates = get_user_meta( get_current_user_id(), 'wcfm_feature_products', true);
    array_walk($feature_dates, function(&$product) {
        $product['id'] = $product['id'];
        $product['post_title'] = html_entity_decode(get_the_title($product['id']));

        $category = get_term( $product['category'] );
        if ( is_a($category, 'WP_Term') ) {
            $product['category_term_name'] = html_entity_decode($category->name);
        }

        $sub_category = get_term( $product['sub_category'] );
        if ( is_a($sub_category, 'WP_Term') ) {
           $product['sub_category_term_name'] = html_entity_decode($sub_category->name);
        }
    });

    return $feature_dates;
}
