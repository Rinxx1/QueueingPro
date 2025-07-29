document.addEventListener('DOMContentLoaded', function() {            
    // Update datetime
    function updateDateTime() {
        const now = new Date();
        const options = {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const dateTimeElement = document.getElementById('currentDateTime');
        if (dateTimeElement) {
            dateTimeElement.textContent = now.toLocaleDateString('en-US', options);
        }
    }

    // Update every second
    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call

    // Add keyboard shortcut for logout (Enter key)
    document.addEventListener('keydown', function(e) {
        // Check if Enter key is pressed and no input field is focused
        if (e.key === 'Enter' && !document.activeElement.matches('input, textarea, select, button')) {
            e.preventDefault();
            confirmLogout();
        }
        
        // Also allow Ctrl+L or Alt+L for logout
        if ((e.ctrlKey || e.altKey) && e.key.toLowerCase() === 'l') {
            e.preventDefault();
            confirmLogout();
        }
    });
});

// Global logout confirmation function with SweetAlert2
window.confirmLogout = async function() {
    const result = await Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to logout?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        // Show logging out message
        Swal.fire({
            title: 'Logging Out...',
            text: 'Please wait',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Clear remember me cookies if they exist
        document.cookie = 'username=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie = 'password=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';

        // Redirect to logout script
        setTimeout(() => {
            window.location.href = '../connections/logout.php';
        }, 1000);
    }
};