<?php
$pageTitle = "Users Management - QueueingPro";
$currentPage = "users";
include 'components/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-users"></i>
        Users Management
    </h2>
    <p class="page-description">Manage system users, operators, administrators, and access permissions.</p>
</div>

<!-- User Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">15</div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">8</div>
        <div class="stat-label">Active Operators</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">3</div>
        <div class="stat-label">Administrators</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">2</div>
        <div class="stat-label">Pending Approval</div>
    </div>
</div>

<!-- User Management Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-search"></i>
                User Search & Filter
            </h3>
            <input type="text" placeholder="Search users..." style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; min-width: 200px;">
            <select style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option>All Roles</option>
                <option>Administrator</option>
                <option>Operator</option>
                <option>Supervisor</option>
            </select>
        </div>
        <button class="btn btn-primary">
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
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Department</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#U001</td>
                <td>John Doe</td>
                <td>john.doe</td>
                <td>john.doe@bank.com</td>
                <td><span style="background: var(--accent-color); color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Operator</span></td>
                <td>Customer Service</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>2 hours ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-ban"></i>
                        Disable
                    </button>
                </td>
            </tr>
            <tr>
                <td>#U002</td>
                <td>Jane Smith</td>
                <td>jane.smith</td>
                <td>jane.smith@bank.com</td>
                <td><span style="background: var(--accent-color); color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Operator</span></td>
                <td>Account Services</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>1 hour ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-ban"></i>
                        Disable
                    </button>
                </td>
            </tr>
            <tr>
                <td>#U003</td>
                <td>Mike Johnson</td>
                <td>mike.johnson</td>
                <td>mike.johnson@bank.com</td>
                <td><span style="background: var(--warning-color); color: var(--primary-color); padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Supervisor</span></td>
                <td>Loan Services</td>
                <td><span class="status-badge status-break">On Break</span></td>
                <td>30 min ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-check"></i>
                        Activate
                    </button>
                </td>
            </tr>
            <tr>
                <td>#U004</td>
                <td>Sarah Wilson</td>
                <td>sarah.wilson</td>
                <td>sarah.wilson@bank.com</td>
                <td><span style="background: var(--accent-color); color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Operator</span></td>
                <td>Card Services</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>15 min ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-ban"></i>
                        Disable
                    </button>
                </td>
            </tr>
            <tr>
                <td>#U005</td>
                <td>David Brown</td>
                <td>david.brown</td>
                <td>david.brown@bank.com</td>
                <td><span style="background: var(--accent-color); color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Operator</span></td>
                <td>Treasury</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>45 min ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-ban"></i>
                        Disable
                    </button>
                </td>
            </tr>
            <tr>
                <td>#U006</td>
                <td>Lisa Anderson</td>
                <td>lisa.anderson</td>
                <td>lisa.anderson@bank.com</td>
                <td><span style="background: var(--danger-color); color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">Administrator</span></td>
                <td>IT Department</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>5 min ago</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-ban"></i>
                        Disable
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- User Management Tools -->
<div class="content-grid">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus"></i>
                Add New User
            </h3>
        </div>
        <div class="user-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">First Name:</label>
                    <input type="text" placeholder="Enter first name" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Last Name:</label>
                    <input type="text" placeholder="Enter last name" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Username:</label>
                <input type="text" placeholder="Enter username" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Email:</label>
                <input type="email" placeholder="Enter email address" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Role:</label>
                    <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        <option>Select Role</option>
                        <option>Administrator</option>
                        <option>Supervisor</option>
                        <option>Operator</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Department:</label>
                    <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        <option>Select Department</option>
                        <option>Customer Service</option>
                        <option>Account Services</option>
                        <option>Loan Services</option>
                        <option>Card Services</option>
                        <option>Treasury</option>
                        <option>IT Department</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Password:</label>
                <input type="password" placeholder="Enter password" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
            </div>
            <button class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-user-plus"></i>
                Create User
            </button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-shield-alt"></i>
                Role Permissions
            </h3>
        </div>
        <div class="permissions">
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.8rem;">Administrator</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Full System Access</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>User Management</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>System Configuration</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Reports & Analytics</li>
                </ul>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.8rem;">Supervisor</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Counter Management</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Operator Oversight</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Basic Reports</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-times" style="color: var(--danger-color); margin-right: 0.5rem;"></i>System Configuration</li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 0.8rem;">Operator</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Counter Operation</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-check" style="color: var(--success-color); margin-right: 0.5rem;"></i>Queue Management</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-times" style="color: var(--danger-color); margin-right: 0.5rem;"></i>User Management</li>
                    <li style="padding: 0.4rem 0; color: var(--medium-gray);"><i class="fas fa-times" style="color: var(--danger-color); margin-right: 0.5rem;"></i>System Configuration</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i>
                User Activity Summary
            </h3>
        </div>
        <div class="activity-summary">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">6</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Active Now</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">12</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Today's Logins</div>
                </div>
            </div>
            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Recent Activity</h4>
            <div style="max-height: 200px; overflow-y: auto;">
                <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                    <div style="font-weight: 600; color: var(--primary-color);">John Doe logged in</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Counter 1 • 2 hours ago</div>
                </div>
                <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                    <div style="font-weight: 600; color: var(--primary-color);">Jane Smith completed 15 transactions</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Counter 2 • 3 hours ago</div>
                </div>
                <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                    <div style="font-weight: 600; color: var(--primary-color);">Mike Johnson went on break</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Counter 3 • 4 hours ago</div>
                </div>
                <div style="padding: 0.8rem 0;">
                    <div style="font-weight: 600; color: var(--primary-color);">Sarah Wilson logged out</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Counter 4 • 5 hours ago</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
