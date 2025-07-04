// Transaction Selection and Modal System
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const confirmationModal = document.getElementById('confirmationModal');
    const ticketModal = document.getElementById('ticketModal');
    const closeConfirmation = document.getElementById('closeConfirmation');
    const closeModal = document.getElementById('closeModal');
    const cancelService = document.getElementById('cancelService');
    const confirmService = document.getElementById('confirmService');
    const printQueueNumber = document.getElementById('printQueueNumber');
    const finishProcess = document.getElementById('finishProcess');
    const printTicket = document.getElementById('printTicket');
    const takePhoto = document.getElementById('takePhoto');
    const closeTicket = document.getElementById('closeTicket');

    // Transaction cards
    const transactionCards = document.querySelectorAll('.transaction-card');

    // Service data
    const serviceData = {
        'general': {
            name: 'General Banking',
            icon: 'fas fa-university',
            wait: '5-10 minutes',
            prefix: 'G'
        },
        'deposit': {
            name: 'Deposits & Withdrawals',
            icon: 'fas fa-money-bill-wave',
            wait: '3-7 minutes',
            prefix: 'D'
        },
        'loans': {
            name: 'Loans & Credit',
            icon: 'fas fa-handshake',
            wait: '15-25 minutes',
            prefix: 'L'
        },
        'account': {
            name: 'Account Services',
            icon: 'fas fa-user-cog',
            wait: '10-20 minutes',
            prefix: 'A'
        },
        'business': {
            name: 'Business Banking',
            icon: 'fas fa-briefcase',
            wait: '12-20 minutes',
            prefix: 'B'
        },
        'support': {
            name: 'Customer Support',
            icon: 'fas fa-headset',
            wait: '8-15 minutes',
            prefix: 'S'
        }
    };

    let selectedService = null;
    let generatedQueueNumber = null;

    // Add click handlers to transaction cards
    transactionCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            selectedService = serviceData[category];
            
            if (selectedService) {
                showConfirmationModal();
            }
        });

        // Add hover effect
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Show confirmation modal
    function showConfirmationModal() {
        resetModalSteps();
        confirmationModal.style.display = 'flex';
        setTimeout(() => {
            confirmationModal.classList.add('active');
        }, 10);
    }

    // Hide confirmation modal
    function hideConfirmationModal() {
        confirmationModal.classList.remove('active');
        setTimeout(() => {
            confirmationModal.style.display = 'none';
        }, 300);
    }

    // Show ticket modal
    function showTicketModal() {
        hideConfirmationModal();
        updateTicketModal();
        setTimeout(() => {
            ticketModal.style.display = 'flex';
            setTimeout(() => {
                ticketModal.classList.add('active');
            }, 10);
        }, 350);
    }

    // Hide ticket modal
    function hideTicketModal() {
        ticketModal.classList.remove('active');
        setTimeout(() => {
            ticketModal.style.display = 'none';
        }, 300);
    }

    // Reset modal steps
    function resetModalSteps() {
        document.getElementById('confirmationStep').style.display = 'block';
        document.getElementById('queueNumberStep').style.display = 'none';
        
        // Reset button visibility with both style and class
        const confirmActions = document.getElementById('confirmationActions');
        const finalActions = document.getElementById('finalActions');
        
        confirmActions.style.display = 'flex';
        confirmActions.classList.remove('hidden');
        
        finalActions.style.display = 'none';
        finalActions.classList.add('hidden');
        
        document.getElementById('closeConfirmation').style.display = 'block';
    }

    // Generate queue number
    function generateQueueNumber() {
        const prefix = selectedService.prefix;
        const number = Math.floor(Math.random() * 999) + 1;
        return prefix + String(number).padStart(3, '0');
    }

    // Update confirmation modal with service details
    function updateConfirmationModal() {
        if (!selectedService) return;

        document.getElementById('confirmServiceIcon').innerHTML = `<i class="${selectedService.icon}"></i>`;
        document.getElementById('confirmServiceName').textContent = selectedService.name;
        document.getElementById('finalServiceName').textContent = selectedService.name;
        
        const now = new Date();
        document.getElementById('finalDateTime').textContent = now.toLocaleString();
    }

    // Update ticket modal with service and queue details
    function updateTicketModal() {
        if (!selectedService || !generatedQueueNumber) return;

        document.getElementById('ticketNumber').textContent = generatedQueueNumber;
        document.getElementById('ticketService').textContent = selectedService.name;
        document.getElementById('ticketWait').textContent = selectedService.wait;
        document.getElementById('ticketAhead').textContent = Math.floor(Math.random() * 10) + 1;
        
        const now = new Date();
        document.getElementById('ticketDate').textContent = now.toLocaleDateString();
    }

    // Event Listeners
    
    // Close buttons
    closeConfirmation.addEventListener('click', hideConfirmationModal);
    closeModal.addEventListener('click', hideTicketModal);
    cancelService.addEventListener('click', hideConfirmationModal);
    closeTicket.addEventListener('click', hideTicketModal);

    // Confirm service button
    confirmService.addEventListener('click', function() {
        generatedQueueNumber = generateQueueNumber();
        updateConfirmationModal();
        
        // Show step 2 of confirmation - hide all previous buttons forcefully
        document.getElementById('confirmationStep').style.display = 'none';
        document.getElementById('queueNumberStep').style.display = 'block';
        
        // Use both style and class to ensure hiding
        const confirmActions = document.getElementById('confirmationActions');
        const finalActions = document.getElementById('finalActions');
        
        confirmActions.style.display = 'none';
        confirmActions.classList.add('hidden');
        
        finalActions.style.display = 'flex';
        finalActions.classList.remove('hidden');
        
        // Hide the close button in the header for final step
        document.getElementById('closeConfirmation').style.display = 'none';
        
        document.getElementById('generatedNumber').textContent = generatedQueueNumber;
    });

    // Print queue number (from confirmation modal)
    printQueueNumber.addEventListener('click', function() {
        showTicketModal();
    });

    // Finish process (from confirmation modal)
    finishProcess.addEventListener('click', function() {
        hideConfirmationModal();
        resetSelection();
    });

    // Print ticket
    printTicket.addEventListener('click', function() {
        // Simulate printing
        alert('Printing your queue ticket...');
        hideTicketModal();
        resetSelection();
    });

    // Take photo
    takePhoto.addEventListener('click', function() {
        // Simulate photo capture
        alert('Photo saved to your device!');
        hideTicketModal();
        resetSelection();
    });

    // Reset selection
    function resetSelection() {
        selectedService = null;
        generatedQueueNumber = null;
        transactionCards.forEach(card => {
            card.classList.remove('selected');
        });
    }

    // Close modal when clicking outside
    confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
            hideConfirmationModal();
        }
    });

    ticketModal.addEventListener('click', function(e) {
        if (e.target === ticketModal) {
            hideTicketModal();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (confirmationModal.style.display === 'flex') {
                hideConfirmationModal();
            }
            if (ticketModal.style.display === 'flex') {
                hideTicketModal();
            }
        }
    });

    // Touch/click feedback for cards
    transactionCards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });

        card.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });

        card.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.98)';
        });

        card.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Utility functions
function formatDateTime() {
    const now = new Date();
    return now.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getRandomWaitTime(service) {
    const times = {
        'general': [5, 10],
        'deposit': [3, 7],
        'loans': [15, 25],
        'account': [10, 20],
        'business': [12, 20],
        'support': [8, 15]
    };
    
    const range = times[service] || [5, 10];
    return Math.floor(Math.random() * (range[1] - range[0] + 1)) + range[0];
}