<div class="page_collapsible" id="wcfm_settings_form_payment_head">
    <label class="wcfmfa" style="font-size:18px;"><?php echo get_woocommerce_currency_symbol(); ?></label>
    <?php _e('Featured Pricing', 'wc-multivendor-featured'); ?><span></span>
</div>

<div class="wcfm-container">
    <div id="wcfm_settings_form_payment_expander" class="wcfm-content">
        <h2><?php _e('Payment Form Template', 'wc-multivendor-featured'); ?></h2>
        <div class="wcfm_clearfix"></div>

        <?php

            $form_options[0] = __('Select a form');
            $payforms = get_posts( ['post_type' => 'wp_payform', 'posts_per_page' => -1] );
            while ($f = current($payforms)) {
                next($payforms);
                $form_options[$f->ID] = $f->post_title;
            }

            $WCFM->wcfm_fields->wcfm_generate_form_field(array(
                "wc_featured_payment_form" => array( 
                    'label' => __('Payment Form', 'wc-multivendor-featured'), 
                    'type' => 'select', 
                    'options' => $form_options, 
                    'class' => 'wcfm-select wcfm_ele', 
                    'label_class' => 'wcfm_title', 
                    'value' => wcfm_get_option( 'wc_featured_payment_form' ), 
                    'desc_class' => 'wcfm_page_options_desc', 
                    'desc' => __( 'Please install WPPayForm and use donation template. Minimum amount should be lowest amount.', 'wc-multivendor-featured' ) 
                ),
            ) );
        ?>

        <h2><?php _e('Store Featured Pricing', 'wc-multivendor-featured'); ?></h2>
        <div class="wcfm_clearfix"></div>

        <?php
        $featured_vendor_pricing = wp_parse_args(wcfm_get_option( 'wcfm_featured_vendor_pricing' ), ['home_page' => '', 'category' => '']);
        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "wcfm_featured_vendor_pricing[home_page]" => array(
                'label' => __('Blexstore home page', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_vendor_pricing['home_page'], 
            ),
        ) );

        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "wcfm_featured_vendor_pricing[category]" => array(
                'label' => __('Category page', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_vendor_pricing['category'], 
            ),
        ) ); ?>

        <div class="wcfm_clearfix"></div>
        <h2><?php _e('Featured Product Pricing', 'wc-multivendor-featured'); ?></h2>
        <div class="wcfm_clearfix"></div>
        <?php
        $featured_product_pricing = wp_parse_args(wcfm_get_option( 'wcfm_featured_product_pricing' ), ['home_page' => '', 'category' => '', 'subcategory' => '']);
        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "wcfm_featured_product_pricing[home_page]" => array(
                'label' => __('Home page', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_product_pricing['home_page'], 
            ),
        ) );

        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "wcfm_featured_product_pricing[category]" => array(
                'label' => __('Category page', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_product_pricing['category'], 
            ),
        ) );

        $WCFM->wcfm_fields->wcfm_generate_form_field(array(
            "wcfm_featured_product_pricing[subcategory]" => array(
                'label' => __('Subcategory page', 'wc-multivendor-featured') , 
                'type' => 'text', 
                'class' => 'wcfm-text wcfm_ele', 
                'label_class' => 'wcfm_title', 
                'desc_class' => 'wcfm_page_options_desc', 
                'value' => $featured_product_pricing['subcategory'], 
            ),
        ) );
	    ?>
    </div>
</div>