<?php

function get_wcfm_feature_table($table = 'vendors') {
    global $wpdb;
    return $wpdb->prefix . 'wcfm_feature_' . $table;
}

function get_wcfm_vendor_featured_url($endpoint = 'wcfm-featured') {
    return wcfm_get_endpoint_url( $endpoint, '', get_wcfm_page());
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

function wcfm_sanitize_session_products($products) {
    if ( !is_array($products) ) return [];

    $pricing = get_wcfm_featured_pricing();

    array_walk($products, function(&$product) use($pricing) {
        $post = get_post( $product['id']);
        if ( is_a($post, 'WP_Post') ) {
            $product['post_title'] = html_entity_decode($post->post_title);
        }

        $is_sub_category = absint($product['sub_category']) > 0;

        $product['target_term'] = $is_sub_category ? $product['sub_category'] : $product['category'];

        $main_term = get_term($product['category'], 'product_cat');
        if ( is_a( $main_term, 'WP_term' )) {
            $product['category_name'] = html_entity_decode($main_term->name);
        }

        if ( $is_sub_category ) {
            $sub_term = get_term($product['sub_category'], 'product_cat');
            if ( is_a( $sub_term, 'WP_term' )) {
                $product['sub_category_name'] = html_entity_decode($sub_term->name);
            }
        }

        $price = $is_sub_category ? $pricing['sub_category'] : $pricing['category'];
        $product['price'] = sizeof($product['dates']) * $price;
    });


    return $products;
}

function get_wcfm_feature_products() {
    global $wpdb;
    $table = get_wcfm_feature_table('products');

	$sql = sprintf("SELECT products.*, posts.post_author FROM $table products INNER JOIN $wpdb->posts posts ON products.product_id = posts.ID WHERE post_author = %d", get_current_user_id(  ));

	$vendor_products = $wpdb->get_results($sql, ARRAY_A);

	$products = [];
	while ($product = current($vendor_products)) {
		next($vendor_products);
		$id = $product['product_id'];
		$products[$id]['id'] = $product['product_id'];
		$products[$id]['category'] = $product['term_id'];
		$products[$id]['sub_category'] = $product['sub_term'];
		$products[$id]['dates'][] = $product['feature_date'];
	}
	
	return wcfm_sanitize_session_products($products);
}