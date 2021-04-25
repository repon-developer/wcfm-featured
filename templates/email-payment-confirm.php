<?php extract($feature_product); ?>

<h3>You have successfully purchased</h3>
<p>We will feature your product within an hour.</p>

<table class="table-email-payment-confirm" stype="border:none">
    <tr>
        <th>Product</th>
        <td><?php echo $post_title ?></td>
    </tr>

    <tr>
        <th>Category</th>
        <td><?php echo $category_name ?></td>
    </tr>
    
    <tr>
        <th>Subcategory</th>
        <td><?php echo $subcategory_name ?></td>
    </tr>

    <tr>
        <th>Cost</th>
        <td>$ <b><?php echo $feature_cost ?></b></td>
    </tr>
</table>


<style>
.table-email-payment-confirm, .table-email-payment-confirm th, .table-email-payment-confirm td {
    border: 1px solid #ccc
}
.table-email-payment-confirm {
    border: none;
    text-align: left;
    border-collapse: collapse;
}

.table-email-payment-confirm th, .table-email-payment-confirm td {
    padding: 8px 13px
}

.table-email-payment-confirm th {
    background-color: #c3c3c3
}
</style>