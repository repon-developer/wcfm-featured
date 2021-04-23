<?php


/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Endpoint {

    function __construct() {
        $current_user = get_currentuserinfo();
        if ( !in_array('wcfm_vendor', $current_user->roles) ) {
            return;
        }
        
        add_filter( 'wcfm_query_vars', array( &$this, 'featured_wcfm_query_vars' ), 20 );
        add_action( 'init', array( &$this, 'featured_wcfm_init' ), 20 );
        add_filter( 'wcfm_menus', array( &$this, 'featured_wcfm_menus' ), 20 );
        
        add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_customers_load_scripts' ), 30 ); 
        add_action( 'wcfm_load_views', array( &$this, 'wcfm_customers_load_views' ), 30 );

        add_filter( 'script_loader_tag', [$this, 'enqueue_babel_script'], 10, 3 ); 
    }

    function featured_wcfm_init() {
        global $WCFM_Query;

        $WCFM_Query->init_query_vars();
        $WCFM_Query->add_endpoints();

        if( !get_option( 'wcfm_updated_end_point_wcfm_featured' ) ) {
            flush_rewrite_rules();
            update_option( 'wcfm_updated_end_point_wcfm_featured', 1 );
        }
    }

	function featured_wcfm_menus( $menus ) {
        global $WCFM;
        
        $featured_menus = array( 'wcfm-featured' => array(   'label'  => __( 'Featured', 'wc-frontend-manager'),
            'label' => 'Featured',
            'url' => get_wcfm_vendor_featured_url(),
            'icon' => 'star',
            'priority' => 5
        ) );

        $menus = array_merge( $menus, $featured_menus );
          
        return $menus;
    }

    function featured_wcfm_query_vars( $query_vars ) {
        $wcfm_modified_endpoints = wcfm_get_option( 'wcfm_endpoints', array() );

        $query_featured_vars = array(
            'wcfm-featured'           => ! empty( $wcfm_modified_endpoints['wcfm-featured'] ) ? $wcfm_modified_endpoints['wcfm-featured'] : 'featured',
            'wcfm-featured-checkout'  => ! empty( $wcfm_modified_endpoints['wcfm-featured-checkout'] ) ? $wcfm_modified_endpoints['wcfm-featured-checkout'] : 'featured-checkout',
        );

        $query_vars = array_merge( $query_vars, $query_featured_vars );

        return $query_vars;
    }

    public function wcfm_customers_load_scripts( $end_point ) {
        if ( 'wcfm-featured' !== $end_point) return;

        $get_terms = get_terms( 'product_cat', array('hide_empty' => false) );
        array_walk($get_terms, function(&$term){
            $term->name = html_entity_decode($term->name);
        });

        if ( !is_array($vendor_filled_dates)) {
            $vendor_filled_dates = [];
        }

        wp_enqueue_script( 'wc-multivendor-featured', WCFM_FEATURED_URI . 'assets/wp-multivendor-featured.js', ['jquery', 'flatpickr', 'moment', 'react', 'react-dom', 'babel'], filemtime(WCFM_FEATURED_PATH. 'assets/wp-multivendor-featured.js'), true);        
        wp_localize_script( 'wc-multivendor-featured', 'wcfeatured', [
            'ajax' => admin_url( 'admin-ajax.php' ),

            'vendor_limit' => get_wcfm_limit(),
            'product_limit' => get_wcfm_limit('products'),

            'vendor_filled_dates' => get_wcfm_vendor_dates(),
            'products_filled_dates' => get_wcfm_products_dates(),

            'categories' => $get_terms,
            'pricing' => get_wcfm_feature_pricing(),            
            'products' => get_posts( ['post_type' => 'product', 'posts_per_page' => -1, 'author' => get_current_user_id()] ),
        ]);
    }

    function enqueue_babel_script($tag, $handle, $src){
        if ( 'wc-multivendor-featured' === $handle ) {
            $tag = '<script type="text/babel" src="' . esc_url( $src ) . '" ></script>';
        }
    
        return $tag;
    }

    public function wcfm_customers_load_views( $end_point ) {
        if ( 'wcfm-featured-checkout' == $end_point ) {
            include_once 'templates/featured-checkout.php';
        }

        if ( 'wcfm-featured' == $end_point ) {
            include_once 'templates/wcfm-view-featured.php';
        }
    }
}