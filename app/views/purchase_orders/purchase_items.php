<?php
$purchase_orders = $data["purchase_orders"] ?? [];
$warehouses = $data["warehouses"] ?? [];
$products = $data["products"] ?? [];

ob_start();
?>


<div class="container">
    <div class="container-header">
        <h2>Purchase Order Items</h2>
    </div>

    <form action="purchase-items/form-submit" method="post" class="form purchase-items-form">
        <!-- SELECT PURCHASE ORDER: Dropdown, required -->
        <select name="order_id" id="purchase-order" required>
            <option value="">Select Purchase Order</option>
            <?php foreach ($purchase_orders as $order): ?>
                <option value="<?= $order["id"] ?>"><?= $order[
    "po_number"
] ?></option>
            <?php endforeach; ?>
        </select>
            
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>

                        <tr onclick="addPurchaseItem(event)" onkeydown="addPurchaseItemEnter(event)">

                            <td  class="purchase-productId hide"><?= $product[
                                "id"
                            ] ?></td>
                            <td class="purchase-productName" ><?= $product[
                                "name"
                            ] ?></td>
                            <td class="purchase-productUnit" ><?= $product[
                                "unit"
                            ] ?></td>
                            <td class="purchase-productPrice" ><?= $product[
                                "price"
                            ] ?></td>
                            <td>
                                <input class="purchase-productQuantity"  type="number" name="">
                            </td>
                            <td>
                                <button type="button" class="add-item">Add</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Added items -->
        <div class="table-wrapper">
            <h3>Purchase Items</h3>
            <table>
                <thead>
                    <tr>
                        <td>Product</td>
                        <td>Unit</td>
                        <td>Unit Price</td>
                        <td>Quantity</td>
                        <td>#</td>
                    </tr>
                </thead>
                <tbody class="purchase-items-list">

                </tbody>
            </table>
        </div>

        <button type="submit">Save Purchase Order</button>
    </form>

    
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . "/../layouts/layout.php";


?>
