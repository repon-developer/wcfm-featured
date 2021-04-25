<?php extract($email_vars); ?>

<h3>You have successfully purchased</h3>
<p>We will aprove your feature product your within 24 hours.</p>

<table class="table-email-payment-confirm" style="border:1px solid #ccc;border-collapse:collapse">
    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Product</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><?php echo $post_title ?></td>
    </tr>

    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Category</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><?php echo $category_name ?></td>
    </tr>
    
    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Subcategory</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><?php echo $subcategory_name ?></td>
    </tr>

    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Cost</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><b>$<?php echo $purchase_value ?></b></td>
    </tr>
</table>
