<div class="page_collapsible" id="wcfm_settings_form_payment_head">
    <label class="wcfmfa" style="font-size:18px;"><?php echo get_woocommerce_currency_symbol(); ?></label>
    <?php _e('Featured Pricing', 'wc-multivendor-marketplace'); ?><span></span>
</div>

<div class="wcfm-container">
    <div id="wcfm_settings_form_payment_expander" class="wcfm-content">
        <h2><?php _e('Store Featured Pricing', 'wc-multivendor-featured'); ?></h2>
        <div class="wcfm_clearfix"></div>

        <?php
        $featured_vendor_price = wcfm_get_option( 'featured_vendor_price' );
        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "featured_vendor_price" => array(
                'label' => __('Price Per Day', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_vendor_price, 
                'desc' => __('Price per day for featured store', 'wc-multivendor-featured'), 
                ),
        ) ); ?>

        <div class="wcfm_clearfix"></div>
        <h2><?php _e('Featured Product Pricing', 'wc-multivendor-featured'); ?></h2>
        <div class="wcfm_clearfix"></div>
        <?php
        $category_pricing = wp_parse_args(wcfm_get_option( 'featured_category_pricing' ), ['main' => '', 'sub' => '']);

        
        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "featured_category_pricing[main]" => array(
                'label' => __('Main Category Price', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $category_pricing['main'], 
                'desc' => __('Price per day for main category', 'wc-multivendor-featured'), 
                ),
        ) );        
        
        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "featured_category_pricing[sub]" => array(
                'label' => __('Sub Category Price', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $category_pricing['sub'], 
                'desc' => __('Price per day for sub category', 'wc-multivendor-featured'), 
                ),
        ) );
	    ?>
    </div>
</div>