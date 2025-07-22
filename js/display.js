// Queue Display System JavaScript
class QueueDisplay {
    constructor() {
        this.updateInterval = 3000; // Update every 3 seconds for faster video sync
        this.timeInterval = 1000; // Update time every second
        this.counters = [];
        this.queueData = {};
        this.baseWidth = 1920; // Base design width
        this.baseHeight = 1080; // Base design height
        this.videoLoadingTimeout = null; // Timeout for video loading
        this.currentVideoId = null; // Track current video ID
        this.currentVideoLocation = null; // Track current video location
        
        this.init();
    }
    
    init() {
        console.log('Queue Display System initialized');
        this.setupAutoScaling();
        this.startTimeUpdates();
        this.startDataUpdates();
        this.bindEvents();
        this.initVideoPlayer();
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
                
                // Handle video updates
                if (result.video) {
                    this.handleVideoUpdate(result.video);
                } else {
                    // No active video
                    this.handleVideoUpdate(null);
                }
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
    
    // Handle video updates from server
    handleVideoUpdate(videoData) {
        const video = document.getElementById('displayVideo');
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        
        if (!video) return;

        // If no active video, show loading state instead of immediately showing placeholder
        if (!videoData) {
            if (this.currentVideoId !== null) {
                console.log('No active video, stopping current video and showing loading');
                this.stopCurrentVideo();
                this.handleVideoState('no-video', 'No video currently active');
            } else {
                // No current video and no new video - show loading state
                this.handleVideoState('no-video', 'Checking for active videos...');
            }
            return;
        }

        // Check if video has changed
        const hasVideoChanged = (
            this.currentVideoId !== videoData.id || 
            this.currentVideoLocation !== videoData.location
        );

        if (hasVideoChanged) {
            console.log('Video changed, updating player', {
                from: { id: this.currentVideoId, location: this.currentVideoLocation },
                to: { id: videoData.id, location: videoData.location }
            });
            
            this.updateVideoPlayer(videoData);
        }
    }

    // Update video player with new video
    updateVideoPlayer(videoData) {
        const video = document.getElementById('displayVideo');
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        
        if (!video) return;

        // Update tracking variables
        this.currentVideoId = videoData.id;
        this.currentVideoLocation = videoData.location;

        // Show loading state
        this.handleVideoState('loading');

        // Clear existing sources
        video.innerHTML = '';

        // Add new video source
        if (videoData.location) {
            const source = document.createElement('source');
            source.src = videoData.location;
            source.type = 'video/mp4';
            video.appendChild(source);
        }

        // Add fallback sources
        const fallbackSources = [
            'uploads/videos/info-video.mp4',
            'uploads/videos/backup-video.mp4'
        ];

        fallbackSources.forEach(src => {
            const source = document.createElement('source');
            source.src = src;
            source.type = 'video/mp4';
            video.appendChild(source);
        });

        // Update placeholder content
        this.updatePlaceholderContent(videoData);

        // Load the new video
        video.load();

        // Log the change
        console.log('Video player updated:', videoData.title);
    }

    // Stop current video
    stopCurrentVideo() {
        const video = document.getElementById('displayVideo');
        
        if (video) {
            video.pause();
            video.currentTime = 0;
            video.innerHTML = ''; // Clear sources
        }

        this.currentVideoId = null;
        this.currentVideoLocation = null;
    }

    // Update placeholder content with video info
    updatePlaceholderContent(videoData) {
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        
        if (videoPlaceholder && videoData) {
            const content = videoPlaceholder.querySelector('.placeholder-content');
            if (content) {
                content.innerHTML = `
                    <i class="fas fa-video fa-3x"></i>
                    <h2>Video Player</h2>
                    <p>Now Playing: ${videoData.title}</p>
                    ${videoData.description ? `<p style="font-size: 0.9rem; opacity: 0.8;">${videoData.description}</p>` : ''}
                `;
            }
        }
    }

    showVideoPlaceholder(message) {
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        
        if (videoPlaceholder) {
            const content = videoPlaceholder.querySelector('.placeholder-content');
            if (content) {
                content.innerHTML = `
                    <i class="fas fa-video fa-3x"></i>
                    <h2>Video Player</h2>
                    <p>${message}</p>
                `;
            }
            videoPlaceholder.style.display = 'flex';
        }

        // Hide loading indicator
        this.handleVideoState('error');
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
    
    initVideoPlayer() {
        const video = document.getElementById('displayVideo');
        const progressFill = document.getElementById('progressFill');
        const progressBar = document.querySelector('.progress-bar');
        const videoPlayTimeSpan = document.getElementById('videoPlayTime');
        const videoDurationSpan = document.getElementById('videoDuration');
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        const videoLoading = document.getElementById('videoLoading');

        if (!video) return;

        // Initialize current video tracking from existing video element
        const currentSource = video.querySelector('source');
        if (currentSource && currentSource.src) {
            this.currentVideoLocation = currentSource.src;
            // We don't have the video ID initially, it will be set on first data fetch
        }

        // Check initial video state
        setTimeout(() => {
            if (video.readyState >= 2 && !video.paused) {
                this.handleVideoState('playing');
            } else if (currentSource && currentSource.src) {
                this.handleVideoState('loading');
            } else {
                // No video source initially - show loading while checking for videos
                this.handleVideoState('no-video', 'Checking for active videos...');
            }
        }, 100);

        // Video event listeners
        video.addEventListener('loadstart', () => {
            this.handleVideoState('loading');
        });

        video.addEventListener('loadeddata', () => {
            this.updateVideoDuration();
        });

        video.addEventListener('canplay', () => {
            this.handleVideoState('playing');
        });

        video.addEventListener('playing', () => {
            this.handleVideoState('playing');
        });

        video.addEventListener('waiting', () => {
            this.handleVideoState('loading');
        });

        video.addEventListener('error', () => {
            this.handleVideoState('error');
            console.warn('Video failed to load, showing placeholder');
        });

        video.addEventListener('timeupdate', () => {
            this.updateVideoProgress();
        });

        // Progress bar click to seek
        if (progressBar) {
            progressBar.addEventListener('click', (e) => {
                this.seekVideo(e);
            });
        }

        // Update video time display
        this.updateVideoTimeDisplay();
        setInterval(() => {
            this.updateVideoTimeDisplay();
        }, 1000);

        // Periodic check to ensure video loading state is accurate
        setInterval(() => {
            this.checkVideoState();
        }, 2000);
    }

    updateVideoTimeDisplay() {
        const videoCurrentTime = document.getElementById('videoCurrentTime');
        const videoCurrentDate = document.getElementById('videoCurrentDate');
        
        if (videoCurrentTime && videoCurrentDate) {
            const now = new Date();
            
            videoCurrentTime.textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            
            videoCurrentDate.textContent = now.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }
    }

    seekVideo(e) {
        const video = document.getElementById('displayVideo');
        const progressBar = e.currentTarget;
        
        if (!video || !progressBar) return;

        const rect = progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        video.currentTime = pos * video.duration;
    }

    updateVideoProgress() {
        const video = document.getElementById('displayVideo');
        const progressFill = document.getElementById('progressFill');
        const videoPlayTimeSpan = document.getElementById('videoPlayTime');
        
        if (!video) return;

        const progress = (video.currentTime / video.duration) * 100;
        
        if (progressFill) {
            progressFill.style.width = progress + '%';
        }
        
        if (videoPlayTimeSpan) {
            videoPlayTimeSpan.textContent = this.formatTime(video.currentTime);
        }
    }

    updateVideoDuration() {
        const video = document.getElementById('displayVideo');
        const videoDurationSpan = document.getElementById('videoDuration');
        
        if (!video || !videoDurationSpan) return;
        
        videoDurationSpan.textContent = this.formatTime(video.duration);
    }

    formatTime(seconds) {
        if (isNaN(seconds)) return '00:00';
        
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    setupControlsAutoHide() {
        const videoSection = document.querySelector('.video-section');
        const controls = document.querySelector('.video-header');
        const progress = document.querySelector('.video-progress');
        
        if (!videoSection || !controls || !progress) return;

        let hideTimeout;
        
        const showControls = () => {
            controls.style.opacity = '1';
            progress.style.opacity = '1';
            clearTimeout(hideTimeout);
            
            hideTimeout = setTimeout(() => {
                controls.style.opacity = '0.7';
                progress.style.opacity = '0.7';
            }, 3000);
        };

        const hideControls = () => {
            controls.style.opacity = '0.7';
            progress.style.opacity = '0.7';
        };

        videoSection.addEventListener('mouseenter', showControls);
        videoSection.addEventListener('mousemove', showControls);
        videoSection.addEventListener('mouseleave', hideControls);
        
        // Initial state
        hideControls();
    }    // Method to properly handle video loading states
    handleVideoState(state, message = null) {
        const videoLoading = document.getElementById('videoLoading');
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        const video = document.getElementById('displayVideo');

        // Clear any existing timeout
        if (this.videoLoadingTimeout) {
            clearTimeout(this.videoLoadingTimeout);
            this.videoLoadingTimeout = null;
        }

        switch(state) {
            case 'loading':
                if (videoLoading) {
                    videoLoading.style.display = 'flex';
                    // Update loading message if provided
                    if (message) {
                        const loadingText = videoLoading.querySelector('p');
                        if (loadingText) loadingText.textContent = message;
                    } else {
                        const loadingText = videoLoading.querySelector('p');
                        if (loadingText) loadingText.textContent = 'Loading video...';
                    }
                }
                if (videoPlaceholder) videoPlaceholder.style.display = 'none';
                
                // Set a timeout to hide loading if it takes too long (video might already be playing)
                this.videoLoadingTimeout = setTimeout(() => {
                    if (video && !video.paused && !video.ended && video.readyState > 2) {
                        this.handleVideoState('playing');
                    }
                }, 3000); // 3 seconds timeout
                break;
                
            case 'playing':
                if (videoLoading) videoLoading.style.display = 'none';
                if (videoPlaceholder) videoPlaceholder.style.display = 'none';
                break;
                
            case 'error':
                if (videoLoading) videoLoading.style.display = 'none';
                if (videoPlaceholder) videoPlaceholder.style.display = 'flex';
                break;
                
            case 'no-video':
                if (videoLoading) {
                    videoLoading.style.display = 'flex';
                    const loadingText = videoLoading.querySelector('p');
                    if (loadingText) loadingText.textContent = message || 'No video currently active';
                }
                if (videoPlaceholder) videoPlaceholder.style.display = 'none';
                break;
        }
    }

    // Method to check and correct video state
    checkVideoState() {
        const video = document.getElementById('displayVideo');
        const videoLoading = document.getElementById('videoLoading');
        
        if (video && videoLoading) {
            // If loading is showing but video is actually playing
            if (videoLoading.style.display === 'flex' && 
                !video.paused && 
                !video.ended && 
                video.readyState > 2 &&
                video.currentTime > 0) {
                console.log('Video is playing but loading indicator is showing. Correcting...');
                this.handleVideoState('playing');
            }
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
