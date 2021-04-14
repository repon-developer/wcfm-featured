<?php

if ( !isset( $_SESSION['featured_products']) ) return;
$featured_products = $_SESSION['featured_products'];


$pricing = get_featured_category_pricing();

array_walk($featured_products, function(&$item) use($pricing) {
    $price = absint($item['sub']) > 0 ? $pricing['sub'] : $pricing['main'];
    $item['price'] = $item['days'] * $price;


    $post = get_post( $item['id'] );
        if ( $post instanceof WP_Post ) {
            $item['post_title'] = $post->post_title;
        }

        $term_id = absint($item['sub']) > 0 ? $item['sub'] : $item['category'];

        $term = get_term($term_id, 'product_cat');
        if ( !is_wp_error( $term )) {
            $item['term_name'] = $term->name;
        }


});

$total_price = array_sum(array_column($featured_products, 'price')); ?>

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
<?php echo do_shortcode('[wppayform id="81"]'); ?>
<script>jQuery('[name="custom_payment_input"]').val(<?php echo $total_price ?>)</script>