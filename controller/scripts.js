/**
 * QueueingPro Controller JavaScript
 * Professional Queue Management System
 */

class QueueController {
    constructor(config) {
        // Initialize with server data
        this.currentNumber = config.currentNumber;
        this.previousNumber = config.previousNumber || '-';
        this.upcomingNumber = config.upcomingNumber;
        this.isOnBreak = config.isOnBreak;
        this.operatorName = config.operatorName;
        this.counterName = config.counterName;
        this.counterId = config.counterId;
        this.userId = config.userId;
        
        this.refreshInterval = null;
        
        // Initialize the controller
        this.initializeEventListeners();
        this.updateDisplay();
        this.updateBreakStatus();
        this.loadAwaitingQueue();
        this.startAwaitingQueueRefresh();
        this.startTimingUpdates();
    }

    initializeEventListeners() {
        // Next Number button
        const nextBtn = document.getElementById('nextNumberBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.nextNumber());
        }

        // Repeat Number button
        const repeatBtn = document.getElementById('repeatNumberBtn');
        if (repeatBtn) {
            repeatBtn.addEventListener('click', () => this.repeatNumber());
        }

        // Break button
        const breakBtn = document.getElementById('breakBtn');
        if (breakBtn) {
            breakBtn.addEventListener('click', () => this.toggleBreak());
        }

        // Set Number button
        const setBtn = document.getElementById('setNumberBtn');
        if (setBtn) {
            setBtn.addEventListener('click', () => this.setCurrentNumber());
        }

        // Enter key for manual number input
        const manualInput = document.getElementById('manualNumber');
        if (manualInput) {
            manualInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.setCurrentNumber();
                }
            });
        }
    }

    updateBreakStatus() {
        const breakBtn = document.getElementById('breakBtn');
        const statusIndicator = document.querySelector('.status-indicator .status');
        
        if (!breakBtn || !statusIndicator) return;
        
        if (this.isOnBreak) {
            breakBtn.innerHTML = '<i class="fas fa-play"></i><span>Resume Service</span><small>Resume counter</small>';
            breakBtn.classList.add('resume');
            statusIndicator.innerHTML = '<i class="fas fa-pause"></i> On Break';
            statusIndicator.classList.remove('active');
            statusIndicator.classList.add('break');
        } else {
            breakBtn.innerHTML = '<i class="fas fa-pause"></i><span>Go On Break</span><small>Pause counter</small>';
            breakBtn.classList.remove('resume');
            statusIndicator.innerHTML = '<i class="fas fa-circle"></i> Active';
            statusIndicator.classList.remove('break');
            statusIndicator.classList.add('active');
        }
    }

    async updateCounterStatus(status) {
        if (!this.counterId) return false;
        
        try {
            const formData = new FormData();
            formData.append('action', 'update_counter_status');
            formData.append('counter_id', this.counterId);
            formData.append('status', status);
            
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            return result.success;
        } catch (error) {
            console.error('Error updating counter status:', error);
            return false;
        }
    }

    async updateCounterCurrentNumber(number) {
        if (!this.counterId) return { success: false };
        
        try {
            const formData = new FormData();
            formData.append('action', 'update_counter_number');
            formData.append('counter_id', this.counterId);
            formData.append('current_number', number);
            
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error updating counter number:', error);
            return { success: false };
        }
    }

    async nextNumber() {
        if (this.isOnBreak) {
            this.showMessage('Cannot call next number while on break', 'error');
            return;
        }

        if (!this.counterId) {
            this.showMessage('Counter not assigned', 'error');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'next_number');
            formData.append('counter_id', this.counterId);
            
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.previousNumber = result.previous || this.currentNumber;
                this.currentNumber = result.number;
                
                // Update global config to sync with repeat function
                if (window.controllerConfig) {
                    window.controllerConfig.currentNumber = this.currentNumber;
                    window.controllerConfig.previousNumber = this.previousNumber;
                }
                
                this.updateDisplay();
                await this.loadAwaitingQueue();
                
                this.showMessage(`${this.operatorName} called number ${this.currentNumber}`, 'success');
                this.animateNumberChange('currentNumber');
            } else {
                this.showMessage(result.message || 'Error calling next number', 'error');
            }
        } catch (error) {
            console.error('Error calling next number:', error);
            this.showMessage('Error calling next number', 'error');
        }
    }

    repeatNumber() {
        if (this.isOnBreak) {
            this.showMessage('Cannot repeat number while on break', 'error');
            return;
        }

        // Call the global repeat function with updated current number
        handleRepeatNumber();
    }

    async toggleBreak() {
        this.isOnBreak = !this.isOnBreak;
        const newStatus = this.isOnBreak ? 'Break' : 'Active';
        
        const updated = await this.updateCounterStatus(newStatus);
        this.updateBreakStatus();
        
        if (this.isOnBreak) {
            const message = updated ? 
                `${this.operatorName} is now on break` : 
                `${this.operatorName} is now on break (offline mode)`;
            this.showMessage(message, 'warning');
        } else {
            const message = updated ? 
                `${this.operatorName} resumed service` : 
                `${this.operatorName} resumed service (offline mode)`;
            this.showMessage(message, 'success');
        }
    }

    async setCurrentNumber() {
        const input = document.getElementById('manualNumber');
        if (!input) return;
        
        const newNumber = input.value.trim().toUpperCase();
        
        if (!newNumber) {
            this.showMessage('Please enter a valid number', 'error');
            return;
        }

        // Validate format (Letter followed by 3 digits)
        if (!/^[A-Z]\d{3}$/.test(newNumber)) {
            this.showMessage('Please enter a valid format (e.g., A001)', 'error');
            return;
        }

        if (!this.counterId) {
            this.showMessage('Counter not assigned', 'error');
            return;
        }
        
        const updated = await this.updateCounterCurrentNumber(newNumber);
        
        if (updated.success) {
            this.previousNumber = updated.previous || this.currentNumber;
            this.currentNumber = newNumber;
            
            // Update global config to sync with repeat function
            if (window.controllerConfig) {
                window.controllerConfig.currentNumber = this.currentNumber;
                window.controllerConfig.previousNumber = this.previousNumber;
            }
            
            this.updateDisplay();
            this.showMessage(`${this.operatorName} set current number to ${newNumber}`, 'success');
            
            input.value = '';
            this.animateNumberChange('currentNumber');
        } else {
            this.showMessage('Error updating number', 'error');
        }
    }

    updateDisplay() {
        const previousElement = document.getElementById('previousNumber');
        const currentElement = document.getElementById('currentNumber');
        const upcomingElement = document.getElementById('upcomingNumber');
        
        if (previousElement) previousElement.textContent = this.previousNumber;
        if (currentElement) currentElement.textContent = this.currentNumber;
        if (upcomingElement) upcomingElement.textContent = this.upcomingNumber;
    }

    animateNumberChange(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        element.style.transform = 'scale(1.1)';
        element.style.color = '#4A90E2';
        
        setTimeout(() => {
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 300);
    }

    showMessage(text, type) {
        const container = document.getElementById('messageContainer');
        const message = document.getElementById('message');
        
        if (!container || !message) return;
        
        message.textContent = text;
        message.className = `message ${type}`;
        container.style.display = 'block';
        
        setTimeout(() => {
            container.style.display = 'none';
        }, 3000);
    }

    async loadAwaitingQueue() {
        if (!this.counterId) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'get_awaiting_queue');
            formData.append('counter_id', this.counterId);
            
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateAwaitingQueueDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading awaiting queue:', error);
        }
    }

    updateAwaitingQueueDisplay(queueData) {
        const awaitingList = document.getElementById('awaitingList');
        const queueCount = document.getElementById('queueCount');
        
        if (!awaitingList || !queueCount) return;
        
        // Update count
        queueCount.textContent = `${queueData.length} client${queueData.length !== 1 ? 's' : ''} waiting`;

        // Update upcoming number
        const upcomingElement = document.getElementById('upcomingNumber');
        const upcomingStatusElement = upcomingElement?.nextElementSibling;
        
        if (upcomingElement && upcomingStatusElement) {
            if (queueData.length > 0) {
                this.upcomingNumber = queueData[0].Awaiting_Number;
                upcomingElement.textContent = this.upcomingNumber;
                upcomingElement.setAttribute('data-none', 'false');
                upcomingStatusElement.textContent = 'Waiting';
                upcomingStatusElement.classList.remove('no-queue');
            } else {
                this.upcomingNumber = 'None';
                upcomingElement.textContent = 'None';
                upcomingElement.setAttribute('data-none', 'true');
                upcomingStatusElement.textContent = 'No Queue';
                upcomingStatusElement.classList.add('no-queue');
            }
        }
        
        // Update list
        if (queueData.length === 0) {
            awaitingList.innerHTML = `
                <div class="no-queue">
                    <i class="fas fa-inbox"></i>
                    <p>No clients in queue</p>
                </div>
            `;
        } else {
            awaitingList.innerHTML = queueData.map(item => `
                <div class="queue-item">
                    <span class="queue-number">${item.Awaiting_Number}</span>
                    <span class="service-type">${this.counterName}</span>
                    <span class="wait-time">-</span>
                </div>
            `).join('');
        }
    }

    startAwaitingQueueRefresh() {
        // Initial load
        this.loadAwaitingQueue();
        
        // Set up interval for auto-refresh every 5 seconds
        this.refreshInterval = setInterval(() => {
            this.loadAwaitingQueue();
        }, 5000);
    }

    stopAwaitingQueueRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    destroy() {
        this.stopAwaitingQueueRefresh();
        this.stopTimingUpdates();
    }

    startTimingUpdates() {
        // Update timing every 30 seconds
        this.timingInterval = setInterval(() => {
            this.updateServiceTiming();
        }, 30000);
        
        // Initial update
        this.updateServiceTiming();
    }

    stopTimingUpdates() {
        if (this.timingInterval) {
            clearInterval(this.timingInterval);
            this.timingInterval = null;
        }
    }

    async updateServiceTiming() {
        if (!this.counterId) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'get_timing_stats');
            formData.append('counter_id', this.counterId);
            
            const response = await fetch('ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.updateTimingDisplay(result.data);
            }
        } catch (error) {
            console.error('Error loading timing stats:', error);
        }
    }

    updateTimingDisplay(timingData) {
        const serviceTimeElement = document.getElementById('serviceTime');
        const serviceDurationElement = document.getElementById('serviceDuration');
        
        if (!serviceTimeElement || !serviceDurationElement) return;
        
        if (timingData.start_time) {
            serviceTimeElement.textContent = timingData.start_time;
            
            // Calculate elapsed time since start
            const now = new Date();
            const startTime = new Date();
            const timeParts = timingData.start_time.split(':');
            startTime.setHours(parseInt(timeParts[0]), parseInt(timeParts[1]), parseInt(timeParts[2]));
            
            const elapsed = now - startTime;
            const elapsedMinutes = Math.floor(elapsed / 60000);
            const elapsedSeconds = Math.floor((elapsed % 60000) / 1000);
            
            if (elapsedMinutes > 0) {
                serviceDurationElement.textContent = `${elapsedMinutes}m ${elapsedSeconds}s elapsed`;
            } else {
                serviceDurationElement.textContent = `${elapsedSeconds}s elapsed`;
            }
        } else {
            serviceTimeElement.textContent = 'Not Started';
            serviceDurationElement.textContent = 'No active customer';
        }
    }
}

