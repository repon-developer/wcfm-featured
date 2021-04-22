<?php

if ( !isset($_SESSION['wcfm_featured_product']) || !is_array($_SESSION['wcfm_featured_product']) ) return;
$total_price = $_SESSION['wcfm_featured_price'];

if ( $total_price <= 0 ) {
    return;
} ?>

<?php echo do_shortcode(sprintf('[wppayform id="%d"]', $payment_form)); ?>
<script>jQuery('[name="custom_payment_input"]').val(<?php echo $total_price ?>)</script>