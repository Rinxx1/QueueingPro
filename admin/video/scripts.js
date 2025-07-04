document.addEventListener('DOMContentLoaded', function() {
    let currentEditVideoId = null;
    
    // Load initial data
    loadVideos();
    
    // Event listeners
    document.getElementById('addVideoBtn').addEventListener('click', openAddModal);
    document.getElementById('videoForm').addEventListener('submit', handleVideoSubmit);
    document.getElementById('uploadForm').addEventListener('submit', handleVideoUpload);
    document.getElementById('statusFilter').addEventListener('change', filterVideos);
    document.getElementById('video_file').addEventListener('change', handleFileSelect);
    
    // Load videos from database
    async function loadVideos() {
        try {
            const response = await fetch('video/ajax.php?action=get_videos');
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            try {
                const result = JSON.parse(text);
                
                if (result.success) {
                    displayVideos(result.data);
                } else {
                    await Swal.fire('Error', result.message || 'Failed to load videos', 'error');
                }
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response text:', text);
                await Swal.fire('Error', 'Invalid response from server. Check console for details.', 'error');
            }
        } catch (error) {
            console.error('Error loading videos:', error);
            await Swal.fire('Error', 'Connection error: ' + error.message, 'error');
        }
    }
    
    // Display videos in grid
    function displayVideos(videos) {
        const container = document.getElementById('videosContainer');
        container.innerHTML = '';
        
        if (videos.length === 0) {
            container.innerHTML = `
                <div class="content-card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-video" style="font-size: 4rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--medium-gray);">No Videos Found</h3>
                    <p style="color: var(--medium-gray);">Start by adding your first video to the library.</p>
                </div>
            `;
            return;
        }
        
        videos.forEach(video => {
            const card = createVideoCard(video);
            container.appendChild(card);
        });
    }
    
    // Create video card
    function createVideoCard(video) {
        const card = document.createElement('div');
        card.className = 'content-card video-card';
        
        const statusClass = video.Video_Status == 1 ? 'status-active' : 'status-inactive';
        const statusText = video.Video_Status == 1 ? 'Active' : 'Inactive';
        const iconClass = video.Video_Status == 1 ? 'fa-play-circle' : 'fa-pause-circle';
        const iconColor = video.Video_Status == 1 ? 'var(--accent-color)' : 'var(--medium-gray)';
        
        card.innerHTML = `
            <div style="position: relative; background: var(--light-gray); height: 200px; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas ${iconClass}" style="font-size: 3rem; color: ${iconColor};"></i>
                <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
            </div>
            <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">${video.Video_Title}</h4>
            <p style="color: var(--medium-gray); font-size: 0.9rem; margin-bottom: 1rem; min-height: 40px;">${video.Video_Description || 'No description'}</p>
            <div style="margin-bottom: 1rem; font-size: 0.85rem; color: var(--medium-gray);">
                <span><i class="fas fa-link"></i> ${video.Video_Location}</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button class="btn btn-warning" style="flex: 1; padding: 0.6rem;" onclick="editVideo(${video.Video_ID})">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn ${video.Video_Status == 1 ? 'btn-danger' : 'btn-success'}" style="flex: 1; padding: 0.6rem;" onclick="toggleVideoStatus(${video.Video_ID}, ${video.Video_Status == 1 ? 0 : 1})">
                    <i class="fas ${video.Video_Status == 1 ? 'fa-pause' : 'fa-play'}"></i> ${video.Video_Status == 1 ? 'Deactivate' : 'Activate'}
                </button>
                <button class="btn btn-danger" style="padding: 0.6rem;" onclick="deleteVideo(${video.Video_ID})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        return card;
    }
    
    // Tab switching
    window.switchTab = function(tab) {
        const manualForm = document.getElementById('videoForm');
        const uploadForm = document.getElementById('uploadForm');
        const tabBtns = document.querySelectorAll('.tab-btn');
        
        // Reset tab buttons
        tabBtns.forEach(btn => btn.classList.remove('active'));
        
        if (tab === 'manual') {
            manualForm.style.display = 'block';
            uploadForm.style.display = 'none';
            tabBtns[0].classList.add('active');
        } else {
            manualForm.style.display = 'none';
            uploadForm.style.display = 'block';
            tabBtns[1].classList.add('active');
        }
    };
    
    // Handle file selection
    function handleFileSelect(e) {
        const file = e.target.files[0];
        const fileInfo = document.getElementById('fileInfo');
        
        if (file) {
            const fileSize = (file.size / (1024 * 1024)).toFixed(2); // Size in MB
            fileInfo.innerHTML = `
                <div style="color: var(--success-color); margin-top: 0.5rem;">
                    <i class="fas fa-check-circle"></i>
                    Selected: ${file.name} (${fileSize} MB)
                </div>
            `;
        } else {
            fileInfo.innerHTML = '';
        }
    }
    
    // Handle video upload
    async function handleVideoUpload(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const file = document.getElementById('video_file').files[0];
        
        if (!file) {
            await Swal.fire('Error', 'Please select a video file', 'error');
            return;
        }
        
        const title = formData.get('upload_title');
        if (!title.trim()) {
            await Swal.fire('Error', 'Please enter a video title', 'error');
            return;
        }
        
        formData.append('action', 'upload_video');
        
        // Show upload progress
        Swal.fire({
            title: 'Uploading Video...',
            html: `
                <div style="margin: 1rem 0;">
                    <div style="background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                        <div id="uploadProgress" style="background: var(--accent-color); height: 20px; width: 0%; transition: width 0.3s;"></div>
                    </div>
                    <div id="uploadText" style="margin-top: 0.5rem;">Preparing upload...</div>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        try {
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    const progressBar = document.getElementById('uploadProgress');
                    const progressText = document.getElementById('uploadText');
                    
                    if (progressBar && progressText) {
                        progressBar.style.width = percentComplete + '%';
                        progressText.textContent = `Uploading... ${percentComplete}%`;
                    }
                }
            });
            
            // Handle completion
            xhr.addEventListener('load', async function() {
                if (xhr.status === 200) {
                    try {
                        const result = JSON.parse(xhr.responseText);
                        
                        if (result.success) {
                            await Swal.fire('Success', result.message, 'success');
                            document.getElementById('uploadForm').reset();
                            document.getElementById('fileInfo').innerHTML = '';
                            closeModal();
                            loadVideos();
                        } else {
                            await Swal.fire('Error', result.message, 'error');
                        }
                    } catch (parseError) {
                        await Swal.fire('Error', 'Invalid response from server', 'error');
                    }
                } else {
                    await Swal.fire('Error', 'Upload failed. Please try again.', 'error');
                }
            });
            
            // Handle errors
            xhr.addEventListener('error', async function() {
                await Swal.fire('Error', 'Upload failed. Please check your connection.', 'error');
            });
            
            // Send the request
            xhr.open('POST', 'video/ajax.php');
            xhr.send(formData);
            
        } catch (error) {
            console.error('Upload error:', error);
            await Swal.fire('Error', 'Upload failed. Please try again.', 'error');
        }
    }
    
    // Open add video modal
    function openAddModal() {
        currentEditVideoId = null;
        document.getElementById('modalTitle').textContent = 'Add New Video';
        document.getElementById('videoForm').reset();
        document.getElementById('uploadForm').reset();
        document.getElementById('fileInfo').innerHTML = '';
        document.getElementById('statusField').style.display = 'none';
        switchTab('manual');
        document.getElementById('videoModal').style.display = 'flex';
    }
    
    // Handle manual form submission
    async function handleVideoSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const action = currentEditVideoId ? 'update_video' : 'create_video';
        
        if (currentEditVideoId) {
            formData.append('video_id', currentEditVideoId);
        }
        formData.append('action', action);
        
        // Show loading
        Swal.fire({
            title: currentEditVideoId ? 'Updating Video...' : 'Creating Video...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        try {
            const response = await fetch('video/ajax.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                await Swal.fire('Success', result.message, 'success');
                closeModal();
                loadVideos();
            } else {
                await Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error saving video:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Edit video function
    window.editVideo = async function(videoId) {
        try {
            const response = await fetch(`video/ajax.php?action=get_video&id=${videoId}`);
            const result = await response.json();
            
            if (result.success) {
                currentEditVideoId = videoId;
                const video = result.data;
                
                document.getElementById('modalTitle').textContent = 'Edit Video';
                document.getElementById('title').value = video.Video_Title;
                document.getElementById('description').value = video.Video_Description || '';
                document.getElementById('location').value = video.Video_Location;
                document.getElementById('status').value = video.Video_Status;
                
                document.getElementById('statusField').style.display = 'block';
                switchTab('manual');
                document.getElementById('videoModal').style.display = 'flex';
            } else {
                await Swal.fire('Error', 'Failed to load video data', 'error');
            }
        } catch (error) {
            console.error('Error loading video:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    };
    
    // Toggle video status
    window.toggleVideoStatus = async function(videoId, newStatus) {
        const actionText = newStatus == 1 ? 'activate' : 'deactivate';
        
        const result = await Swal.fire({
            title: `Confirm ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`,
            text: `Are you sure you want to ${actionText} this video?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`
        });
        
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('video_id', videoId);
                formData.append('status', newStatus);
                
                const response = await fetch('video/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const toggleResult = await response.json();
                
                if (toggleResult.success) {
                    await Swal.fire('Success', toggleResult.message, 'success');
                    loadVideos();
                } else {
                    await Swal.fire('Error', toggleResult.message, 'error');
                }
            } catch (error) {
                console.error('Error toggling video status:', error);
                await Swal.fire('Error', 'Connection error', 'error');
            }
        }
    };
    
    // Delete video
    window.deleteVideo = async function(videoId) {
        const result = await Swal.fire({
            title: 'Confirm Delete',
            text: 'Are you sure you want to delete this video? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });
        
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'delete_video');
                formData.append('video_id', videoId);
                
                const response = await fetch('video/ajax.php', {
                    method: 'POST',
                    body: formData
                });
                
                const deleteResult = await response.json();
                
                if (deleteResult.success) {
                    await Swal.fire('Deleted!', deleteResult.message, 'success');
                    loadVideos();
                } else {
                    await Swal.fire('Error', deleteResult.message, 'error');
                }
            } catch (error) {
                console.error('Error deleting video:', error);
                await Swal.fire('Error', 'Connection error', 'error');
            }
        }
    };
    
    // Close modal
    window.closeModal = function() {
        document.getElementById('videoModal').style.display = 'none';
        currentEditVideoId = null;
    };
    
    // Filter videos
    function filterVideos() {
        const statusFilter = document.getElementById('statusFilter').value;
        const cards = document.querySelectorAll('.video-card');
        
        cards.forEach(card => {
            const statusBadge = card.querySelector('.status-badge');
            const isActive = statusBadge.classList.contains('status-active');
            
            let showCard = true;
            
            if (statusFilter === 'active' && !isActive) {
                showCard = false;
            } else if (statusFilter === 'inactive' && isActive) {
                showCard = false;
            }
            
            card.style.display = showCard ? 'block' : 'none';
        });
    }
});
