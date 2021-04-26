<?php
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


add_action('wcfmmp_store_list_after_store_info', function($store_id){
    $meta_key = !empty($_SESSION['wcfm_query_data']['key']) ? $_SESSION['wcfm_query_data']['key'] : false;
    if (!$meta_key) return;
    
    $meta_value = get_user_meta( $store_id, $meta_key, true);
    $session_value = !empty($_SESSION['wcfm_query_data']['value']) ? $_SESSION['wcfm_query_data']['value'] : '';

    if ( $meta_value == $session_value ) {
        echo '<span class="wcfm-featured-store">Featured</span>';
    }
}, 40);


function wcfm_woocommerce_feature_product( $posts_clauses, $query ) {
    
    if ( (!is_admin() && $query->is_main_query() && ( is_shop() || is_tax('product_cat') ) ) || defined('DOING_AJAX') && DOING_AJAX ) {  
        global $wpdb;
        
        $meta_key = 'wcfm_featured_home_page';
        $meta_value = 'home';
        
        $object = get_queried_object(  );

        //For WP Filter plugin
        $queryvars = json_decode(stripslashes($_REQUEST['filtersDataBackend']));
        if ( !empty($queryvars[0]->settings[0])) {
            $object = get_term( $queryvars[0]->settings[0] );            
        }

        if ( is_a($object, 'WP_Term') ) {
            $meta_key = 'wcfm_featured_category';
            $meta_value = $object->term_id;

            if ( $object->parent > 0 ) {
                $meta_key = 'wcfm_featured_subcategory';
            }
        }

        set_query_var( 'wcfm_feature_key',  $meta_key);
        set_query_var( 'wcfm_feature_value',  $meta_value);

        $posts_clauses['join'] .= sprintf(" LEFT JOIN (
            SELECT DISTINCT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key='%s' AND meta_value = '%s'
        ) AS wcfm_post1 ON ({$wpdb->posts}.ID = wcfm_post1.post_id)", $meta_key, $meta_value);

        $posts_clauses['orderby'] = 'wcfm_post1.meta_key DESC, ' . $posts_clauses['orderby'];
        return $posts_clauses;
    }

    return $posts_clauses;

}
add_filter( 'posts_clauses', 'wcfm_woocommerce_feature_product', 20, 2 );

add_action( 'woocommerce_before_shop_loop_item', function(){
    global $wp_query;
    $meta_value = json_decode(stripslashes($_REQUEST['queryvars']))->product_category_id;

    $meta_key = get_query_var( 'wcfm_feature_key');
    $meta_value = get_query_var( 'wcfm_feature_value', $meta_value);

    $term_id = get_post_meta( get_the_id(), $meta_key, true);
    if ( $meta_value == $term_id ) {
        echo '<span class="wcfm-featured" title="featured by BLEX Store"></span>';
    }
});