// Initialize controller when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Check if controller data is available (will be set by PHP)
    if (typeof window.controllerConfig !== 'undefined' && window.controllerConfig.counterId) {
        window.queueController = new QueueController(window.controllerConfig);
    } else {
        console.log('Counter not assigned - controller interface disabled');
    }
});

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Repeat Number Button
    const repeatBtn = document.getElementById('repeatNumberBtn');
    if (repeatBtn) {
        repeatBtn.addEventListener('click', handleRepeatNumber);
    }
});

// Handle repeat number functionality
async function handleRepeatNumber() {
    const repeatBtn = document.getElementById('repeatNumberBtn');
    
    if (!window.controllerConfig || !window.controllerConfig.counterId) {
        showMessage('Counter not configured', 'error');
        return;
    }
    
    // Get the most current number from the controller instance or config
    let currentNumber = window.controllerConfig.currentNumber;
    
    // If controller instance exists, use its current number (most up to date)
    if (window.queueController && window.queueController.currentNumber) {
        currentNumber = window.queueController.currentNumber;
        // Sync the config as well
        window.controllerConfig.currentNumber = currentNumber;
    }
    
    const counterName = window.controllerConfig.counterName;
    
    if (!currentNumber || currentNumber === '-' || currentNumber === 'None') {
        showMessage('No current number to repeat', 'error');
        return;
    }
    
    // Show loading state
    repeatBtn.disabled = true;
    repeatBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Repeating...</span>';
    
    try {
        const response = await fetch('actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'repeat_number',
                counter_id: window.controllerConfig.counterId,
                current_number: currentNumber,
                counter_name: counterName
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showMessage('Number repeated successfully - speaking slowly for clarity', 'success');
            
            // Optional: Add visual feedback to current number
            const currentNumberElement = document.getElementById('currentNumber');
            if (currentNumberElement) {
                currentNumberElement.style.animation = 'pulse 1s ease-in-out';
                setTimeout(() => {
                    currentNumberElement.style.animation = '';
                }, 1000);
            }
        } else {
            showMessage('Error repeating number: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Error connecting to server', 'error');
    } finally {
        // Reset button state
        repeatBtn.disabled = false;
        repeatBtn.innerHTML = '<i class="fas fa-redo"></i> <span>Repeat Number</span> <small>Call current again</small>';
    }
}

// Add showMessage function if it doesn't exist
function showMessage(text, type) {
    // Try to use the controller's showMessage method first
    if (window.queueController && typeof window.queueController.showMessage === 'function') {
        window.queueController.showMessage(text, type);
        return;
    }
    
    // Fallback to direct DOM manipulation
    const container = document.getElementById('messageContainer');
    const message = document.getElementById('message');
    
    if (!container || !message) {
        console.log(`${type.toUpperCase()}: ${text}`);
        return;
    }
    
    message.textContent = text;
    message.className = `message ${type}`;
    container.style.display = 'block';
    
    setTimeout(() => {
        container.style.display = 'none';
    }, 3000);
}
