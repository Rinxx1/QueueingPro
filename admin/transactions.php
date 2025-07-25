<?php
$pageTitle = "Transactions Management - QueueingPro";
$currentPage = "transactions";
include 'components/header.php';
?>
<link rel="stylesheet" href="transactions/transactions.css">

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-exchange-alt"></i>
        Transactions Management
    </h2>
    <p class="page-description">Monitor and manage all queue transactions, customer processing, and counter activities with real-time analytics.</p>
</div>

<!-- Transaction Statistics -->
<div class="transactions-stats">
    <div class="stat-card stat-info" data-stat="total">
        <div class="stat-value">
            <i class="fas fa-list-ol"></i>
            <span>0</span>
        </div>
        <div class="stat-label">Total Transactions Today</div>
    </div>
    <div class="stat-card stat-success" data-stat="completed">
        <div class="stat-value">
            <i class="fas fa-check-circle"></i>
            <span>0</span>
        </div>
        <div class="stat-label">Completed Today</div>
    </div>
    <div class="stat-card stat-warning" data-stat="progress">
        <div class="stat-value">
            <i class="fas fa-clock"></i>
            <span>0</span>
        </div>
        <div class="stat-label">Currently Processing</div>
    </div>
    <div class="stat-card stat-danger" data-stat="wait-time">
        <div class="stat-value">
            <i class="fas fa-hourglass-half"></i>
            <span>0m</span>
        </div>
        <div class="stat-label">Average Wait Time</div>
    </div>
</div>

<!-- Filter and Actions -->
<div class="filters-card">
    <div class="filters-row">
        <div class="filters-group">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-filter"></i>
                Transaction Filters
            </h3>
            <input type="text" id="searchInput" class="filter-input" placeholder="Search transactions...">
            <select id="statusFilter" class="filter-input">
                <option value="">All Status</option>
                <option value="Awaiting">Awaiting</option>
                <option value="Complete">Complete</option>
            </select>
            <select id="counterFilter" class="filter-input">
                <option value="">All Counters</option>
                <!-- Options loaded via JavaScript -->
            </select>
            <select id="dateRangeFilter" class="filter-input">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>
        <div class="actions-group">
            <button id="exportBtn" class="btn btn-primary">
                <i class="fas fa-download"></i>
                Export Report
            </button>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="transactions-table-container">
    <div class="table-header">
        <h3 class="table-title">
            <i class="fas fa-table"></i>
            Recent Transactions
        </h3>
        <div class="results-count" id="resultsCount">Loading...</div>
    </div>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Queue Number</th>
                <th>Counter</th>
                <th>Operator</th>
                <th>Time Created</th>
                <th>Time Served</th>
                <th>Duration</th>
                <th>Status</th>
                <th>View Details</th>
            </tr>
        </thead>
        <tbody id="transactionsTableBody">
            <!-- Transactions will be loaded here via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Analytics and Reports -->
<div class="analytics-grid">
    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">
                <i class="fas fa-chart-bar"></i>
                Counter Performance
            </h3>
        </div>
        <div class="analytics-content" id="counterAnalyticsContainer">
            <!-- Counter analytics will be loaded here -->
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">
                <i class="fas fa-clock"></i>
                Peak Hours Analysis
            </h3>
        </div>
        <div class="analytics-content">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--success-color);">9-11 AM</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Morning Peak</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--warning-color);">12-2 PM</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Lunch Rush</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.2rem; font-weight: 700; color: var(--accent-color);">4-6 PM</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Evening Peak</div>
                </div>
            </div>
            <div class="hourly-chart" id="hourlyChart">
                <!-- Hourly chart will be rendered here -->
            </div>
        </div>
    </div>

    <div class="analytics-card">
        <div class="analytics-header">
            <h3 class="analytics-title">
                <i class="fas fa-tachometer-alt"></i>
                Processing Efficiency
            </h3>
        </div>
        <div class="analytics-content">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);" id="completionRate">--</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Completion Rate</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);" id="avgServiceTime">--</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Avg Service Time</div>
                </div>
            </div>
            <div class="progress-item">
                <div class="progress-label">Fast Service (< 5 min)</div>
                <div class="progress-value" id="fastServicePercent">--</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="fastServiceBar" style="width: 0%; background: var(--success-color);"></div>
                </div>
            </div>
            <div class="progress-item">
                <div class="progress-label">Standard Service (5-15 min)</div>
                <div class="progress-value" id="standardServicePercent">--</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="standardServiceBar" style="width: 0%; background: var(--accent-color);"></div>
                </div>
            </div>
            <div class="progress-item">
                <div class="progress-label">Slow Service (> 15 min)</div>
                <div class="progress-value" id="slowServicePercent">--</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="slowServiceBar" style="width: 0%; background: var(--warning-color);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="transactions/scripts.js"></script>

<?php include 'components/footer.php'; ?>
