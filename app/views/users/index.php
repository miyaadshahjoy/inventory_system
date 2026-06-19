<?php
$users = $data["users"] ?? [];
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
        <div class="table-wrapper">
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
                                <?= $user["full_name"] ?>
                            </td>
                            <td>
                                <?= $user["email"] ?>
                            </td>
                            <td>
                                <?= $user["role"] ?>
                            </td>
                            <td class="userStatus" data-userId="<?= $user[
                                "id"
                            ] ?>">
                                <?= $user["user_status"] ?>
                            </td>
                            <td>
                                <div data-userId="<?= $user["id"] ?>"
                                    class="userActions <?= $user[
                                        "user_status"
                                    ] === "INACTIVE" ||
                                    $user["role"] === "ADMIN"
                                        ? "hide"
                                        : "" ?>">


                                    <button data-userId="<?= $user[
                                        "id"
                                    ] ?>" onclick="deleteUser(this)">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-6"
                                            height="24px"
                                            width="24px"
                                        >
                                            <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                            />
                                        </svg>

                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

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
require_once __DIR__ . "/../layouts/layout.php";


?>
