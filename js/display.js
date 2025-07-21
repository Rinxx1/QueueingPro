// Queue Display System JavaScript
class QueueDisplay {
    constructor() {
        this.updateInterval = 5000; // Update every 5 seconds
        this.timeInterval = 1000; // Update time every second
        this.counters = [];
        this.queueData = {};
        this.baseWidth = 1920; // Base design width
        this.baseHeight = 1080; // Base design height
        
        this.init();
    }
    
    init() {
        console.log('Queue Display System initialized');
        this.setupAutoScaling();
        this.startTimeUpdates();
        this.startDataUpdates();
        this.bindEvents();
    }
    
    setupAutoScaling() {
        // Function to scale the display to fit any screen
        const scaleDisplay = () => {
            const screenWidth = window.innerWidth;
            const screenHeight = window.innerHeight;
            
            // Calculate scale factors for width and height
            const scaleX = screenWidth / this.baseWidth;
            const scaleY = screenHeight / this.baseHeight;
            
            // Use the smaller scale to ensure everything fits
            const scale = Math.min(scaleX, scaleY);
            
            // Apply scaling to the body
            document.body.style.transform = `scale(${scale})`;
            document.body.style.transformOrigin = 'top left';
            
            // Adjust body size to scaled dimensions
            document.body.style.width = `${this.baseWidth}px`;
            document.body.style.height = `${this.baseHeight}px`;
            
            // Center the display if there's extra space
            const scaledWidth = this.baseWidth * scale;
            const scaledHeight = this.baseHeight * scale;
            
            const offsetX = (screenWidth - scaledWidth) / 2;
            const offsetY = (screenHeight - scaledHeight) / 2;
            
            document.body.style.left = `${offsetX}px`;
            document.body.style.top = `${offsetY}px`;
            
            console.log(`Display scaled to ${(scale * 100).toFixed(1)}% for ${screenWidth}x${screenHeight} screen`);
        };
        
        // Scale on load and resize
        scaleDisplay();
        window.addEventListener('resize', scaleDisplay);
        
        // Ensure scaling happens after fonts load
        document.fonts.ready.then(() => {
            setTimeout(scaleDisplay, 100);
        });
    }
    
