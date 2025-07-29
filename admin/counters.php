<?php
$pageTitle = "Counters Management - QueueingPro";
$currentPage = "counters";
include 'components/header.php';
?>
<link rel="stylesheet" href="counters/counters.css">

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
    <div class="stat-card" data-stat="total">
        <div class="stat-value">0</div>
        <div class="stat-label">Total Counters</div>
    </div>
    <div class="stat-card" data-stat="active">
        <div class="stat-value">0</div>
        <div class="stat-label">Active Counters</div>
    </div>
    <div class="stat-card" data-stat="break">
        <div class="stat-value">0</div>
        <div class="stat-label">On Break</div>
    </div>
    <div class="stat-card" data-stat="offline">
        <div class="stat-value">0</div>
        <div class="stat-label">Offline</div>
    </div>
</div>

<!-- Counter Management Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-search"></i>
                Counter Search & Filter
            </h3>
            <input type="text" id="searchInput" placeholder="Search counters..." style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; min-width: 200px;">
            <select id="statusFilter" style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Break">On Break</option>
                <option value="Offline">Offline</option>
            </select>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button id="resetQueueBtn" class="btn btn-warning">
                <i class="fas fa-redo"></i>
                Reset Queue Numbers
            </button>
            <button id="addCounterBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Add New Counter
            </button>
        </div>
    </div>
</div>

<!-- Counters Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Counter ID</th>
                <th>Counter Name</th>
                <th>Description</th>
                <th>Current Number</th>
                <th>Operator</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="countersTableBody">
            <!-- Counters will be loaded here via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Counter Modal -->
<div id="counterModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Counter</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="counterForm">
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <label for="counter_name">Counter Name *</label>
                    <input type="text" id="counter_name" name="counter_name" required placeholder="e.g., Service Counter 1, Information Desk">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="counter_description">Counter Description</label>
                    <textarea id="counter_description" name="counter_description" rows="3" placeholder="Describe the services provided by this counter..." style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; resize: vertical;"></textarea>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="current_number">Current Number *</label>
                    <input type="text" id="current_number" name="current_number" required placeholder="e.g., A000, B000" readonly style="background-color: #f8f9fa;">
                    <small style="color: #6c757d; font-size: 0.875rem;">Auto-generated alphabetically (A000, B000, etc.)</small>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Break">On Break</option>
                            <option value="Offline">Offline</option>
                        </select>
                    </div>
                    <div>
                        <label for="user_id">Assign Operator</label>
                        <select id="user_id" name="user_id">
                            <!-- Options loaded via JavaScript -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Counter</button>
            </div>
        </form>
    </div>
</div>

<!-- Awaiting Queue Table -->
<div class="content-card" style="margin-bottom: 2rem; margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-clock"></i>
                Awaiting Queue
            </h3>
            <input type="text" id="awaitingSearchInput" placeholder="Search by counter name..." style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; min-width: 200px;">
            <select id="awaitingStatusFilter" style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option value="">All Counter Status</option>
                <option value="Active">Active</option>
                <option value="Break">On Break</option>
                <option value="Offline">Offline</option>
            </select>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Awaiting ID</th>
                <th>Awaiting Number</th>
                <th>Counter Name</th>
                <th>Counter Current Number</th>
                <th>Counter Status</th>
            </tr>
        </thead>
        <tbody id="awaitingTableBody">
            <!-- Awaiting entries will be loaded here via JavaScript -->
        </tbody>
    </table>
</div>

<script src="counters/scripts.js"></script>

<?php include 'components/footer.php'; ?>
