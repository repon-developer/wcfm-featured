<?php
function get_featured_vendor_price() {
    return wcfm_get_option( 'featured_vendor_price', 4.99 );
}

function get_featured_category_pricing() {
    return wp_parse_args(wcfm_get_option( 'featured_category_pricing' ), ['main' => 2.99, 'sub' => 2.29]);
}

function get_wcfm_featured_products() {
    $featured_products = get_user_meta( get_current_user_id(), 'featured_products', true);
    if ( !is_array($featured_products)) return [];

    array_walk($featured_products, function(&$item){
        $post = get_post( $item['id'] );
        if ( $post instanceof WP_Post ) {
            $item['post_title'] = $post->post_title;
        }

        $term_id = absint($item['sub']) > 0 ? $item['sub'] : $item['category'];

        $term = get_term($term_id, 'product_cat');
        if ( !is_wp_error( $term )) {
            $item['term_name'] = $term->name;
        }
    });

    return $featured_products;
}