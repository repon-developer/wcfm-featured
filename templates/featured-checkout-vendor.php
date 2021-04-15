<?php

if ( !isset( $_SESSION['featured_vendor']) ) return;
$vendor_featured = $_SESSION['featured_vendor'];
$price = $vendor_featured->days * get_wcfm_featured_pricing()['vendor'];

if ( $price <= 0 ) {
    return;
} ?>



<h2>Total Cost: $<?php echo $price; ?></h2>
<div class="gap-10"></div>

<?php echo do_shortcode('[wppayform id="81"]'); ?>

<script>jQuery('[name="custom_payment_input"]').val(<?php echo $price ?>)</script>