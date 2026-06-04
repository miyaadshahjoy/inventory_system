<?php
$suppliers = $data['suppliers'] ?? [];
ob_start();
?>
<div class="container">
    <div class="container-header">
        <h2>Suppliers</h2>
        <button onclick="openModal()">+ Add Supplier</button>
    </div>
    <div class="table-wrapper">
        <!--
        # Supplier Name | Email | Contact Number | Purchase Orders | Status | Created At | Actions 
         -->
        <table>
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Purchase Orders</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $supplier): ?>
                    <!--
                        # Supplier Name | Email | Contact Number | Purchase Orders | Status | Created At | Actions 
                    -->
                    <tr>
                        <td><?= htmlspecialchars($supplier['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                        <td><?= htmlspecialchars($supplier['phone']) ?></td>
                        <td><?= htmlspecialchars($supplier['purchase_orders'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($supplier['supplier_status']) ?></td>
                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($supplier['created_at']))) ?></td>
                        <td>
                            <button onclick="editSupplier(<?= $supplier['id'] ?>)">Edit</button>
                            <button onclick="deleteSupplier(<?= $supplier['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>