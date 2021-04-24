<?php

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
add_action( 'pre_get_posts', 'wcfm_featured_product_query' );

add_filter( 'posts_orderby', function($orderby, $query ){
    global $wpdb;
    if ( is_post_type_archive('product') && $query->is_main_query() ) {
        $orderby = "{$wpdb->postmeta}.meta_key DESC, " . $orderby;
    }

    return $orderby;
}, 23, 2);



add_filter( 'wcfmmp_vendor_list_args', function($args, $search_data){
    $category = $search_data['wcfmmp_store_category'];

    $args['wcfm_meta_query'] = 'wcfm_featured_home_page';
    $args['wcfm_meta_value'] = 'home';
    if ($category) {
        $args['wcfm_meta_query'] = 'wcfm_featured_category';
        $args['wcfm_meta_value'] = $category;
    }
    
    $_SESSION['wcfm_query_data'] = array('key' => $args['wcfm_meta_query'], 'value' => $args['wcfm_meta_value']);

    return $args;
}, 23, 2);


add_action('pre_user_query', function($query){
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if( !($meta_key = $query->query_vars['wcfm_meta_query']) ) {
        return;
    }
    
    $meta_value = $query->query_vars['wcfm_meta_value'];

    global $wpdb; 
    $query->query_from .= sprintf(" LEFT JOIN (
        SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key='%s' AND meta_value = '%s'
    ) AS wcfm_m1 ON (wp_users.ID = wcfm_m1.user_id)", $meta_key, $meta_value);

    $orderby = str_replace('ORDER BY', '', $query->query_orderby);
    $query->query_orderby = 'ORDER BY wcfm_m1.meta_key DESC';
    
    if (!empty($orderby)) {
        $query->query_orderby .= ',' . $orderby;
    }
});
