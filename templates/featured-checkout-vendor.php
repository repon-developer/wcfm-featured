<?php

if ( !isset( $_SESSION['featured_vendor']) ) return;
$vendor_featured = $_SESSION['featured_vendor'];
$price = $_SESSION['wcfm_featured_price'];

if ( $price <= 0 ) {
    return;
} ?>

<h2>Total Cost: $<?php echo $price; ?></h2>
<div class="gap-10"></div>

<?php echo do_shortcode(sprintf('[wppayform id="%d"]', $payment_form)); ?>
<script>jQuery('[name="custom_payment_input"]').val(<?php echo $price ?>)</script>