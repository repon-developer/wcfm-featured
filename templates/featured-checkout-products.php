<?php
if ( !isset($_SESSION['featured_products']) || !is_array($_SESSION['featured_products']) ) return;

$featured_products = get_wcfm_featured_products((array) $_SESSION['featured_products']);
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
            <th>Start Date</th>
            <th>Expired on</th>
            <th>Days</th>
            <th>Category</th>
            <th>Cost</th>
        </tr>
    </thead>

    <?php while ($item = current($featured_products)) : next($featured_products); ?>
    <tr>
        <td>#<?php echo $item['id'] ?></td>
        <td><?php echo $item['post_title'] ?></td>
        <td><?php echo date('F j, Y, g:i a', strtotime($item['start'])); ?></td>
        <td><?php echo date('F j, Y, g:i a', strtotime(sprintf('%s + %d days', $item['start'], $item['days']))); ?></td>
        <td><?php echo $item['days'] ?></td>
        <td><?php echo $item['term_name'] ?></td>
        <td>$<?php echo $item['price'] ?></td>
    </tr>
    <?php endwhile; ?>

    <tfoot>
        <tr>
            <td colspan="6">Total Cost</td>
            <td>$<?php echo $total_price; ?></td>
        </tr>
    </tfoot>
</table>
<?php echo do_shortcode(sprintf('[wppayform id="%d"]', $payment_form)); ?>
<script>jQuery('[name="custom_payment_input"]').val(<?php echo $total_price ?>)</script>