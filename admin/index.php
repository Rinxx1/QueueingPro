<?php
$pageTitle = "Admin Dashboard - QueueingPro";
$currentPage = "dashboard";
include 'components/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-tachometer-alt"></i>
        Dashboard Overview
    </h2>
    <p class="page-description">Monitor and manage your queueing system performance and operations.</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">24</div>
        <div class="stat-label">Customers Served Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">8</div>
        <div class="stat-label">Customers Waiting</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">6</div>
        <div class="stat-label">Active Counters</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">4.2</div>
        <div class="stat-label">Avg Wait Time (min)</div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Quick Actions -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h3>
        </div>
        <div class="quick-actions" style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="counters.php" class="btn btn-primary">
                <i class="fas fa-desktop"></i>
                Manage Counters
            </a>
            <a href="users.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i>
                Add New User
            </a>
            <a href="../QueDisplay.php" class="btn btn-warning">
                <i class="fas fa-tv"></i>
                View Display
            </a>
        </div>
    </div>

    <!-- System Status -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-heartbeat"></i>
                System Status
            </h3>
        </div>
        <div class="system-status">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span>Queue System</span>
                <span class="status-badge status-active">Online</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span>Display Screen</span>
                <span class="status-badge status-active">Connected</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span>Audio System</span>
                <span class="status-badge status-active">Working</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Database</span>
                <span class="status-badge status-active">Connected</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clock"></i>
                Recent Activity
            </h3>
        </div>
        <div class="recent-activity">
            <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                <div style="font-weight: 600; color: var(--primary-color);">A025 - General Inquiry</div>
                <div style="font-size: 0.85rem; color: var(--medium-gray);">Served at Counter 1 • 2 minutes ago</div>
            </div>
            <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                <div style="font-weight: 600; color: var(--primary-color);">A024 - Account Opening</div>
                <div style="font-size: 0.85rem; color: var(--medium-gray);">Served at Counter 2 • 5 minutes ago</div>
            </div>
            <div style="padding: 0.8rem 0; border-bottom: 1px solid var(--border-color);">
                <div style="font-weight: 600; color: var(--primary-color);">A023 - Loan Application</div>
                <div style="font-size: 0.85rem; color: var(--medium-gray);">Served at Counter 3 • 8 minutes ago</div>
            </div>
            <div style="padding: 0.8rem 0;">
                <div style="font-weight: 600; color: var(--primary-color);">A022 - Card Services</div>
                <div style="font-size: 0.85rem; color: var(--medium-gray);">Served at Counter 4 • 12 minutes ago</div>
            </div>
        </div>
    </div>

    <!-- Performance Chart Placeholder -->
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Today's Performance
            </h3>
        </div>
        <div class="performance-summary">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">95%</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Service Efficiency</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">3.8</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Customer Rating</div>
                </div>
            </div>
            <div style="background: var(--light-gray); padding: 1rem; border-radius: 8px; text-align: center;">
                <i class="fas fa-chart-bar" style="font-size: 2rem; color: var(--medium-gray); margin-bottom: 0.5rem;"></i>
                <div style="color: var(--medium-gray); font-size: 0.9rem;">Performance chart will be displayed here</div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