    bindEvents() {
        // Auto-refresh page every 30 minutes to prevent memory leaks
        setTimeout(() => {
            window.location.reload();
        }, 30 * 60 * 1000);
        
        // Handle video errors
        const video = document.getElementById('displayVideo');
        if (video) {
            video.addEventListener('error', (e) => {
                console.log('Video error, showing placeholder');
                this.showVideoPlaceholder();
            });
        }
        
        // Handle fullscreen requests
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F11' || e.key === 'f') {
                e.preventDefault();
                this.toggleFullscreen();
            }
        });
    }
    
    toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().then(() => {
                setTimeout(() => this.setupAutoScaling(), 100);
            });
        } else {
            document.exitFullscreen();
        }
    }
    
    startTimeUpdates() {
        this.updateTime();
        setInterval(() => {
            this.updateTime();
        }, this.timeInterval);
    }
    
    updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('currentTime');
        const dateElement = document.getElementById('currentDate');
        
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }
        
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }
    
    startDataUpdates() {
        this.fetchQueueData();
        setInterval(() => {
            this.fetchQueueData();
        }, this.updateInterval);
    }
    
    async fetchQueueData() {
        try {
            const response = await fetch('display_data.php');
            const result = await response.json();
            
            if (result.success) {
                this.updateDisplay(result.data);
            } else {
                console.error('Error fetching queue data:', result.message);
                this.handleDataError();
            }
        } catch (error) {
            console.error('Error fetching queue data:', error);
            this.handleDataError();
        }
    }
    
    handleDataError() {
        // Handle data loading errors gracefully
        console.warn('Using fallback data due to connection issues');
        
        // Create fallback data for 6 counters
        const fallbackCounters = [];
        for (let i = 1; i <= 6; i++) {
            fallbackCounters.push({
                id: i,
                name: `Counter ${i}`,
                description: '',
                current_number: '--',
                status: 'Offline',
                served_today: 0,
                avg_duration: 0
            });
        }
        
        this.updateDisplay(fallbackCounters);
    }
    
    updateDisplay(counters) {
        if (!counters || !Array.isArray(counters)) {
            console.error('Invalid counter data received');
            return;
        }
        
        // Clear any existing dynamic sections first
        this.clearDynamicCounters();
        
        // Update each counter display
        counters.forEach((counter, index) => {
            this.updateCounterCard(counter, index);
        });
    }
    
    clearDynamicCounters() {
        // This method can be used if we need to clear dynamically added sections
        // For now, we'll rely on the server-side generation
    }
    
    updateCounterCard(counter, index) {
        // Get the counter card by its position in the DOM
        const allCounterCards = document.querySelectorAll('.counter-card');
        
        if (index >= allCounterCards.length) {
            console.warn(`Counter index ${index} exceeds available counter cards`);
            return;
        }
        
        const counterCard = allCounterCards[index];
        if (!counterCard) return;
        
        // Update status and classes
        const isActive = counter.status && counter.status.toLowerCase() === 'active';
        counterCard.className = `counter-card ${isActive ? 'active' : 'offline'}`;
        
        // Update counter number
        const counterNumber = counterCard.querySelector('.counter-number');
        if (counterNumber) {
            counterNumber.textContent = String(counter.id).padStart(2, '0');
        }
        
        // Update counter name
        const counterName = counterCard.querySelector('h3');
        if (counterName) {
            counterName.textContent = counter.name;
        }
        
        // Update status
        const statusElement = counterCard.querySelector('.counter-status');
        if (statusElement) {
            statusElement.className = `counter-status ${isActive ? 'online' : 'offline'}`;
            const statusText = statusElement.querySelector('span');
            if (statusText) {
                statusText.textContent = isActive ? 'ONLINE' : 'OFFLINE';
            }
        }
        
        // Update current serving number
        const queueNumber = counterCard.querySelector('.queue-number');
        if (queueNumber) {
            queueNumber.textContent = counter.current_number || '--';
        }
        
        // Update served today
        const servedStat = counterCard.querySelector('.stat:nth-child(1) span');
        if (servedStat) {
            servedStat.textContent = `Served Today: ${counter.served_today || 0}`;
        }
        
        // Update average time
        const avgTimeStat = counterCard.querySelector('.stat:nth-child(2) span');
        if (avgTimeStat) {
            const avgTime = counter.avg_duration > 0 ? `${counter.avg_duration} min` : '-- min';
            avgTimeStat.textContent = `Avg Time: ${avgTime}`;
        }
    }
    
    showVideoPlaceholder() {
        const videoContent = document.querySelector('.video-content');
        if (videoContent) {
            videoContent.innerHTML = `
                <div class="video-placeholder">
                    <h2>Video</h2>
                </div>
            `;
        }
    }
    
    // Method to highlight updated counters
    highlightCounter(counterId) {
        const counterCard = document.querySelector(`.counter-card:nth-child(${counterId})`);
        if (counterCard) {
            counterCard.style.transform = 'scale(1.02)';
            counterCard.style.boxShadow = '0 8px 25px rgba(48, 60, 84, 0.3)';
            
            setTimeout(() => {
                counterCard.style.transform = '';
                counterCard.style.boxShadow = '';
            }, 2000);
        }
    }
}

// Initialize the display system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new QueueDisplay();
});

// Handle page visibility changes to pause/resume updates
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        console.log('Display hidden, reducing update frequency');
    } else {
        console.log('Display visible, resuming normal updates');
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'F5') {
        e.preventDefault();
        window.location.reload();
    }
});

// Export for potential use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QueueDisplay;
}
