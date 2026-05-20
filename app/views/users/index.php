<?php
$users = $data['users'] ?? [];
ob_start();
?>

<div class="container">
    <div class="container-header">
        <h2>Users</h2>
        <button onclick="openModal()">+ Add new user</button>
    </div>

    <!-- # Users List-->
    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php endif; ?>
    <?php if (!empty($users)): ?>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?= $user['full_name']; ?>
                        </td>
                        <td>
                            <?= $user['email']; ?>
                        </td>
                        <td>
                            <?= $user['role']; ?>
                        </td>
                        <td class="userStatus" data-userId="<?= $user['id'] ?>">
                            <?= $user['user_status']; ?>
                        </td>
                        <td>
                            <div data-userId="<?= $user['id'] ?>"
                                class="userActions <?= $user['user_status'] === 'INACTIVE' || $user['role'] === 'ADMIN' ? 'hide' : '' ?>">


                                <button data-userId="<?= $user['id'] ?>" onclick="deleteUser(this)">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>
</div>

<!-- 
# ADD NEW USER
-->
<!-- # Add User Modal -->

<div id="modal" class="modal user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add user</h3>
            <span class="close" onclick="closeModal()">×</span>
        </div>

        <form class="form user-form" action="users/form-submit" method="post">

            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="Enter full name" required />
            </div>
            <div class="form-group">
                <div>
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter email" required />
                </div>
                <div>

                    <label>password</label>
                    <input type="password" name="password" placeholder="Enter password" required />
                </div>
            </div>

            <div>
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select user role</option>
                    <option value="STAFF">Staff</option>
                </select>
            </div>

            <button type="submit">Add new user</button>
        </form>
    </div>
</div>


<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/layout.php';
?>