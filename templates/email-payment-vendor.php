<?php extract($email_vars);
$on_blex_store = in_array('home_page', $packages) ? 'Yes' : 'No'; ?>

<h3>You have successfully purchased</h3>
<p>We will aprove your store within our BLEX store list.</p>

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