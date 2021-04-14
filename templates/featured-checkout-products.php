<?php

if ( !isset( $_SESSION['featured_products']) ) return;
$featured_products = $_SESSION['featured_products'];

var_dump($featured_products);

?>


    <?php echo do_shortcode('[wppayform id="81"]'); ?>
<form metho="POST">
    <?php wp_nonce_field( 'memarjana', 'payment_success'); ?>

    <div class="gap-10"></div>
    <button>Test Submit</button>
</form>