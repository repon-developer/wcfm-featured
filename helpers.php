<?php

function get_wcfm_feature_table() {
    global $wpdb;
    return $wpdb->prefix . 'wcfm_feature';
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

function get_wcfm_featured_products($products = false) {
    if ( !is_array($products) ) {
        $products = get_user_meta( get_current_user_id(), 'featured_products', true);
    }    

    if ( !is_array($products)) return [];

    $pricing = get_wcfm_featured_pricing();

    array_walk($products, function(&$item) use($pricing) {
        $post = get_post( $item['id'] );
        if ( $post instanceof WP_Post ) {
            $item['post_title'] = $post->post_title;
        }

        $term_id = absint($item['sub']) > 0 ? $item['sub'] : $item['category'];

        $term = get_term($term_id, 'product_cat');
        if ( !is_wp_error( $term )) {
            $item['term_name'] = $term->name;
        }

        $price = absint($item['sub']) > 0 ? $pricing['sub_category'] : $pricing['category'];
        $item['price'] = $item['days'] * $price;

        $item['expire_on'] = date('Y-m-d H:i:s', strtotime($item['start']) + ($item['days'] * DAY_IN_SECONDS));
    });
    
    return $products;
}