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

        add_action( 'init', [$this, 'create_session_for_vendor']);
        add_action( 'init', [$this, 'products_featured_before_payment']);

        add_filter( 'script_loader_tag', [$this, 'enqueue_babel_script'], 10, 3 ); 

        //fire event after successful payment
        add_action('wppayform/form_payment_success', [$this, 'featured_info_payment_successfull'], 23);
        //$this->featured_info_payment_successfull('');
        //add_filter('wppayform/create_submission_data', [$this, 'secured_wcfeatured_price']);
    }

    function secured_wcfeatured_price($submission) {
        $submission['payment_total'] = $_SESSION['wcfm_featured_price'] * 100;
        error_log($submission['payment_total']);
        return $submission;
    }

    function featured_info_payment_successfull($submission) {     
        $current_form = $_SESSION['wcfm_featured_current_form'];
        $this->save_feature_data_vendor($current_form);
        $this->save_feature_data_products($current_form);

        unset($_SESSION['wcfm_featured_price']);
        unset($_SESSION['wcfm_featured_current_form']);

        if ( isset($_REQUEST['wpf_action']) && $_REQUEST['wpf_action'] == 'stripe_hosted_success' ) {
            wp_safe_redirect(get_wcfm_vendor_featured_url());
            exit;
        }        
    }

    function save_feature_data_vendor($current_form) {
        global $wpdb;
        if ( $current_form !== 'wcfm_feature_vendor') return;
        $wcfm_feature_table = get_wcfm_feature_table();

        $rows = [];
        $user_id = get_current_user_id(  );
        $category = $_SESSION['wcfm_feature_vendor']['category'];

        foreach ($_SESSION['wcfm_feature_vendor']['dates'] as $date) {
            $rows[] = $wpdb->prepare("(%d, %d, %s, 'vendor')", $user_id, $category, $date);
        }

        $wpdb->query(sprintf("INSERT INTO $wcfm_feature_table (object_id, term_id, feature_date, feature_type) VALUES %s", implode( ",\n", $rows )));
        unset($_SESSION['wcfm_feature_vendor']);
    }

    function save_feature_data_products($current_form) {
        if ( $current_form !== 'wcfm_feature_vendorddd') return;

        //get_wcfm_feature_table()

        exit;

        $featured_products = get_user_meta( get_current_user_id(), 'featured_products', true);
        if ( !is_array($featured_products) ) {
            $featured_products = [];
        }

        $featured_products = array_merge($featured_products, $_SESSION['featured_products']);

        update_user_meta( get_current_user_id(), 'featured_products', $featured_products );
        unset($_SESSION['featured_products']);
    }


    function create_session_for_vendor() {
        if (!wp_verify_nonce($_POST['_nonce_featured_vendor'], 'vendor_featured') ) {
            return;
        }


        $_SESSION['wcfm_featured_current_form'] = 'wcfm_feature_vendor';
        $_SESSION['wcfm_featured_price'] = $_POST['price'];

        $feature_dates = is_array($_POST['feature_dates']) ? $_POST['feature_dates'] : [];
        $wcfm_feature_vendor = array('category' => $_POST['feature_category'], 'dates' => $feature_dates);


        $_SESSION['wcfm_feature_vendor'] = $wcfm_feature_vendor;


        wp_safe_redirect(get_wcfm_vendor_featured_url('wcfm-featured-checkout'));
        exit;
    }

    function products_featured_before_payment() {
        if (!wp_verify_nonce($_POST['_nonce_featured_products'], 'vendor_featured_products') ) {
            return;
        }

        unset($_SESSION['featured_vendor']);
        $featured_products = get_wcfm_featured_products((array) $_POST['featured_products']);

        $_SESSION['featured_products'] = $featured_products;
        $_SESSION['wcfm_featured_form'] = 'featured_products';
        $_SESSION['wcfm_featured_price'] = array_sum(array_column($featured_products, 'price'));

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

        global $wpdb;
        $feature_table = get_wcfm_feature_table();

        $get_terms = get_terms( 'product_cat', array('hide_empty' => false) );

        array_walk($get_terms, function(&$term){
            $term->name = html_entity_decode($term->name);
        });

        $unavailable_dates_vendor = $wpdb->get_results(
            "SELECT term_id, feature_date, COUNT(*) as total FROM $feature_table 
            WHERE feature_type = 'vendor' AND feature_date > DATE(NOW()) GROUP BY term_id, feature_date HAVING total >= 8");

        if ( !is_array($unavailable_dates_vendor)) {
            $unavailable_dates_vendor = [];
        }

        wp_enqueue_script( 'wc-multivendor-featured', WCFM_FEATURED_URI . 'assets/wp-multivendor-featured.js', ['jquery', 'flatpickr', 'moment', 'react', 'react-dom', 'babel'], filemtime(WCFM_FEATURED_PATH. 'assets/wp-multivendor-featured.js'), true);        
        wp_localize_script( 'wc-multivendor-featured', 'wcfeatured', [
            'ajax' => admin_url( 'admin-ajax.php' ),
            'unavailable_dates_vendor' => $unavailable_dates_vendor,
            'pricing' => get_wcfm_featured_pricing(),            
            'products' => get_posts( ['post_type' => 'product', 'posts_per_page' => -1, 'author' => get_current_user_id()] ),
            'categories' => $get_terms
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