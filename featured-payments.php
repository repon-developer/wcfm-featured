<?php


/**
 * WCFM Marketplace Featured Plugin
 *
 * @author 		Repon Hossain
 * @package 	wcfmmp_featured/core
 * @version   1.0.1
 */


class WCFM_Multivendor_Featured_Payments {

    function __construct() {
        if (!session_id() ) {
            session_start();
        }

        add_action( 'init', [$this, 'create_session_for_vendor']);
        add_action( 'init', [$this, 'create_session_for_products']);

        //fire event after successful payment
        add_action('wppayform/form_payment_success', [$this, 'featured_info_payment_successfull'], 23);
        //add_filter('wppayform/create_submission_data', [$this, 'secured_wcfeatured_price']);

        add_action( 'init', function(){
            //$this->save_feature_data_products('wcfm_feature_product');
        });        
    }

    function secured_wcfeatured_price($submission) {
        $submission['payment_total'] = $_SESSION['wcfm_featured_price'] * 100;
        return $submission;
    }

    function featured_info_payment_successfull() {
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
            $rows[] = $wpdb->prepare("(%d, %d, %s)", $user_id, $category, $date);
        }

        $wpdb->query(sprintf("INSERT INTO $wcfm_feature_table (vendor_id, term_id, feature_date) VALUES %s", implode( ",\n", $rows )));
        unset($_SESSION['wcfm_feature_vendor']);
    }

    function save_feature_data_products($current_form) {
        if ( $current_form !== 'wcfm_feature_product') return;

        $feature_products = get_user_meta( get_current_user_id(), 'wcfm_feature_products', true);
        if ( !is_array($feature_products) ) {
            $feature_products = [];
        }

        $feature_product = $_SESSION['wcfm_featured_product'];        
        $dates = $feature_product['dates'];
        unset($feature_product['dates']);		
        while ($date = current($dates)) {
            next($dates);
            $feature_dates[] = array_merge(['date' => $date], $feature_product);
        }
        
        $feature_products = array_merge($feature_products, $feature_dates);
        update_user_meta(get_current_user_id(), 'wcfm_feature_products', $feature_products);
        unset($_SESSION['wcfm_featured_products']);
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

    function create_session_for_products() {
        if (!wp_verify_nonce($_POST['_nonce_featured_products'], 'vendor_featured_products') ) {
            return;
        }

        $_SESSION['wcfm_featured_current_form'] = 'wcfm_feature_product';
        $_SESSION['wcfm_featured_price'] = $_POST['price'];
        
        $_SESSION['wcfm_featured_product'] = array(
            'id'            => $_POST['id'], 
            'dates'         => $_POST['dates'],
            'category'      => $_POST['category'], 
            'packages'      => $_POST['packages'], 
            'sub_category'  => $_POST['sub_category'], 
        );
        wp_safe_redirect(get_wcfm_vendor_featured_url('wcfm-featured-checkout'));
        exit;
    }
}