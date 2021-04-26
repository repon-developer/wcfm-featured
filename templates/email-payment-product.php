<?php extract($email_vars);

$date_string = implode(', ', $dates); 

$user = wp_get_current_user(  );
$store_name     = wcfm_get_vendor_store_name( get_current_user_id(  ) );
$store_name     = empty( $store_name ) ? $user->display_name : $store_name;

?>

<p>Thank you "<b><?php echo $store_name; ?></b>" for featuring your product(s) on BLEX. Please allow us 24 hours to approve your product(s). 
Once approved, your product(s) will be featured on <?php echo $date_string; ?>.</p>

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

<p>If you have any questions, please contact us at Info@blexshoppes.com</p>