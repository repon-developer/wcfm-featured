<?php

function get_featured_vendor_price() {
    return 5.99;
}

function get_featured_category_pricing() {
    return array('main' => 2.99, 'sub' => 1.99);
}

function featured_store_info($info) {
    $term = get_term_by( 'id', $info->category, 'product_cat');
    $expire = Date('Y-m-d', strtotime(sprintf("%s + %d days", $info->start_date, $info->days))); ?>
    <dl class="store-featured-info">
        <dt>Start Date</dt>
        <dd><?php echo $info->start_date ?></dd>

        <dt>Days</dt>
        <dd><?php echo $info->days ?></dd>

        <dt>Expire on</dt>
        <dd><?php echo $expire ?></dd>

        <dt>Category</dt>
        <dd><?php echo $term->name ?></dd>

        <dt>Total Cost</dt>
        <dd><?php echo $info->days * 5 ?></dd>
    </dl>
    <?php
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