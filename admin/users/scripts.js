document.addEventListener('DOMContentLoaded', function() {
    let currentEditUserId = null;
    
    // Load initial data
    loadUsers();
    loadCounters();
    
    // Event listeners
    document.getElementById('addUserBtn').addEventListener('click', openAddModal);
    document.getElementById('userForm').addEventListener('submit', handleUserSubmit);
    document.getElementById('searchInput').addEventListener('input', filterUsers);
    document.getElementById('roleFilter').addEventListener('change', filterUsers);
    
    // Load users from database
    async function loadUsers() {
        try {
            const response = await fetch('users/ajax.php?action=get_users');
            const result = await response.json();
            
            if (result.success) {
                displayUsers(result.data);
            } else {
                await Swal.fire('Error', 'Failed to load users', 'error');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Load counters for dropdown
    async function loadCounters() {
        try {
            const response = await fetch('users/ajax.php?action=get_counters');
            const result = await response.json();
            
            if (result.success) {
                populateCounterDropdowns(result.data);
            }
        } catch (error) {
            console.error('Error loading counters:', error);
        }
    }
    
    // Display users in table
    function displayUsers(users) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = '';
        
        users.forEach(user => {
            const row = createUserRow(user);
            tbody.appendChild(row);
        });
    }
    
    // Create user table row
    function createUserRow(user) {
        const row = document.createElement('tr');
        const userLevelName = getUserLevelName(user.User_Lvl);
        const statusClass = user.Status == 1 ? 'status-active' : 'status-inactive';
        const statusText = user.Status == 1 ? 'Active' : 'Inactive';
        
        row.innerHTML = `
            <td>#U${user.User_ID.toString().padStart(3, '0')}</td>
            <td>${user.Firstname}</td>
            <td>${user.Lastname}</td>
            <td>${user.Username}</td>
            <td><span class="role-badge role-${user.User_Lvl}">${userLevelName}</span></td>
            <td>${user.Counter_Name || 'Not Assigned'}</td>
            <td><span class="status-badge ${statusClass}">${statusText}</span></td>
            <td>${formatDate(user.User_Created)}</td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="editUser(${user.User_ID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.User_ID})" style="margin-left: 0.5rem;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        
        return row;
    }
    
    // Open add user modal
    function openAddModal() {
        currentEditUserId = null;
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('passwordField').style.display = 'block';
        document.getElementById('statusField').style.display = 'none';
        document.getElementById('password').required = true;
        document.getElementById('userModal').style.display = 'flex';
    }
    
    // Open edit user modal
    window.editUser = async function(userId) {
        try {
            const response = await fetch(`users/ajax.php?action=get_user&id=${userId}`);
            const result = await response.json();
            
            if (result.success) {
                currentEditUserId = userId;
                const user = result.data;
                
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('firstname').value = user.Firstname;
                document.getElementById('lastname').value = user.Lastname;
                document.getElementById('username').value = user.Username;
                document.getElementById('user_level').value = user.User_Lvl;
                document.getElementById('status').value = user.Status;
                document.getElementById('counter_id').value = user.Counter_ID || '';
                document.getElementById('password').value = '';
                document.getElementById('password').required = false;
                
                document.getElementById('passwordField').style.display = 'block';
                document.getElementById('statusField').style.display = 'block';
                document.getElementById('userModal').style.display = 'flex';
            } else {
                await Swal.fire('Error', 'Failed to load user data', 'error');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    // Handle form submission
    async function handleUserSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const action = currentEditUserId ? 'update_user' : 'create_user';
        
        if (currentEditUserId) {
            formData.append('user_id', currentEditUserId);
        }
        formData.append('action', action);
        
        // Show loading
        Swal.fire({
            title: currentEditUserId ? 'Updating User...' : 'Creating User...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        try {
            const response = await fetch('users/ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', result.message, 'success');
                closeModal();
                loadUsers();
            } else {
                await Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Delete user
    window.deleteUser = async function(userId) {
        const result = await Swal.fire({
            title: 'Confirm Delete',
            text: 'Are you sure you want to delete this user?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });
        
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userId);
                
                const response = await fetch('users/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const deleteResult = await response.json();
                
                if (deleteResult.success) {
                    await Swal.fire('Deleted!', deleteResult.message, 'success');
                    loadUsers();
                } else {
                    await Swal.fire('Error', deleteResult.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                await Swal.fire('Error', 'Connection error', 'error');
            }
        }
    };
    
    // Close modal
    window.closeModal = function() {
        document.getElementById('userModal').style.display = 'none';
        currentEditUserId = null;
    };
    
    // Filter users
    function filterUsers() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const rows = document.querySelectorAll('#usersTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const roleCell = row.cells[4].textContent;
            
            const matchesSearch = text.includes(searchTerm);
            const matchesRole = roleFilter === '' || roleCell.includes(getRoleNameFromFilter(roleFilter));
            
            row.style.display = matchesSearch && matchesRole ? '' : 'none';
        });
    }
    
    // Helper functions
    function getUserLevelName(level) {
        const levels = {
            '0': 'Administrator',
            '1': 'Controller'
        };
        return levels[level] || 'Unknown';
    }
    
    function getRoleNameFromFilter(filter) {
        const mapping = {
            'Administrator': 'Administrator',
            'Controller': 'Controller'
        };
        return mapping[filter] || '';
    }
    
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    function populateCounterDropdowns(counters) {
        const counterSelects = document.querySelectorAll('#counter_id');
        
        counterSelects.forEach(select => {
            select.innerHTML = '<option value="">Not Assigned</option>';
            counters.forEach(counter => {
                const option = document.createElement('option');
                option.value = counter.Counter_ID;
                option.textContent = counter.Counter_Name;
                select.appendChild(option);
            });
        });
    }
});
