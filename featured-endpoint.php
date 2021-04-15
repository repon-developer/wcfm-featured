<?php

function is_valid_featured_store_info($info) {
    if ( absint($info->days) == 0 ) {
        return false;
    }

    if ( absint($info->category) == 0 ) {
        return false;
    }

    return true;
}

function get_wcfm_vendor_featured_url($endpoint = 'wcfm-featured') {
    return wcfm_get_endpoint_url( $endpoint, '', get_wcfm_page());
}

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

        if (!session_id() ) {
            session_start();
        }

        add_filter( 'wcfm_query_vars', array( &$this, 'featured_wcfm_query_vars' ), 20 );
        add_action( 'init', array( &$this, 'featured_wcfm_init' ), 20 );
        add_filter( 'wcfm_menus', array( &$this, 'featured_wcfm_menus' ), 20 );
        
        add_action( 'wcfm_load_scripts', array( &$this, 'wcfm_customers_load_scripts' ), 30 ); 
        add_action( 'wcfm_load_views', array( &$this, 'wcfm_customers_load_views' ), 30 );

        add_action( 'init', [$this, 'store_featured_before_payment']);
        add_action( 'init', [$this, 'products_featured_before_payment']);

        add_filter( 'script_loader_tag', [$this, 'enqueue_babel_script'], 10, 3 ); 

        //fire event after successful payment
        add_action('wppayform/form_payment_success', [$this, 'featured_info_payment_successfull'], 23);

        //$this->featured_info_payment_successfull('');
    }

    function featured_info_payment_successfull($submission) {
        $current_form = $_SESSION['wcfm_featured_form']; 

        if ( 'featured_products' == $current_form && isset($_SESSION['featured_products']) ) {
            $featured_products = get_user_meta( get_current_user_id(), 'featured_products', true);
            if ( !is_array($featured_products) ) {
                $featured_products = [];
            }

            $featured_products = array_merge($featured_products, $_SESSION['featured_products']);

            update_user_meta( get_current_user_id(), 'featured_products', $featured_products );
            unset($_SESSION['featured_products']);
        }

        if ( 'featured_vendor' == $current_form && isset($_SESSION['featured_vendor']) ) {
            update_user_meta( get_current_user_id(), 'featured_vendor', $_SESSION['featured_vendor'] );
            unset($_SESSION['featured_vendor']);
        }

        if ( isset($_REQUEST['wpf_action']) && $_REQUEST['wpf_action'] == 'stripe_hosted_success' ) {
            wp_safe_redirect(get_wcfm_vendor_featured_url());
            unset($_SESSION['wcfm_featured_form']);
            exit;
        }        
    }

    function store_featured_before_payment() {
        if (!wp_verify_nonce($_POST['_nonce_featured_vendor'], 'vendor_featured') ) {
            return;
        }

        $days = absint($_POST['wcfm_featured_store_days']);

        
        $featured_error = new WP_Error();

        if ( empty($_POST['wcfm_featured_store_start_date']) ) {
            $featured_error->add('start_date', "Start date is not valid date value");
        }

        if ( $days == 0) {
            $featured_error->add('days', "Days should be large than 0");
        }

        if ( !empty($featured_error->errors) ) {
            return;
        }
        
        unset($_SESSION['featured_products']);

        $_SESSION['wcfm_featured_form'] = 'featured_vendor';
        $_SESSION['featured_vendor'] = (object) array(
            'start_date' => $_POST['wcfm_featured_store_start_date'],
            'days' => $_POST['wcfm_featured_store_days'],
            'category' => $_POST['wcfm_featured_store_category'],
        );

        wp_safe_redirect(get_wcfm_vendor_featured_url('wcfm-featured-checkout'));
        exit;
    }

    function products_featured_before_payment() {
        if (!wp_verify_nonce($_POST['_nonce_featured_products'], 'vendor_featured_products') ) {
            return;
        }

        unset($_SESSION['featured_vendor']);

        $_SESSION['wcfm_featured_form'] = 'featured_products';
        $_SESSION['featured_products'] = $_POST['featured_products'];

        wp_safe_redirect(get_wcfm_vendor_featured_url('wcfm-featured-checkout'));
        exit;
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
            'priority' => 3
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

        wp_enqueue_script( 'wc-multivendor-featured', WCFM_FEATURED_URI . 'assets/wp-multivendor-featured.js', ['jquery', 'flatpickr', 'moment', 'react', 'react-dom', 'babel'], null, true);
        wp_localize_script( 'wc-multivendor-featured', 'wcfeatured', [
            'ajax' => admin_url( 'admin-ajax.php' ),
            'pricing' => get_wcfm_featured_pricing(),
            'products' => get_posts( ['post_type' => 'product', 'author' => get_current_user_id()] ),
            'categories' => get_terms( 'product_cat', array('hide_empty' => false) )
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