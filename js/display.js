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
            // In a real implementation, this would fetch from your backend API
            // For now, we'll simulate with demo data
            const demoData = this.generateDemoData();
            this.updateDisplay(demoData);
        } catch (error) {
            console.error('Error fetching queue data:', error);
        }
    }
    
    generateDemoData() {
        // Generate realistic demo data
        const services = [
            { id: 1, name: 'General Banking', prefix: 'A', color: '#28a745' },
            { id: 2, name: 'Account Services', prefix: 'B', color: '#007bff' },
            { id: 3, name: 'Loan Services', prefix: 'C', color: '#ffc107' },
            { id: 4, name: 'Deposits & Withdrawals', prefix: 'D', color: '#17a2b8' },
            { id: 5, name: 'Customer Support', prefix: 'F', color: '#6c757d' },
            { id: 6, name: 'Premium Services', prefix: 'E', color: '#6f42c1' }
        ];
        
        const counters = [];
        
        services.forEach((service, index) => {
            const counterId = index + 1;
            const isOnline = Math.random() > 0.2; // 80% chance of being online
            const servedToday = Math.floor(Math.random() * 50) + 1;
            const avgTime = Math.floor(Math.random() * 20) + 5;
            const currentNumber = Math.floor(Math.random() * 50) + 1;
            
            counters.push({
                id: counterId,
                name: service.name,
                prefix: service.prefix,
                isOnline: isOnline,
                currentServing: isOnline ? `${service.prefix}${currentNumber.toString().padStart(3, '0')}` : null,
                servedToday: isOnline ? servedToday : 0,
                averageTime: isOnline ? avgTime : 0
            });
        });
        
        return {
            counters: counters,
            totalServed: counters.reduce((sum, counter) => sum + counter.servedToday, 0),
            activeCounters: counters.filter(c => c.isOnline).length,
            totalCounters: counters.length
        };
    }
    
    updateDisplay(data) {
        this.updateCounters(data.counters);
    }
    
    updateCounters(counters) {
        counters.forEach((counter, index) => {
            const counterCard = document.querySelector(`.counter-card:nth-child(${index + 1})`);
            if (!counterCard) return;
            
            // Update counter status
            const statusElement = counterCard.querySelector('.counter-status');
            const currentServingElement = counterCard.querySelector('.current-serving .queue-number');
            const servedTodayElement = counterCard.querySelector('.stat:nth-child(1) span');
            const avgTimeElement = counterCard.querySelector('.stat:nth-child(2) span');
            
            // Update card class
            counterCard.className = `counter-card ${counter.isOnline ? 'active' : 'offline'}`;
            
            // Update status
            if (statusElement) {
                statusElement.className = `counter-status ${counter.isOnline ? 'online' : 'offline'}`;
                statusElement.innerHTML = `
                    <i class="fas fa-circle"></i>
                    <span>${counter.isOnline ? 'ONLINE' : 'OFFLINE'}</span>
                `;
            }
            
            // Update current serving
            if (currentServingElement) {
                if (counter.isOnline && counter.currentServing) {
                    currentServingElement.textContent = counter.currentServing;
                    currentServingElement.className = 'queue-number';
                } else {
                    currentServingElement.textContent = 'Closed';
                    currentServingElement.className = 'queue-number offline';
                }
            }
            
            // Update stats
            if (servedTodayElement) {
                servedTodayElement.textContent = `Served Today: ${counter.servedToday}`;
            }
            if (avgTimeElement) {
                avgTimeElement.textContent = `Avg Time: ${counter.averageTime || '--'} min`;
            }
        });
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
