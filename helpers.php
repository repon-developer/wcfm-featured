<?php

function featured_store_info($info) {
    $term = get_term_by( 'id', $info->category, 'product_cat');
    $expire = Date('Y-m-d', strtotime(sprintf("%s + %d days", $info->start_date, $info->days))); ?>
    <dl class="store-featured-info">
        <dt>Start Date</dt>
        <dd><?php echo $info->start_date ?></dd>

        <dt>Days</dt>
        <dd><?php echo $info->days ?></dd>

        <dt>Expire on</dt>
        <dd><?php echo $expire ?></dd>

        <dt>Category</dt>
        <dd><?php echo $term->name ?></dd>

        <dt>Total Cost</dt>
        <dd><?php echo $info->days * 5 ?></dd>
    </dl>
    <?php
}