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
        this.startAutoplayMonitoring();
        this.setupInteractionDetection();
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
        // Do an immediate fetch to sync with server state
        this.fetchQueueData();
        
        // Then start the regular interval
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
                
                // Handle global mute status
                if (result.hasOwnProperty('global_muted')) {
                    this.handleGlobalMuteUpdate(result.global_muted);
                }
                
                // Handle global volume
                if (result.hasOwnProperty('global_volume')) {
                    this.handleGlobalVolumeUpdate(result.global_volume);
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

        // If no active video from server
        if (!videoData) {
            // Check if we currently have a video loaded (from initial PHP load)
            const currentSource = video.querySelector('source');
            if (currentSource && currentSource.src && this.currentVideoId !== null) {
                console.log('No active video from server, stopping current video');
                this.stopCurrentVideo();
                this.handleVideoState('no-video', 'No video currently active');
            } else if (!currentSource || !currentSource.src) {
                // No current video and no new video - show loading state
                this.handleVideoState('no-video', 'Checking for active videos...');
            } else {
                // We have a video source but no tracking ID - this means server might be slow
                console.log('Video source exists but no server data yet - keeping current state');
            }
            return;
        }

        // Special case: if we don't have currentVideoId set but video matches the loaded source
        if (this.currentVideoId === null && this.currentVideoLocation) {
            const normalizedCurrentLocation = this.currentVideoLocation.replace(/^.*[\\\/]/, '');
            const normalizedNewLocation = videoData.location.replace(/^.*[\\\/]/, '');
            
            if (normalizedCurrentLocation === normalizedNewLocation) {
                // Same video, just update the tracking
                console.log('Initial video matches server video, updating tracking and ensuring playback');
                this.currentVideoId = videoData.id;
                this.currentVideoLocation = videoData.location;
                
                // Make sure the video is playing if it should be
                if (video.paused && video.readyState >= 2) {
                    console.log('Video was paused, attempting to start playback');
                    this.attemptAutoplay();
                } else if (!video.paused) {
                    // Video is already playing, just update UI state
                    this.handleVideoState('playing');
                }
                return;
            }
        }

        // Handle case where we have no currentVideoLocation but there's a source element
        if (this.currentVideoId === null && !this.currentVideoLocation) {
            const currentSource = video.querySelector('source');
            if (currentSource && currentSource.src) {
                const normalizedCurrentLocation = currentSource.src.replace(/^.*[\\\/]/, '');
                const normalizedNewLocation = videoData.location.replace(/^.*[\\\/]/, '');
                
                if (normalizedCurrentLocation === normalizedNewLocation) {
                    // This is the initial video loaded by PHP
                    console.log('Found initial PHP video matches server data, setting up tracking');
                    this.currentVideoId = videoData.id;
                    this.currentVideoLocation = videoData.location;
                    
                    // Ensure video is playing
                    this.attemptAutoplay();
                    return;
                }
            }
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
        } else {
            // Same video, but make sure it's playing
            if (video.paused && video.readyState >= 2) {
                console.log('Same video but paused, attempting to start playback');
                this.attemptAutoplay();
            }
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
        
        // Multiple autoplay attempts for new videos
        const attemptPlayback = () => {
            if (video.paused && video.readyState >= 1) {
                console.log('Attempting to play newly loaded video');
                video.muted = true; // Ensure muted for autoplay success
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log('New video autoplay started successfully');
                        // Restore original mute setting after successful autoplay
                        setTimeout(() => {
                            const globalMuted = video.getAttribute('data-global-muted') === '1';
                            video.muted = globalMuted;
                        }, 1000);
                    }).catch(error => {
                        console.warn('New video autoplay failed:', error);
                        this.tryAlternativeAutoplay();
                    });
                }
            }
        };

        // Try multiple times with different delays
        setTimeout(attemptPlayback, 100);
        setTimeout(attemptPlayback, 500);
        setTimeout(attemptPlayback, 1000);

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
        const debugActiveVideo = video.getAttribute('data-debug-active-video');
        const debugVideoTitle = video.getAttribute('data-debug-video-title');
        
        console.log('Video initialization debug info:', {
            hasActiveVideoFromPHP: debugActiveVideo === 'true',
            videoTitleFromPHP: debugVideoTitle,
            hasSourceElement: currentSource !== null,
            sourceUrl: currentSource ? currentSource.src : 'none'
        });
        
        if (currentSource && currentSource.src) {
            this.currentVideoLocation = currentSource.src;
            console.log('Initial video source detected:', this.currentVideoLocation);
            // We don't have the video ID initially, it will be set on first data fetch
        } else {
            console.log('No initial video source found');
        }
        
        // Initialize global mute status and volume from data attributes
        const globalMuted = video.getAttribute('data-global-muted') === '1';
        const globalVolume = parseInt(video.getAttribute('data-global-volume')) || 50;
        
        if (globalMuted) {
            video.muted = true;
            console.log('Video initialized with global mute status: muted');
        }
        
        // Set initial volume (convert percentage to decimal)
        video.volume = globalVolume / 100;
        console.log('Video initialized with global volume:', globalVolume + '%');

        // Ensure initial video autoplays if there's a source
        if (currentSource && currentSource.src) {
            // For initial video loaded by PHP, we need to explicitly try to play it
            console.log('Attempting to play initial PHP-loaded video');
            
            // Set video properties for better autoplay success
            video.muted = true; // Start muted for better autoplay success
            video.autoplay = true;
            video.preload = 'auto';
            
            // First try immediate autoplay
            this.attemptAutoplay();
            
            // Also try after multiple delays to catch different loading states
            setTimeout(() => {
                if (video.paused && video.readyState >= 1) {
                    console.log('Retrying autoplay for initial video after 500ms');
                    this.attemptAutoplay();
                }
            }, 500);
            
            setTimeout(() => {
                if (video.paused && video.readyState >= 2) {
                    console.log('Retrying autoplay for initial video after 1s');
                    this.attemptAutoplay();
                }
            }, 1000);
            
            setTimeout(() => {
                if (video.paused) {
                    console.log('Final autoplay attempt after 2s');
                    this.attemptAutoplay();
                }
            }, 2000);
            
            // Set initial state to loading
            this.handleVideoState('loading');
        } else {
            // No initial video source
            this.handleVideoState('no-video', 'Waiting for video...');
        }

        // Check initial video state after a brief delay
        setTimeout(() => {
            this.performInitialVideoStateCheck();
        }, 300);

        // Video event listeners
        video.addEventListener('loadstart', () => {
            console.log('Video loadstart event');
            this.handleVideoState('loading');
        });

        video.addEventListener('loadeddata', () => {
            console.log('Video loadeddata event');
            this.updateVideoDuration();
            // Always try to play when data is loaded
            setTimeout(() => this.attemptAutoplay(), 100);
        });

        video.addEventListener('canplay', () => {
            console.log('Video canplay event');
            // Try to play as soon as video can play
            this.attemptAutoplay();
        });

        video.addEventListener('canplaythrough', () => {
            console.log('Video canplaythrough event');
            // Video can play through without stopping - try to start if paused
            if (video.paused) {
                this.attemptAutoplay();
            } else {
                this.handleVideoState('playing');
            }
        });

        video.addEventListener('pause', () => {
            console.log('Video paused event - checking if it should continue playing');
            // If we have an active video, try to resume playback
            if (this.currentVideoId !== null && this.currentVideoLocation) {
                setTimeout(() => {
                    if (video.paused && video.readyState >= 2) {
                        console.log('Video was paused but should be playing - attempting to resume');
                        this.attemptAutoplay();
                    }
                }, 500);
            }
        });

        video.addEventListener('playing', () => {
            console.log('Video playing event');
            this.handleVideoState('playing');
        });

        video.addEventListener('waiting', () => {
            console.log('Video waiting event');
            this.handleVideoState('loading');
        });

        video.addEventListener('error', () => {
            console.log('Video error event');
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

        // Video placeholder - no click interaction needed, autoplay will handle it
        if (videoPlaceholder) {
            // Remove cursor pointer since we don't want manual interaction
            videoPlaceholder.style.cursor = 'default';
        }

        // Update video time display
        this.updateVideoTimeDisplay();
        setInterval(() => {
            this.updateVideoTimeDisplay();
        }, 1000);

        // Periodic check to ensure video loading state is accurate - more frequent checks
        setInterval(() => {
            this.checkVideoState();
        }, 1000);

        // Initial state check after a brief delay for page initialization  
        setTimeout(() => {
            this.performInitialVideoStateCheck();
        }, 1500); // Additional check after 1.5 seconds
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

        console.log('Video state change:', state, message || '');

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
                
                // Set a timeout to check video state if loading persists
                this.videoLoadingTimeout = setTimeout(() => {
                    if (video) {
                        console.log('Loading timeout - checking video state:', {
                            paused: video.paused,
                            ended: video.ended,
                            readyState: video.readyState,
                            currentTime: video.currentTime,
                            src: video.src
                        });
                        
                        if (!video.paused && !video.ended && video.readyState > 2 && video.currentTime > 0) {
                            // Video is actually playing
                            console.log('Video was playing during loading state - correcting');
                            this.handleVideoState('playing');
                        } else if (video.readyState >= 2 && video.src) {
                            // Video is ready but not playing - try autoplay
                            console.log('Video ready but not playing - attempting autoplay');
                            this.attemptAutoplay();
                        } else if (!video.src) {
                            // No video source
                            this.handleVideoState('no-video', 'No video source');
                        }
                        // If none of the above, keep loading state
                    }
                }, 5000); // Increased timeout to 5 seconds
                break;
                
            case 'playing':
                if (videoLoading) videoLoading.style.display = 'none';
                if (videoPlaceholder) videoPlaceholder.style.display = 'none';
                break;
                
            case 'paused':
                if (videoLoading) videoLoading.style.display = 'none';
                if (videoPlaceholder) {
                    videoPlaceholder.style.display = 'flex';
                    // Update placeholder to show autoplay blocked state
                    const content = videoPlaceholder.querySelector('.placeholder-content');
                    if (content) {
                        content.innerHTML = `
                            <i class="fas fa-play fa-3x"></i>
                            <h2>Video Loading...</h2>
                            <p>Attempting to start video automatically...</p>
                        `;
                    }
                }
                
                // Keep trying to autoplay every few seconds when in paused state
                setTimeout(() => {
                    if (this.currentVideoId !== null) {
                        console.log('Retrying autoplay from paused state');
                        this.attemptAutoplay();
                    }
                }, 3000);
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
        const videoPlaceholder = document.getElementById('videoPlaceholder');
        
        if (!video) return;
        
        const isLoadingVisible = videoLoading && videoLoading.style.display === 'flex';
        const isPlaceholderVisible = videoPlaceholder && videoPlaceholder.style.display === 'flex';
        
        // Check if video is actually playing but UI shows it's not
        if (isLoadingVisible && 
            !video.paused && 
            !video.ended && 
            video.readyState > 2 &&
            video.currentTime > 0) {
            console.log('Video is playing but loading indicator is showing. Correcting...');
            this.handleVideoState('playing');
            return;
        }
        
        // Check if video is ready to play but stuck in loading
        if (isLoadingVisible && 
            video.readyState >= 2 && 
            video.paused && 
            video.src) {
            console.log('Video is ready but paused, attempting autoplay...');
            this.attemptAutoplay();
            return;
        }
        
        // Check if video has no source but loading is showing
        if (isLoadingVisible && !video.src) {
            console.log('No video source but loading is showing. Showing no-video state...');
            this.handleVideoState('no-video', 'No active video');
            return;
        }
        
        // Check if video ended and is stuck
        if (video.ended && (isLoadingVisible || isPlaceholderVisible)) {
            console.log('Video ended, attempting restart...');
            video.currentTime = 0;
            this.attemptAutoplay();
            return;
        }
    }

    // Method to attempt autoplay - centralized logic
    attemptAutoplay() {
        const video = document.getElementById('displayVideo');
        if (!video) return;

        // Check if video has any source (including from <source> elements)
        const hasSource = video.src || video.querySelector('source');
        
        if (!hasSource) {
            console.log('No video source available for autoplay');
            return;
        }

        // Only attempt autoplay if video is paused
        if (video.paused) {
            console.log('Attempting autoplay...', {
                readyState: video.readyState,
                hasSource: !!hasSource,
                currentTime: video.currentTime,
                duration: video.duration
            });
            
            // Ensure video is unmuted for autoplay to work in most browsers
            video.muted = true;
            
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log('Video autoplay successful');
                    this.handleVideoState('playing');
                    
                    // After successful autoplay, restore original mute setting
                    setTimeout(() => {
                        const globalMuted = video.getAttribute('data-global-muted') === '1';
                        video.muted = globalMuted;
                        console.log('Restored original mute setting:', globalMuted);
                    }, 1000);
                }).catch(error => {
                    console.warn('Video autoplay blocked by browser:', error);
                    
                    // Try more aggressive approaches
                    this.tryAlternativeAutoplay();
                });
            }
        } else {
            // Video is already playing
            console.log('Video is already playing');
            this.handleVideoState('playing');
        }
    }

    // Try alternative autoplay methods
    tryAlternativeAutoplay() {
        const video = document.getElementById('displayVideo');
        if (!video) return;

        console.log('Trying alternative autoplay methods...');

        // Method 1: Try with extremely low volume instead of muted
        video.muted = false;
        video.volume = 0.01; // Very low volume
        
        const playPromise1 = video.play();
        if (playPromise1 !== undefined) {
            playPromise1.then(() => {
                console.log('Alternative autoplay successful with low volume');
                this.handleVideoState('playing');
                
                // Gradually restore volume
                setTimeout(() => {
                    const globalVolume = parseInt(video.getAttribute('data-global-volume')) || 50;
                    video.volume = globalVolume / 100;
                    console.log('Restored volume to:', globalVolume + '%');
                }, 2000);
            }).catch(() => {
                // Method 2: Try with interaction simulation
                this.simulateUserInteraction();
            });
        }
    }

    // Simulate user interaction for autoplay
    simulateUserInteraction() {
        const video = document.getElementById('displayVideo');
        if (!video) return;

        console.log('Simulating user interaction for autoplay...');

        // Create a synthetic click event
        const clickEvent = new MouseEvent('click', {
            view: window,
            bubbles: true,
            cancelable: true
        });

        // Try to trigger autoplay through simulated interaction
        document.body.dispatchEvent(clickEvent);
        
        setTimeout(() => {
            video.muted = true;
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log('Autoplay successful after simulated interaction');
                    this.handleVideoState('playing');
                    
                    // Restore settings
                    setTimeout(() => {
                        const globalMuted = video.getAttribute('data-global-muted') === '1';
                        const globalVolume = parseInt(video.getAttribute('data-global-volume')) || 50;
                        video.muted = globalMuted;
                        video.volume = globalVolume / 100;
                    }, 1000);
                }).catch(() => {
                    console.log('All autoplay methods failed - video requires manual interaction');
                    this.handleVideoState('paused');
                });
            }
        }, 100);
    }

    // Perform initial video state check after page load
    performInitialVideoStateCheck() {
        const video = document.getElementById('displayVideo');
        if (!video) return;

        console.log('Performing initial video state check', {
            readyState: video.readyState,
            paused: video.paused,
            ended: video.ended,
            currentTime: video.currentTime,
            duration: video.duration,
            src: video.src,
            hasSource: video.querySelector('source') !== null,
            sourceCount: video.querySelectorAll('source').length
        });

        // Check if video element has any sources
        const sources = video.querySelectorAll('source');
        const hasValidSource = sources.length > 0 && sources[0].src && sources[0].src !== '';

        if (hasValidSource) {
            console.log('Video has valid source:', sources[0].src);
            
            if (video.readyState >= 2) { // HAVE_CURRENT_DATA or higher
                if (!video.paused && !video.ended) {
                    // Video is actually playing
                    console.log('Video is playing on initial check');
                    this.handleVideoState('playing');
                } else {
                    // Video is ready but not playing - try to start it
                    console.log('Video ready but not playing - attempting autoplay');
                    this.attemptAutoplay();
                    
                    // If still paused after attempt, it might be browser restrictions
                    setTimeout(() => {
                        if (video.paused) {
                            console.log('Video still paused after autoplay attempt - showing clickable placeholder');
                            this.handleVideoState('paused');
                        }
                    }, 1000);
                }
            } else {
                // Video is still loading
                console.log('Video still loading - showing loading state');
                this.handleVideoState('loading');
                
                // Force a load attempt
                video.load();
            }
        } else {
            // No video source - this is the issue you're experiencing
            console.log('No video source found on initial check');
            this.handleVideoState('no-video', 'No active video');
        }
    }

    // Handle global mute status updates from admin
    handleGlobalMuteUpdate(globalMuted) {
        const video = document.getElementById('displayVideo');
        if (!video) return;
        
        // Only update if the mute status has changed
        if (video.muted !== globalMuted) {
            video.muted = globalMuted;
            console.log('Global mute status updated:', globalMuted ? 'muted' : 'unmuted');
        }
    }

    // Handle global volume updates from admin
    handleGlobalVolumeUpdate(globalVolume) {
        const video = document.getElementById('displayVideo');
        if (!video) return;
        
        // Convert percentage to decimal (0-1)
        const volumeLevel = globalVolume / 100;
        
        // Only update if the volume has changed
        if (Math.abs(video.volume - volumeLevel) > 0.01) {
            video.volume = volumeLevel;
            console.log('Global volume updated to:', globalVolume + '%');
        }
    }

    // Continuous autoplay monitoring - ensures video keeps playing when Video_Status = 1
    startAutoplayMonitoring() {
        setInterval(() => {
            this.enforceAutoplay();
        }, 2000); // Check every 2 seconds
    }

    // Enforce autoplay if video should be playing
    enforceAutoplay() {
        const video = document.getElementById('displayVideo');
        if (!video) return;

        // Only enforce if we have a tracked video (meaning server says there's an active video)
        if (this.currentVideoId !== null && this.currentVideoLocation) {
            const hasSource = video.src || video.querySelector('source');
            
            if (hasSource && video.paused && video.readyState >= 2) {
                console.log('Video is paused but should be playing - enforcing autoplay');
                this.attemptAutoplay();
            }
        }
   }

    // Setup page interaction detection for autoplay enablement
    setupInteractionDetection() {
        let interactionDetected = false;
        
        const enableAutoplay = () => {
            if (!interactionDetected) {
                interactionDetected = true;
                console.log('User interaction detected - enabling aggressive autoplay');
                
                // Try to play video if it's paused
                const video = document.getElementById('displayVideo');
                if (video && video.paused && this.currentVideoId !== null) {
                    this.attemptAutoplay();
                }
            }
        };

        // Listen for various user interactions
        ['click', 'touchstart', 'keydown', 'mousemove'].forEach(event => {
            document.addEventListener(event, enableAutoplay, { once: true, passive: true });
        });
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
