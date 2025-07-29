document.addEventListener('DOMContentLoaded', function() {
    let currentTransactions = [];
    let currentFilters = {
        status: '',
        counter_id: '',
        date_range: 'today',
        search: ''
    };

    // Initialize page
    initializePage();
    
    // Event listeners
    setupEventListeners();
    
    async function initializePage() {
        try {
            // Show loading state
            showLoading();
            
            // Load initial data
            await Promise.all([
                loadStats(),
                loadTransactions(),
                loadCounterAnalytics(),
                loadDropdownOptions()
            ]);
            
            // Hide loading state
            hideLoading();
        } catch (error) {
            console.error('Error initializing page:', error);
            showError('Failed to load page data');
        }
    }
    
    function setupEventListeners() {
        // Filter controls
        document.getElementById('statusFilter').addEventListener('change', handleFilterChange);
        document.getElementById('counterFilter').addEventListener('change', handleFilterChange);
        document.getElementById('dateRangeFilter').addEventListener('change', handleFilterChange);
        document.getElementById('searchInput').addEventListener('input', debounce(handleFilterChange, 300));
        
        // Action buttons
        document.getElementById('exportBtn').addEventListener('click', exportTransactions);
        
        // Auto-refresh every 30 seconds
        setInterval(refreshData, 30000);
    }
    
    async function loadStats() {
        try {
            const response = await fetch('transactions/ajax.php?action=get_stats&period=' + currentFilters.date_range);
            const result = await response.json();
            
            if (result.success) {
                updateStatsDisplay(result.data);
            } else {
                console.error('Failed to load stats:', result.message);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
    
    async function loadTransactions() {
        try {
            const queryParams = new URLSearchParams(currentFilters);
            const response = await fetch('transactions/ajax.php?action=get_transactions&' + queryParams);
            const result = await response.json();
            
            if (result.success) {
                currentTransactions = result.data;
                displayTransactions(result.data);
                updateResultsCount(result.data.length);
            } else {
                console.error('Failed to load transactions:', result.message);
                displayTransactions([]);
            }
        } catch (error) {
            console.error('Error loading transactions:', error);
            displayTransactions([]);
        }
    }
    
    async function loadCounterAnalytics() {
        try {
            const response = await fetch('transactions/ajax.php?action=get_counter_analytics&period=' + currentFilters.date_range);
            const result = await response.json();
            
            if (result.success) {
                updateCounterAnalytics(result.data);
            } else {
                console.error('Failed to load counter analytics:', result.message);
            }
        } catch (error) {
            console.error('Error loading counter analytics:', error);
        }
    }
    
    async function loadDropdownOptions() {
        try {
            // Load counters
            const counterResponse = await fetch('transactions/ajax.php?action=get_counters');
            const counterResult = await counterResponse.json();
            if (counterResult.success) {
                populateCounterDropdowns(counterResult.data);
            }
            
            // Load operators
            const operatorResponse = await fetch('transactions/ajax.php?action=get_operators');
            const operatorResult = await operatorResponse.json();
            if (operatorResult.success) {
                populateOperatorDropdowns(operatorResult.data);
            }
        } catch (error) {
            console.error('Error loading dropdown options:', error);
        }
    }
    
    function updateStatsDisplay(stats) {
        document.querySelector('[data-stat="total"] .stat-value').textContent = stats.total_today || 0;
        document.querySelector('[data-stat="completed"] .stat-value').textContent = stats.completed_today || 0;
        document.querySelector('[data-stat="progress"] .stat-value').textContent = stats.waiting || 0;
        document.querySelector('[data-stat="wait-time"] .stat-value').textContent = (stats.average_wait_time || 0) + 'm';
        
        // Update efficiency metrics
        if (stats.efficiency) {
            const completionRate = document.getElementById('completionRate');
            const avgServiceTime = document.getElementById('avgServiceTime');
            const fastServicePercent = document.getElementById('fastServicePercent');
            const standardServicePercent = document.getElementById('standardServicePercent');
            const slowServicePercent = document.getElementById('slowServicePercent');
            const fastServiceBar = document.getElementById('fastServiceBar');
            const standardServiceBar = document.getElementById('standardServiceBar');
            const slowServiceBar = document.getElementById('slowServiceBar');
            
            if (completionRate) completionRate.textContent = (stats.efficiency.completion_rate || 0) + '%';
            if (avgServiceTime) avgServiceTime.textContent = (stats.efficiency.avg_service_time || 0) + 'm';
            if (fastServicePercent) fastServicePercent.textContent = (stats.efficiency.fast_service || 0) + '%';
            if (standardServicePercent) standardServicePercent.textContent = (stats.efficiency.standard_service || 0) + '%';
            if (slowServicePercent) slowServicePercent.textContent = (stats.efficiency.slow_service || 0) + '%';
            if (fastServiceBar) fastServiceBar.style.width = (stats.efficiency.fast_service || 0) + '%';
            if (standardServiceBar) standardServiceBar.style.width = (stats.efficiency.standard_service || 0) + '%';
            if (slowServiceBar) slowServiceBar.style.width = (stats.efficiency.slow_service || 0) + '%';
        }
    }
    
    function displayTransactions(transactions) {
        const tbody = document.getElementById('transactionsTableBody');
        tbody.innerHTML = '';
        
        if (transactions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="no-data-message">
                        <div class="no-data-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div>No transactions found matching your criteria</div>
                    </td>
                </tr>
            `;
            return;
        }
        
        transactions.forEach(transaction => {
            const row = createTransactionRow(transaction);
            tbody.appendChild(row);
        });
    }
    
    function createTransactionRow(transaction) {
        const row = document.createElement('tr');
        
        const statusClass = getStatusClass(transaction.status);
        
        row.innerHTML = `
            <td>
                <span class="queue-number">${transaction.queue_number}</span>
            </td>
            <td>${transaction.Counter_Name || 'N/A'}</td>
            <td>${transaction.operator_name || 'Unassigned'}</td>
            <td>${formatDateTime(transaction.time_created)}</td>
            <td>${formatDateTime(transaction.time_served)}</td>
            <td>
                <span class="duration-display">${transaction.formatted_duration}</span>
            </td>
            <td>
                <span class="status-badge ${statusClass}">${transaction.status}</span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-view" onclick="viewTransaction('${transaction.transaction_id}')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${getActionButtons(transaction)}
                </div>
            </td>
        `;
        
        return row;
    }
    
    function getActionButtons(transaction) {
        // Only show view button for transaction logs
        return `
            <button class="btn-action btn-view" onclick="viewTransaction('${transaction.transaction_id}')" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
        `;
    }
    
    function updateCounterAnalytics(analytics) {
        const container = document.getElementById('counterAnalyticsContainer');
        container.innerHTML = '';
        
        if (analytics.length === 0) {
            container.innerHTML = `
                <div class="no-data-message">
                    <div class="no-data-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div>No counter data available</div>
                </div>
            `;
            return;
        }
        
        analytics.forEach(counter => {
            const item = document.createElement('div');
            item.className = 'progress-item';
            item.innerHTML = `
                <div class="progress-label">${counter.Counter_Name}</div>
                <div class="progress-value">${counter.count} transactions</div>
                <div class="progress-bar" style="width: 150px; margin-left: 1rem;">
                    <div class="progress-fill" style="width: ${counter.percentage}%; background: var(--primary-color);"></div>
                </div>
            `;
            container.appendChild(item);
        });
    }
    
    function populateCounterDropdowns(counters) {
        const filterSelect = document.getElementById('counterFilter');
        const modalSelect = document.getElementById('modalCounterId');
        
        [filterSelect, modalSelect].forEach(select => {
            if (select) {
                // Keep the first option
                const firstOption = select.children[0];
                select.innerHTML = '';
                select.appendChild(firstOption);
                
                counters.forEach(counter => {
                    const option = document.createElement('option');
                    option.value = counter.Counter_ID;
                    option.textContent = counter.Counter_Name;
                    select.appendChild(option);
                });
            }
        });
    }
    
    function populateOperatorDropdowns(operators) {
        const modalSelect = document.getElementById('modalOperatorId');
        
        if (modalSelect) {
            // Keep the first option
            const firstOption = modalSelect.children[0];
            modalSelect.innerHTML = '';
            modalSelect.appendChild(firstOption);
            
            operators.forEach(operator => {
                const option = document.createElement('option');
                option.value = operator.User_ID;
                option.textContent = operator.full_name;
                modalSelect.appendChild(option);
            });
        }
    }
    
    function handleFilterChange() {
        // Update current filters
        currentFilters.status = document.getElementById('statusFilter').value;
        currentFilters.counter_id = document.getElementById('counterFilter').value;
        currentFilters.date_range = document.getElementById('dateRangeFilter').value;
        currentFilters.search = document.getElementById('searchInput').value.trim();
        
        // Reload data
        loadTransactions();
        loadStats();
        loadCounterAnalytics();
    }
    
    async function refreshData() {
        await Promise.all([
            loadStats(),
            loadTransactions(),
            loadCounterAnalytics()
        ]);
    }
    
    // Transaction Actions
    window.viewTransaction = async function(transactionId) {
        try {
            const response = await fetch(`transactions/ajax.php?action=get_transaction&transaction_id=${transactionId}`);
            const result = await response.json();
            
            if (result.success) {
                showTransactionDetails(result.data);
            } else {
                await Swal.fire('Error', 'Transaction not found', 'error');
            }
        } catch (error) {
            console.error('Error viewing transaction:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    window.startTransaction = async function(transactionId) {
        try {
            const result = await Swal.fire({
                title: 'Complete Service',
                text: 'Are you sure you want to mark this transaction as completed?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Complete',
                cancelButtonText: 'Cancel'
            });
            
            if (result.isConfirmed) {
                // Find the awaiting ID from current transactions
                const transaction = currentTransactions.find(t => t.transaction_id == transactionId);
                if (!transaction) {
                    await Swal.fire('Error', 'Transaction not found', 'error');
                    return;
                }
                
                // For now, we'll use the awaiting table ID if available
                const response = await fetch('transactions/ajax.php?action=complete_transaction', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `awaiting_id=${transaction.awaiting_id || transactionId}&duration=5`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire('Success', result.message, 'success');
                    refreshData();
                } else {
                    await Swal.fire('Error', result.message, 'error');
                }
            }
        } catch (error) {
            console.error('Error completing transaction:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    window.cancelTransaction = async function(transactionId) {
        try {
            const result = await Swal.fire({
                title: 'Cancel Transaction',
                text: 'Are you sure you want to cancel this transaction?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No'
            });
            
            if (result.isConfirmed) {
                // Find the awaiting ID from current transactions
                const transaction = currentTransactions.find(t => t.transaction_id == transactionId);
                if (!transaction) {
                    await Swal.fire('Error', 'Transaction not found', 'error');
                    return;
                }
                
                const response = await fetch('transactions/ajax.php?action=cancel_transaction', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `awaiting_id=${transaction.awaiting_id || transactionId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire('Success', result.message, 'success');
                    refreshData();
                } else {
                    await Swal.fire('Error', result.message, 'error');
                }
            }
        } catch (error) {
            console.error('Error cancelling transaction:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    window.editOperator = async function(transactionId) {
        try {
            // Get available operators
            const operatorResponse = await fetch('transactions/ajax.php?action=get_operators');
            const operatorResult = await operatorResponse.json();
            
            if (!operatorResult.success) {
                await Swal.fire('Error', 'Failed to load operators', 'error');
                return;
            }
            
            const operators = operatorResult.data;
            const options = {};
            operators.forEach(op => {
                options[op.User_ID] = op.full_name;
            });
            options[''] = 'Unassigned';
            
            const { value: operatorId } = await Swal.fire({
                title: 'Assign Operator',
                input: 'select',
                inputOptions: options,
                inputPlaceholder: 'Select an operator',
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel'
            });
            
            if (operatorId !== undefined) {
                const response = await fetch('transactions/ajax.php?action=update_operator', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `transaction_id=${transactionId}&user_id=${operatorId}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    await Swal.fire('Success', result.message, 'success');
                    refreshData();
                } else {
                    await Swal.fire('Error', result.message, 'error');
                }
            }
        } catch (error) {
            console.error('Error updating operator:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    // Transaction adding and modal functions removed - this is now a read-only log
    
    async function exportTransactions() {
        try {
            const queryParams = new URLSearchParams(currentFilters);
            queryParams.append('export', 'csv');
            
            // Create downloadable CSV
            const transactions = currentTransactions;
            if (transactions.length === 0) {
                await Swal.fire('Info', 'No transactions to export', 'info');
                return;
            }
            
            const csv = generateCSV(transactions);
            downloadCSV(csv, 'transactions_export.csv');
            
            await Swal.fire('Success', 'Transactions exported successfully', 'success');
        } catch (error) {
            console.error('Error exporting transactions:', error);
            await Swal.fire('Error', 'Export failed', 'error');
        }
    }
    
    function generateCSV(transactions) {
        const headers = [
            'Queue Number', 'Counter', 'Operator', 'Time Created', 'Time Served',
            'Duration', 'Status'
        ];
        
        const rows = transactions.map(t => [
            t.queue_number,
            t.Counter_Name || 'N/A',
            t.operator_name || 'Unassigned',
            formatDateTime(t.time_created),
            formatDateTime(t.time_served),
            t.formatted_duration,
            t.status
        ]);
        
        return [headers, ...rows].map(row => 
            row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',')
        ).join('\n');
    }
    
    function downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    }
    
    function showTransactionDetails(transaction) {
        Swal.fire({
            title: `Transaction Details - ${transaction.queue_number}`,
            html: `
                <div style="text-align: left;">
                    <p><strong>Queue Number:</strong> ${transaction.queue_number}</p>
                    <p><strong>Status:</strong> <span class="status-badge ${getStatusClass(transaction.status)}">${transaction.status}</span></p>
                    <p><strong>Counter:</strong> ${transaction.Counter_Name || 'N/A'}</p>
                    <p><strong>Operator:</strong> ${transaction.operator_name || 'Unassigned'}</p>
                    <p><strong>Created:</strong> ${formatDateTime(transaction.time_created)}</p>
                    ${transaction.time_served ? `<p><strong>Served:</strong> ${formatDateTime(transaction.time_served)}</p>` : ''}
                    ${transaction.duration ? `<p><strong>Duration:</strong> ${transaction.duration} minutes</p>` : ''}
                    ${transaction.notes ? `<p><strong>Notes:</strong> ${transaction.notes}</p>` : ''}
                </div>
            `,
            confirmButtonText: 'Close'
        });
    }
    
    function updateResultsCount(count) {
        const resultsElement = document.getElementById('resultsCount');
        if (resultsElement) {
            resultsElement.textContent = `${count} transactions`;
        }
    }
    
    // Utility functions
    function getStatusClass(status) {
        switch (status.toLowerCase()) {
            case 'waiting':
            case 'awaiting':
                return 'status-waiting';
            case 'completed':
            case 'complete':
                return 'status-completed';
            case 'cancelled':
                return 'status-cancelled';
            default:
                return 'status-unknown';
        }
    }
    
    function formatDateTime(datetime) {
        if (!datetime) return 'N/A';
        return new Date(datetime).toLocaleString();
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    function showLoading() {
        // Add loading indicator
        document.body.style.cursor = 'wait';
    }
    
    function hideLoading() {
        document.body.style.cursor = 'default';
    }
    
    function showError(message) {
        Swal.fire('Error', message, 'error');
    }
});
