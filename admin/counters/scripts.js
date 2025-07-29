document.addEventListener('DOMContentLoaded', function() {
    let currentEditCounterId = null;
    let currentEditAwaitingId = null;
    
    // Load initial data
    checkDailyReset(); // Check for daily reset first
    loadCounters();
    loadUsers();
    loadStats();
    loadAwaiting();
    
    // Event listeners
    document.getElementById('addCounterBtn').addEventListener('click', openAddModal);
    document.getElementById('resetQueueBtn').addEventListener('click', handleResetQueue);
    document.getElementById('counterForm').addEventListener('submit', handleCounterSubmit);
    document.getElementById('searchInput').addEventListener('input', filterCounters);
    document.getElementById('statusFilter').addEventListener('change', filterCounters);
    
    // Awaiting event listeners
    document.getElementById('awaitingSearchInput').addEventListener('input', filterAwaiting);
    document.getElementById('awaitingStatusFilter').addEventListener('change', filterAwaiting);
    
    // Load counters from database
    async function loadCounters() {
        try {
            const response = await fetch('counters/ajax.php?action=get_counters');
            const result = await response.json();
            
            if (result.success) {
                displayCounters(result.data);
            } else {
                await Swal.fire('Error', 'Failed to load counters', 'error');
            }
        } catch (error) {
            console.error('Error loading counters:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Load users for dropdown (only unassigned users)
    async function loadUsers() {
        try {
            const response = await fetch('counters/ajax.php?action=get_users');
            const result = await response.json();
            
            if (result.success) {
                populateUserDropdowns(result.data);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    // Load available users for a specific counter (when editing)
    async function loadAvailableUsers(counterId = null) {
        try {
            const url = counterId 
                ? `counters/ajax.php?action=get_available_users&counter_id=${counterId}`
                : 'counters/ajax.php?action=get_users';
            
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success) {
                populateUserDropdowns(result.data);
            }
        } catch (error) {
            console.error('Error loading available users:', error);
        }
    }

    // Load counter statistics
    async function loadStats() {
        try {
            const response = await fetch('counters/ajax.php?action=get_stats');
            const result = await response.json();
            
            if (result.success) {
                updateStatsDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
    
    // Load awaiting counters from database
    async function loadAwaiting() {
        try {
            const response = await fetch('counters/ajax.php?action=get_awaiting');
            const result = await response.json();
            
            if (result.success) {
                displayAwaiting(result.data);
            } else {
                await Swal.fire('Error', 'Failed to load awaiting entries', 'error');
            }
        } catch (error) {
            console.error('Error loading awaiting:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Display counters in table
    function displayCounters(counters) {
        const tbody = document.getElementById('countersTableBody');
        tbody.innerHTML = '';
        
        counters.forEach(counter => {
            const row = createCounterRow(counter);
            tbody.appendChild(row);
        });
    }
    
    // Create counter table row
    function createCounterRow(counter) {
        const row = document.createElement('tr');
        const statusClass = getStatusClass(counter.Counter_Status);
        const operatorName = counter.OperatorName || 'Not Assigned';
        const description = counter.Counter_Description || 'No description';
        
        row.innerHTML = `
            <td>#C${counter.Counter_ID.toString().padStart(3, '0')}</td>
            <td>${counter.Counter_Name}</td>
            <td>${description}</td>
            <td>${counter.Counter_CurrentNumber}</td>
            <td>${operatorName}</td>
            <td><span class="status-badge ${statusClass}">${counter.Counter_Status}</span></td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="editCounter(${counter.Counter_ID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteCounter(${counter.Counter_ID})" style="margin-left: 0.5rem;">
                    <i class="fas fa-trash"></i> Delete
                </button>
                ${getStatusActionButton(counter.Counter_Status, counter.Counter_ID)}
            </td>
        `;
        
        return row;
    }

    // Get status CSS class
    function getStatusClass(status) {
        switch (status) {
            case 'Active':
                return 'status-active';
            case 'Break':
                return 'status-break';
            case 'Offline':
                return 'status-offline';
            default:
                return 'status-inactive';
        }
    }

    // Get status action button
    function getStatusActionButton(status, counterId) {
        switch (status) {
            case 'Active':
                return `<button class="btn btn-warning btn-sm" onclick="changeCounterStatus(${counterId}, 'Break')" style="margin-left: 0.5rem;">
                    <i class="fas fa-pause"></i> Break
                </button>`;
            case 'Break':
                return `<button class="btn btn-success btn-sm" onclick="changeCounterStatus(${counterId}, 'Active')" style="margin-left: 0.5rem;">
                    <i class="fas fa-play"></i> Resume
                </button>`;
            case 'Offline':
                return `<button class="btn btn-success btn-sm" onclick="changeCounterStatus(${counterId}, 'Active')" style="margin-left: 0.5rem;">
                    <i class="fas fa-power-off"></i> Activate
                </button>`;
            default:
                return '';
        }
    }
    
    // Open add counter modal
    function openAddModal() {
        currentEditCounterId = null;
        document.getElementById('modalTitle').textContent = 'Add New Counter';
        document.getElementById('counterForm').reset();
        
        // Set readonly attribute only for current number (auto-generated)
        document.getElementById('current_number').setAttribute('readonly', true);
        document.getElementById('current_number').style.backgroundColor = '#f8f9fa';
        
        // Counter name should be editable
        document.getElementById('counter_name').removeAttribute('readonly');
        document.getElementById('counter_name').style.backgroundColor = '';
        
        // Auto-generate current number only
        generateCurrentNumber();
        
        // Load only available (unassigned) users
        loadAvailableUsers();
        
        document.getElementById('counterModal').style.display = 'flex';
    }
    
    // Generate next available current number (alphabetical)
    async function generateCurrentNumber() {
        try {
            const response = await fetch('counters/ajax.php?action=get_next_counter_name');
            const result = await response.json();
            
            if (result.success && result.data) {
                document.getElementById('current_number').value = result.data;
            } else {
                // Fallback to A001 if no data returned
                document.getElementById('current_number').value = 'A001';
            }
        } catch (error) {
            console.error('Error generating current number:', error);
            // Fallback to A001 on error
            document.getElementById('current_number').value = 'A001';
        }
    }
    
    // Open edit counter modal
    window.editCounter = async function(counterId) {
        try {
            const response = await fetch(`counters/ajax.php?action=get_counter&id=${counterId}`);
            const result = await response.json();
            
            if (result.success) {
                currentEditCounterId = counterId;
                document.getElementById('modalTitle').textContent = 'Edit Counter';
                
                // Load available users for this counter (includes current operator)
                await loadAvailableUsers(counterId);
                
                // Fill form with counter data
                document.getElementById('counter_name').value = result.data.Counter_Name;
                document.getElementById('counter_description').value = result.data.Counter_Description || '';
                document.getElementById('current_number').value = result.data.Counter_CurrentNumber;
                document.getElementById('status').value = result.data.Counter_Status;
                document.getElementById('user_id').value = result.data.User_ID || '';
                
                // For edit mode, make current number editable
                document.getElementById('current_number').removeAttribute('readonly');
                document.getElementById('current_number').style.backgroundColor = '';
                
                document.getElementById('counterModal').style.display = 'flex';
            } else {
                await Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error loading counter:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Handle counter form submission
    async function handleCounterSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const action = currentEditCounterId ? 'update_counter' : 'create_counter';
        formData.append('action', action);
        
        if (currentEditCounterId) {
            formData.append('counter_id', currentEditCounterId);
        }
        
        try {
            const response = await fetch('counters/ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', result.message, 'success');
                closeModal();
                loadCounters();
                loadStats();
            } else {
                await Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error submitting counter:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Delete counter
    window.deleteCounter = async function(counterId) {
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });
        
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'delete_counter');
                formData.append('counter_id', counterId);
                
                const response = await fetch('counters/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const deleteResult = await response.json();
                
                if (deleteResult.success) {
                    await Swal.fire('Deleted!', deleteResult.message, 'success');
                    loadCounters();
                    loadStats();
                } else {
                    await Swal.fire('Error', deleteResult.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting counter:', error);
                await Swal.fire('Error', 'Connection error', 'error');
            }
        }
    }

    // Change counter status
    window.changeCounterStatus = async function(counterId, newStatus) {
        try {
            // First get current counter data
            const getResponse = await fetch(`counters/ajax.php?action=get_counter&id=${counterId}`);
            const getResult = await getResponse.json();
            
            if (!getResult.success) {
                await Swal.fire('Error', 'Failed to get counter data', 'error');
                return;
            }
            
            const counter = getResult.data;
            
            // Update counter with new status
            const formData = new FormData();
            formData.append('action', 'update_counter');
            formData.append('counter_id', counterId);
            formData.append('counter_name', counter.Counter_Name);
            formData.append('current_number', counter.Counter_CurrentNumber);
            formData.append('status', newStatus);
            formData.append('user_id', counter.User_ID || '');
            
            const response = await fetch('counters/ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', `Counter status changed to ${newStatus}`, 'success');
                loadCounters();
                loadStats();
            } else {
                await Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error changing counter status:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Close modal
    window.closeModal = function() {
        document.getElementById('counterModal').style.display = 'none';
        currentEditCounterId = null;
    }
    
    // Filter counters
    function filterCounters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#countersTableBody tr');
        
        rows.forEach(row => {
            const counterName = row.cells[1].textContent.toLowerCase();
            const currentNumber = row.cells[2].textContent.toLowerCase();
            const operatorName = row.cells[3].textContent.toLowerCase();
            const status = row.cells[4].textContent.trim();
            
            const matchesSearch = counterName.includes(searchTerm) || 
                                currentNumber.includes(searchTerm) || 
                                operatorName.includes(searchTerm);
            const matchesStatus = statusFilter === '' || status === statusFilter;
            
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }
    
    // Populate user dropdowns
    function populateUserDropdowns(users) {
        const userSelect = document.getElementById('user_id');
        userSelect.innerHTML = '<option value="">Select Operator</option>';
        
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.User_ID;
            
            // If user is assigned to another counter, show that information
            if (user.CurrentCounterID && user.CurrentCounterName) {
                option.textContent = `${user.FullName} (Currently at ${user.CurrentCounterName})`;
            } else {
                option.textContent = user.FullName;
            }
            
            userSelect.appendChild(option);
        });
    }

    // Update stats display
    function updateStatsDisplay(stats) {
        document.querySelector('[data-stat="total"] .stat-value').textContent = stats.total;
        document.querySelector('[data-stat="active"] .stat-value').textContent = stats.active;
        document.querySelector('[data-stat="break"] .stat-value').textContent = stats.break;
        document.querySelector('[data-stat="offline"] .stat-value').textContent = stats.offline;
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const counterModal = document.getElementById('counterModal');
        
        if (e.target === counterModal) {
            closeModal();
        }
    });
    
    // Load awaiting from database
    async function loadAwaiting() {
        try {
            const response = await fetch('counters/ajax.php?action=get_awaiting');
            const result = await response.json();
            
            if (result.success) {
                displayAwaiting(result.data);
            } else {
                await Swal.fire('Error', 'Failed to load awaiting entries', 'error');
            }
        } catch (error) {
            console.error('Error loading awaiting:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Display awaiting in table
    function displayAwaiting(awaiting) {
        const tbody = document.getElementById('awaitingTableBody');
        tbody.innerHTML = '';
        
        awaiting.forEach(item => {
            const row = createAwaitingRow(item);
            tbody.appendChild(row);
        });
    }
    
    // Create awaiting table row
    function createAwaitingRow(item) {
        const row = document.createElement('tr');
        const statusClass = getStatusClass(item.Counter_Status);
        
        row.innerHTML = `
            <td>#A${item.Awaiting_ID.toString().padStart(3, '0')}</td>
            <td>${item.Awaiting_Number}</td>
            <td>${item.Counter_Name}</td>
            <td>${item.Counter_CurrentNumber}</td>
            <td><span class="status-badge ${statusClass}">${item.Counter_Status}</span></td>
        `;
        
        return row;
    }
    
    // Filter awaiting entries
    function filterAwaiting() {
        const searchTerm = document.getElementById('awaitingSearchInput').value.toLowerCase();
        const statusFilter = document.getElementById('awaitingStatusFilter').value;
        const rows = document.querySelectorAll('#awaitingTableBody tr');
        
        rows.forEach(row => {
            const counterName = row.cells[2].textContent.toLowerCase();
            const counterStatus = row.cells[4].textContent.trim();
            
            const matchesSearch = counterName.includes(searchTerm);
            const matchesStatus = statusFilter === '' || counterStatus === statusFilter;
            
            row.style.display = matchesSearch && matchesStatus ? '' : 'none';
        });
    }

    // Check and perform daily reset if needed
    async function checkDailyReset() {
        try {
            const response = await fetch('counters/ajax.php?action=check_daily_reset', {
                method: 'POST'
            });
            const result = await response.json();
            
            if (result.success && result.message !== 'Reset already performed today') {
                // A reset was performed, reload data
                console.log('Daily reset performed:', result.message);
                loadCounters();
                loadStats();
                loadAwaiting();
            }
        } catch (error) {
            console.error('Error checking daily reset:', error);
        }
    }
    
    // Handle manual reset queue
    async function handleResetQueue() {
        const result = await Swal.fire({
            title: 'Reset All Queue Numbers?',
            text: 'This will reset all counter queue numbers to 000 and clear awaiting queues. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Reset All!',
            cancelButtonText: 'Cancel'
        });
        
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Resetting Queue Numbers...',
                text: 'Please wait while we reset all counters.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            try {
                const response = await fetch('counters/ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=reset_queue_numbers'
                });
                
                const resetResult = await response.json();
                
                if (resetResult.success) {
                    await Swal.fire({
                        title: 'Reset Successful!',
                        text: resetResult.message,
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    });
                    
                    // Reload all data
                    loadCounters();
                    loadStats();
                    loadAwaiting();
                } else {
                    await Swal.fire('Error', resetResult.message, 'error');
                }
            } catch (error) {
                console.error('Error resetting queue:', error);
                await Swal.fire('Error', 'Connection error while resetting queue numbers', 'error');
            }
        }
    }
});
