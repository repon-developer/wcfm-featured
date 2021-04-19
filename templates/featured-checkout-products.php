<?php
if ( !isset($_SESSION['wcfm_featured_products']) || !is_array($_SESSION['wcfm_featured_products']) ) return;
$featured_products = wcfm_sanitize_session_products((array) $_SESSION['wcfm_featured_products']);
$total_price = array_sum(array_column($featured_products, 'price'));

if ( $total_price <= 0 ) {
    return;
} ?>

<table class="table-featured-products">
    <caption>Your Featured Products</caption>
    <thead>
        <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Sub Category</th>
            <th>Dates</th>
            <th>Cost</th>
        </tr>
    </thead>

    <?php while ($product = current($featured_products)) : next($featured_products); ?>
    <tr>
        <td>#<?php echo $product['id'] ?></td>
        <td><?php echo $product['post_title'] ?></td>
        <td><?php echo $product['category_name'] ?></td>
        <td><?php echo $product['sub_category_name'] ?></td>
        <td>
            <?php 

            $dates = array_map(function($date){
                return date('j M, Y', strtotime($date));
            }, $product['dates']);
            echo implode(' | ', $dates); ?>
        </td>
        <td>$<?php echo $product['price'] ?></td>
    </tr>
    <?php endwhile; ?>

    <tfoot>
        <tr>
            <td colspan="5">Total Cost</td>
            <td>$<?php echo $total_price; ?></td>
        </tr>
    </tfoot>
</table>
<?php echo do_shortcode(sprintf('[wppayform id="%d"]', $payment_form)); ?>
<script>jQuery('[name="custom_payment_input"]').val(<?php echo $total_price ?>)</script>