<?php
$pageTitle = "Transactions Management - QueueingPro";
$currentPage = "transactions";
include 'components/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-exchange-alt"></i>
        Transactions Management
    </h2>
    <p class="page-description">Monitor and manage all queue transactions, service types, and customer interactions.</p>
</div>

<!-- Transaction Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">156</div>
        <div class="stat-label">Total Transactions Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">24</div>
        <div class="stat-label">Completed This Hour</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">8</div>
        <div class="stat-label">Currently Processing</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">12</div>
        <div class="stat-label">Average Wait Time (min)</div>
    </div>
</div>

<!-- Filter and Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-filter"></i>
                Transaction Filters
            </h3>
            <select style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option>All Services</option>
                <option>General Inquiry</option>
                <option>Account Services</option>
                <option>Loan Services</option>
                <option>Card Services</option>
                <option>Deposits</option>
                <option>Withdrawals</option>
            </select>
            <select style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option>Today</option>
                <option>This Week</option>
                <option>This Month</option>
                <option>Custom Range</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-primary">
                <i class="fas fa-download"></i>
                Export Report
            </button>
            <button class="btn btn-success">
                <i class="fas fa-plus"></i>
                Manual Entry
            </button>
        </div>
    </div>
</div>

<!-- Recent Transactions Table -->
<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Queue Number</th>
                <th>Service Type</th>
                <th>Counter</th>
                <th>Operator</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#T001256</td>
                <td>A025</td>
                <td>General Inquiry</td>
                <td>Counter 1</td>
                <td>John Doe</td>
                <td>14:30:15</td>
                <td>14:33:45</td>
                <td>3m 30s</td>
                <td><span class="status-badge status-active">Completed</span></td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                </td>
            </tr>
            <tr>
                <td>#T001255</td>
                <td>B012</td>
                <td>Account Services</td>
                <td>Counter 2</td>
                <td>Jane Smith</td>
                <td>14:25:20</td>
                <td>14:32:10</td>
                <td>6m 50s</td>
                <td><span class="status-badge status-active">Completed</span></td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                </td>
            </tr>
            <tr>
                <td>#T001254</td>
                <td>A026</td>
                <td>General Inquiry</td>
                <td>Counter 1</td>
                <td>John Doe</td>
                <td>14:35:00</td>
                <td>-</td>
                <td>2m 15s</td>
                <td><span class="status-badge status-break">In Progress</span></td>
                <td>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-stop"></i>
                        End
                    </button>
                </td>
            </tr>
            <tr>
                <td>#T001253</td>
                <td>C008</td>
                <td>Loan Services</td>
                <td>Counter 3</td>
                <td>Mike Johnson</td>
                <td>14:20:30</td>
                <td>14:28:45</td>
                <td>8m 15s</td>
                <td><span class="status-badge status-active">Completed</span></td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                </td>
            </tr>
            <tr>
                <td>#T001252</td>
                <td>D015</td>
                <td>Card Services</td>
                <td>Counter 4</td>
                <td>Sarah Wilson</td>
                <td>14:33:20</td>
                <td>-</td>
                <td>4m 05s</td>
                <td><span class="status-badge status-break">In Progress</span></td>
                <td>
                    <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-stop"></i>
                        End
                    </button>
                </td>
            </tr>
            <tr>
                <td>#T001251</td>
                <td>E020</td>
                <td>Deposits</td>
                <td>Counter 5</td>
                <td>David Brown</td>
                <td>14:18:45</td>
                <td>14:22:30</td>
                <td>3m 45s</td>
                <td><span class="status-badge status-active">Completed</span></td>
                <td>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Analytics and Reports -->
<div class="content-grid">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Service Type Analytics
            </h3>
        </div>
        <div class="service-analytics">
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">General Inquiry</span>
                    <span style="font-weight: 600; color: var(--accent-color);">45%</span>
                </div>
                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--accent-color); height: 100%; width: 45%;"></div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Account Services</span>
                    <span style="font-weight: 600; color: var(--success-color);">25%</span>
                </div>
                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--success-color); height: 100%; width: 25%;"></div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Loan Services</span>
                    <span style="font-weight: 600; color: var(--warning-color);">15%</span>
                </div>
                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--warning-color); height: 100%; width: 15%;"></div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Card Services</span>
                    <span style="font-weight: 600; color: var(--info-color);">10%</span>
                </div>
                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--info-color); height: 100%; width: 10%;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Others</span>
                    <span style="font-weight: 600; color: var(--danger-color);">5%</span>
                </div>
                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; overflow: hidden;">
                    <div style="background: var(--danger-color); height: 100%; width: 5%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clock"></i>
                Peak Hours Analysis
            </h3>
        </div>
        <div class="peak-hours">
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
            <div style="background: var(--light-gray); padding: 1rem; border-radius: 8px; text-align: center;">
                <i class="fas fa-chart-area" style="font-size: 2rem; color: var(--medium-gray); margin-bottom: 0.5rem;"></i>
                <div style="color: var(--medium-gray); font-size: 0.9rem;">Hourly traffic analysis will be displayed here</div>
            </div>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-star"></i>
                Service Quality Metrics
            </h3>
        </div>
        <div class="quality-metrics">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">4.2/5</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Customer Rating</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">92%</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Resolution Rate</div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Excellent (5★)</span>
                    <span style="font-weight: 600; color: var(--success-color);">65%</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--success-color); height: 100%; width: 65%;"></div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Good (4★)</span>
                    <span style="font-weight: 600; color: var(--accent-color);">25%</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--accent-color); height: 100%; width: 25%;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Average (3★ & below)</span>
                    <span style="font-weight: 600; color: var(--warning-color);">10%</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--warning-color); height: 100%; width: 10%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
