<?php
$pageTitle = "Counters Management - QueueingPro";
$currentPage = "counters";
include 'components/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-desktop"></i>
        Counters Management
    </h2>
    <p class="page-description">Configure and monitor all service counters, assign services, and track performance.</p>
</div>

<!-- Counter Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">6</div>
        <div class="stat-label">Total Counters</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">5</div>
        <div class="stat-label">Active Counters</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">1</div>
        <div class="stat-label">On Break</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">0</div>
        <div class="stat-label">Offline</div>
    </div>
</div>

<!-- Actions Bar -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; color: var(--primary-color);">
            <i class="fas fa-list"></i>
            Counter Overview
        </h3>
        <button class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Counter
        </button>
    </div>
</div>

<!-- Counters Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Counter ID</th>
                <th>Counter Name</th>
                <th>Service Type</th>
                <th>Operator</th>
                <th>Status</th>
                <th>Current Number</th>
                <th>Queue Length</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#001</td>
                <td>Counter 1</td>
                <td>General Inquiry</td>
                <td>John Doe</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>A025</td>
                <td>3 customers</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                </td>
            </tr>
            <tr>
                <td>#002</td>
                <td>Counter 2</td>
                <td>Account Services</td>
                <td>Jane Smith</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>B012</td>
                <td>2 customers</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                </td>
            </tr>
            <tr>
                <td>#003</td>
                <td>Counter 3</td>
                <td>Loan Services</td>
                <td>Mike Johnson</td>
                <td><span class="status-badge status-break">On Break</span></td>
                <td>C008</td>
                <td>5 customers</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-play"></i>
                        Resume
                    </button>
                </td>
            </tr>
            <tr>
                <td>#004</td>
                <td>Counter 4</td>
                <td>Card Services</td>
                <td>Sarah Wilson</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>D015</td>
                <td>1 customer</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                </td>
            </tr>
            <tr>
                <td>#005</td>
                <td>Counter 5</td>
                <td>Deposits</td>
                <td>David Brown</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>E020</td>
                <td>4 customers</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                </td>
            </tr>
            <tr>
                <td>#006</td>
                <td>Counter 6</td>
                <td>Withdrawals</td>
                <td>Lisa Anderson</td>
                <td><span class="status-badge status-active">Active</span></td>
                <td>F005</td>
                <td>2 customers</td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin-left: 0.5rem;">
                        <i class="fas fa-pause"></i>
                        Pause
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Counter Configuration -->
<div class="content-grid">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-cogs"></i>
                Counter Configuration
            </h3>
        </div>
        <div class="config-form">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Select Counter:</label>
                <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                    <option>Counter 1 - General Inquiry</option>
                    <option>Counter 2 - Account Services</option>
                    <option>Counter 3 - Loan Services</option>
                    <option>Counter 4 - Card Services</option>
                    <option>Counter 5 - Deposits</option>
                    <option>Counter 6 - Withdrawals</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Service Type:</label>
                <input type="text" placeholder="Enter service type" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Assign Operator:</label>
                <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                    <option>Select Operator</option>
                    <option>John Doe</option>
                    <option>Jane Smith</option>
                    <option>Mike Johnson</option>
                    <option>Sarah Wilson</option>
                    <option>David Brown</option>
                    <option>Lisa Anderson</option>
                </select>
            </div>
            <button class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-save"></i>
                Save Configuration
            </button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                Counter Performance
            </h3>
        </div>
        <div class="performance-metrics">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">24</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Customers Served</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">3.5 min</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Avg Service Time</div>
                </div>
            </div>
            <div style="background: var(--light-gray); padding: 1rem; border-radius: 8px; text-align: center;">
                <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--medium-gray); margin-bottom: 0.5rem;"></i>
                <div style="color: var(--medium-gray); font-size: 0.9rem;">Performance analytics will be displayed here</div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
