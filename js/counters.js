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

    // Current selected counter
    let selectedCounter = null;
    let generatedQueueNumber = null;
    let queueData = null;

    // Add click handlers to transaction cards
    transactionCards.forEach(card => {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const counterId = this.getAttribute('data-counter-id');
            
            if (category === 'counter' && counterId) {
                selectedCounter = {
                    id: counterId,
                    name: this.querySelector('h4').textContent,
                    description: this.querySelector('p').textContent,
                    icon: 'fas fa-desktop',
                    wait: '5-10 minutes'
                };
                
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
        updateConfirmationModal(); // Update the modal content
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
            resetButtonStates(); // Reset button states when closing
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
            resetButtonStates(); // Reset button states when closing
            resetSelection(); // Reset selection when closing ticket modal
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
        
        // Reset button states
        resetButtonStates();
    }

    // Reset button states function
    function resetButtonStates() {
        const confirmBtn = document.getElementById('confirmService');
        const proceedBtn = document.getElementById('printQueueNumber');
        const exitBtn = document.getElementById('exit');
        
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Get Queue Number';
        }
        
        if (proceedBtn) {
            proceedBtn.disabled = false;
            proceedBtn.innerHTML = '<i class="fas fa-print"></i> Proceed';
        }
        
        if (exitBtn) {
            exitBtn.disabled = false;
            exitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Close';
        }
    }

    // Generate queue number
    // Update confirmation modal with service details
    function updateConfirmationModal() {
        if (!selectedCounter) return;

        document.getElementById('confirmServiceIcon').innerHTML = `<i class="${selectedCounter.icon}"></i>`;
        document.getElementById('confirmServiceName').textContent = selectedCounter.name;
        document.getElementById('finalServiceName').textContent = selectedCounter.name;
        
        const now = new Date();
        document.getElementById('finalDateTime').textContent = now.toLocaleString();
    }

    // Update ticket modal with service and queue details
    function updateTicketModal() {
        if (!selectedCounter || !generatedQueueNumber) return;

        document.getElementById('ticketNumber').textContent = generatedQueueNumber;
        document.getElementById('ticketService').textContent = selectedCounter.name;
        
        // Use queue data if available, otherwise fallback to default
        if (queueData) {
            const waitTime = queueData.estimated_wait_minutes;
            const waitText = waitTime <= 1 ? '1 minute' : `${waitTime} minutes`;
            document.getElementById('ticketWait').textContent = waitText;
            document.getElementById('ticketAhead').textContent = queueData.people_ahead || 0;
        } else {
            document.getElementById('ticketWait').textContent = selectedCounter.wait;
            document.getElementById('ticketAhead').textContent = Math.floor(Math.random() * 10) + 1;
        }
        
        const now = new Date();
        document.getElementById('ticketDate').textContent = now.toLocaleDateString();
    }

    // Update queue information in confirmation modal
    function updateQueueInfo(data) {
        if (!data) return;
        
        // Add queue info to the confirmation modal if elements exist
        const queueInfoContainer = document.querySelector('.queue-info-display');
        if (queueInfoContainer) {
            queueInfoContainer.innerHTML = `
                <div class="queue-stat">
                    <span class="stat-label">People Ahead:</span>
                    <span class="stat-value">${data.people_ahead || 0}</span>
                </div>
                <div class="queue-stat">
                    <span class="stat-label">Estimated Wait:</span>
                    <span class="stat-value">${data.estimated_wait_minutes || 5} min</span>
                </div>
            `;
        }
    }

    // Event Listeners
    
    // Close buttons
    closeConfirmation.addEventListener('click', () => {
        hideConfirmationModal();
        resetSelection();
    });
    closeModal.addEventListener('click', hideTicketModal);
    cancelService.addEventListener('click', () => {
        hideConfirmationModal();
        resetSelection();
    });
    closeTicket.addEventListener('click', hideTicketModal);
    
    // Exit button (Close button in final step)
    const exitBtn = document.getElementById('exit');
    if (exitBtn) {
        exitBtn.addEventListener('click', () => {
            hideConfirmationModal();
            resetSelection();
        });
    }

    // Confirm service button
    confirmService.addEventListener('click', async function() {
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        
        try {
            // Generate queue number from backend
            const response = await fetch('queue_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'generate_queue_number',
                    counter_id: selectedCounter.id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                queueData = result.data;
                generatedQueueNumber = queueData.awaiting_number;
                
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
                
                // Update the display with queue data
                document.getElementById('generatedNumber').textContent = generatedQueueNumber;
                
                // Update people ahead and wait time in the modal
                updateQueueInfo(queueData);
            } else {
                alert('Error generating queue number: ' + result.message);
                // Reset button state
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check"></i> Get Queue Number';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error connecting to server. Please try again.');
            // Reset button state
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check"></i> Get Queue Number';
        }
    });

    // Print queue number (from confirmation modal)
    printQueueNumber.addEventListener('click', async function() {
        if (!queueData || !generatedQueueNumber) {
            alert('No queue number generated');
            return;
        }
        
        // Show loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            // Add to awaiting table
            const response = await fetch('queue_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'add_to_queue',
                    counter_id: selectedCounter.id,
                    awaiting_number: generatedQueueNumber
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Successfully added to queue, show ticket modal
                showTicketModal();
                // Reset the confirmation modal button states for next use
                resetButtonStates();
            } else {
                alert('Error adding to queue: ' + result.message);
                // Reset button state
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-print"></i> Proceed';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error connecting to server. Please try again.');
            // Reset button state
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-print"></i> Proceed';
        }
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
        selectedCounter = null;
        generatedQueueNumber = null;
        queueData = null;
        transactionCards.forEach(card => {
            card.classList.remove('selected');
        });
        
        // Reset button states
        resetButtonStates();
    }

    // Close modal when clicking outside
    confirmationModal.addEventListener('click', function(e) {
        if (e.target === confirmationModal) {
            hideConfirmationModal();
            resetSelection();
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
                resetSelection();
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