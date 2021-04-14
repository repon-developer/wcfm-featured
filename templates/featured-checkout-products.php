<?php

if ( !isset( $_SESSION['featured_products']) ) return;
$featured_products = $_SESSION['featured_products'];


$pricing = get_featured_category_pricing();

array_walk($featured_products, function(&$item) use($pricing) {
    $price = absint($item['sub']) > 0 ? $pricing['sub'] : $pricing['main'];

    $item['price'] = $item['days'] * $price;
});

var_dump($featured_products);

$total_price = array_sum(array_column($featured_products, 'price')); ?>

<h2>Total Cost: $<?php echo $total_price; ?></h2>
<div class="gap-10"></div>
<?php echo do_shortcode('[wppayform id="81"]'); ?>

<script>jQuery('[name="custom_payment_input"]').val(<?php echo $total_price ?>)</script>
    