<?php extract($email_vars);
$on_blex_store = in_array('home_page', $packages) ? 'Yes' : 'No';

$date_string = implode(', ', $dates); 

$user = wp_get_current_user(  );
$store_name     = wcfm_get_vendor_store_name( get_current_user_id(  ) );
$store_name     = empty( $store_name ) ? $user->display_name : $store_name;

?>

<p>Thank you "<b><?php echo $store_name; ?></b>" for featuring your store on the BLEX Vendor store list. Your store will be featured on the <?php echo $date_string; ?> you selected.</p>

<table class="table-email-payment-confirm" style="border:1px solid #ccc;border-collapse:collapse">
    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Blex store homepage</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><?php echo $on_blex_store ?></td>
    </tr>

    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Category</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><?php echo $category_name ?></td>
    </tr>    
    

    <tr>
        <th style="border:1px solid #ccc; text-align:left; padding: 8px 14px; background-color:#c3c3c3">Cost</th>
        <td style="padding: 8px 14px; border:1px solid #ccc"><b>$<?php echo $purchase_value ?></b></td>
    </tr>
</table>

<p>If you have any questions, please contact us at info@blexshoppes.com</p>