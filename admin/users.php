<?php
$pageTitle = "Users Management - QueueingPro";
$currentPage = "users";
include 'components/header.php';
?>
<link rel="stylesheet" href="users/users.css">
<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-users"></i>
        Users Management
    </h2>
    <p class="page-description">Manage system users, operators, administrators, and access permissions.</p>
</div>

<!-- User Management Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-search"></i>
                User Search & Filter
            </h3>
            <input type="text" id="searchInput" placeholder="Search users..." style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; min-width: 200px;">
            <select id="roleFilter" style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option value="">All Roles</option>
                <option value="Administrator">Administrator</option>
                <option value="Controller">Controller</option>
            </select>
        </div>
        <button id="addUserBtn" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Add New User
        </button>
    </div>
</div>

<!-- Users Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Counter</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <!-- Users will be loaded here via JavaScript -->
        </tbody>
    </table>
</div>

<!-- User Modal -->
<div id="userModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New User</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="userForm">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label for="firstname">First Name *</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div>
                        <label for="lastname">Last Name *</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div id="passwordField" style="margin-bottom: 1rem;">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: var(--medium-gray);">Leave blank to keep current password (for editing)</small>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label for="user_level">Role *</label>
                        <select id="user_level" name="user_level" required>
                            <option value="0">Administrator</option>
                            <option value="1" selected>Controller</option>
                        </select>
                    </div>
                    <div>
                        <label for="counter_id">Counter Assignment</label>
                        <select id="counter_id" name="counter_id" required>
                            <!-- Options loaded via JavaScript -->
                        </select>
                    </div>
                </div>
                <div id="statusField" style="margin-bottom: 1rem; display: none;">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save User</button>
            </div>
        </form>
    </div>
</div>


<script src="users/scripts.js"></script>


 

<?php include 'components/footer.php'; ?>
