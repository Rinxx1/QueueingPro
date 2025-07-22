document.addEventListener('DOMContentLoaded', function() {
    let currentEditVideoId = null;
    
    // Load initial data
    loadVideos();
    
    // Event listeners
    document.getElementById('addVideoBtn').addEventListener('click', openAddModal);
    document.getElementById('uploadForm').addEventListener('submit', handleVideoUpload);
    document.getElementById('editVideoForm').addEventListener('submit', handleEditVideoSubmit);
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
        
        // Apply current filter after loading videos
        filterVideos();
    }
    
    // Create video card
    function createVideoCard(video) {
        const card = document.createElement('div');
        card.className = `content-card video-card ${video.Video_Status == 1 ? 'active-video' : ''}`;
        
        const statusClass = video.Video_Status == 1 ? 'status-active' : 'status-inactive';
        const statusText = video.Video_Status == 1 ? 'Active' : 'Inactive';
        const iconClass = video.Video_Status == 1 ? 'fa-play-circle' : 'fa-pause-circle';
        const iconColor = video.Video_Status == 1 ? 'var(--success-color)' : 'var(--medium-gray)';
        
        // Generate thumbnail content with proper aspect ratio
        let thumbnailContent = '';
        if (video.Video_Thumbnail && video.Video_Thumbnail.trim() !== '') {
            thumbnailContent = `
                <img src="${video.Video_Thumbnail}?t=${Date.now()}" 
                     alt="Video thumbnail" 
                     class="video-thumbnail-image"
                     onerror="this.parentElement.innerHTML = getNoThumbnailContent('${iconClass}', '${iconColor}', ${video.Video_ID}, '${video.Video_Location}');">
                <div class="video-overlay">
                    <div class="video-play-button">
                        <i class="fas ${iconClass}"></i>
                    </div>
                </div>
                <div class="video-badge thumbnail-info bottom-left">
                    <i class="fas fa-image"></i> Preview
                </div>
                <div class="video-actions">
                    <button onclick="generateThumbnail(${video.Video_ID}, '${video.Video_Location}', true)" title="Regenerate thumbnail">
                        <i class="fas fa-sync"></i>
                    </button>
                </div>
            `;
        } else {
            thumbnailContent = getNoThumbnailContent(iconClass, iconColor, video.Video_ID, video.Video_Location);
        }
        
        card.innerHTML = `
            <div class="video-thumbnail-container ${video.Video_Status == 1 ? 'active' : ''}">
                ${thumbnailContent}
                ${video.Video_Status == 1 ? '<div class="video-badge status-active top-left"><i class="fas fa-star"></i></div>' : ''}
                <div class="video-badge ${statusClass} top-right">${statusText}</div>
            </div>
            <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                ${video.Video_Status == 1 ? '<i class="fas fa-star" style="color: var(--success-color); margin-right: 0.5rem;"></i>' : ''}
                ${video.Video_Title}
                ${video.Video_Status == 1 ? ' <small style="color: var(--success-color); font-weight: normal;">(Currently Playing)</small>' : ''}
            </h4>
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

    // Helper function to generate no thumbnail content
    function getNoThumbnailContent(iconClass, iconColor, videoId, videoLocation) {
        return `
            <div class="video-preview-area">
                <i class="fas ${iconClass}" style="color: ${iconColor};"></i>
                <div class="preview-text">No Preview Available</div>
            </div>
        `;
    }
    
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
        document.getElementById('modalTitle').textContent = 'Upload New Video';
        document.getElementById('uploadForm').reset();
        document.getElementById('fileInfo').innerHTML = '';
        document.getElementById('videoModal').style.display = 'flex';
    }
    
    // Edit video function
    window.editVideo = async function(videoId) {
        try {
            const response = await fetch(`video/ajax.php?action=get_video&id=${videoId}`);
            const result = await response.json();
            
            if (result.success) {
                currentEditVideoId = videoId;
                const video = result.data;
                
                // Populate edit form
                document.getElementById('edit_title').value = video.Video_Title;
                document.getElementById('edit_description').value = video.Video_Description || '';
                
                // Open edit modal
                document.getElementById('editVideoModal').style.display = 'flex';
            } else {
                await Swal.fire('Error', 'Failed to load video data', 'error');
            }
        } catch (error) {
            console.error('Error loading video:', error);
            await Swal.fire('Error', 'Failed to load video data', 'error');
        }
    };
    
    // Toggle video status
    window.toggleVideoStatus = async function(videoId, newStatus) {
        const actionText = newStatus == 1 ? 'activate' : 'deactivate';
        
        let confirmConfig = {
            title: `Confirm ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} it!`
        };
        
        if (newStatus == 1) {
            // If activating, show warning about deactivating other videos
            confirmConfig.text = 'This will activate this video and automatically deactivate all other videos. Only one video can be active at a time.';
            confirmConfig.html = `
                <p>This will activate this video and automatically <strong>deactivate all other videos</strong>.</p>
                <p><i class="fas fa-info-circle" style="color: var(--accent-color);"></i> Only one video can be active at a time.</p>
            `;
            confirmConfig.icon = 'warning';
        } else {
            confirmConfig.text = `Are you sure you want to ${actionText} this video?`;
        }
        
        const result = await Swal.fire(confirmConfig);
        
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
    
    // Close edit modal
    window.closeEditModal = function() {
        document.getElementById('editVideoModal').style.display = 'none';
        document.getElementById('editVideoForm').reset();
        currentEditVideoId = null;
    };
    
    // Validate edit form
    function validateEditForm() {
        const title = document.getElementById('edit_title').value.trim();
        
        if (!title) {
            Swal.fire('Validation Error', 'Video title is required', 'warning');
            return false;
        }
        
        if (title.length > 255) {
            Swal.fire('Validation Error', 'Video title is too long (maximum 255 characters)', 'warning');
            return false;
        }
        
        const description = document.getElementById('edit_description').value.trim();
        if (description.length > 1000) {
            Swal.fire('Validation Error', 'Description is too long (maximum 1000 characters)', 'warning');
            return false;
        }
        
        return true;
    }
    
    // Handle edit form submission
    async function handleEditVideoSubmit(e) {
        e.preventDefault();
        
        if (!currentEditVideoId) {
            await Swal.fire('Error', 'No video selected for editing', 'error');
            return;
        }
        
        // Validate form before submission
        if (!validateEditForm()) {
            return;
        }
        
        // Validate form
        const isValid = validateEditForm();
        if (!isValid) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'edit_video_details');
        formData.append('video_id', currentEditVideoId);
        formData.append('title', document.getElementById('edit_title').value);
        formData.append('description', document.getElementById('edit_description').value);
        
        // Show loading
        Swal.fire({
            title: 'Updating Video...',
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
                await Swal.fire('Success', 'Video updated successfully!', 'success');
                closeEditModal();
                loadVideos(); // Reload videos to show updated data
            } else {
                await Swal.fire('Error', result.message || 'Failed to update video', 'error');
            }
        } catch (error) {
            console.error('Error updating video:', error);
            await Swal.fire('Error', 'Connection error', 'error');
        }
    }
    
    // Filter videos
    function filterVideos() {
        const statusFilter = document.getElementById('statusFilter');
        const filterValue = statusFilter.value;
        const cards = document.querySelectorAll('.video-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            // Look for the status badge in the top-right corner
            const statusBadge = card.querySelector('.video-badge.status-active, .video-badge.status-inactive');
            const isActive = statusBadge && statusBadge.classList.contains('status-active');
            
            let showCard = true;
            
            if (filterValue === 'active' && !isActive) {
                showCard = false;
            } else if (filterValue === 'inactive' && isActive) {
                showCard = false;
            }
            
            card.style.display = showCard ? 'block' : 'none';
            if (showCard) visibleCount++;
        });
        
        // Add visual feedback for filtering state
        if (filterValue) {
            statusFilter.classList.add('filtering');
        } else {
            statusFilter.classList.remove('filtering');
        }
        
        // Update results counter
        updateFilterResults(visibleCount, cards.length, filterValue);
    }
    
    // Update filter results display
    function updateFilterResults(visibleCount, totalCount, filterValue) {
        // Remove existing results display
        const existingResults = document.querySelector('.filter-results');
        if (existingResults) {
            existingResults.remove();
        }
        
        // Add new results display if filtering is active
        if (filterValue) {
            const statusText = filterValue === 'active' ? 'active' : 'inactive';
            const resultsElement = document.createElement('span');
            resultsElement.className = 'filter-results';
            resultsElement.textContent = `${visibleCount} ${statusText} video${visibleCount !== 1 ? 's' : ''}`;
            
            const statusFilter = document.getElementById('statusFilter');
            statusFilter.parentNode.appendChild(resultsElement);
        }
    }
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        const uploadModal = document.getElementById('videoModal');
        const editModal = document.getElementById('editVideoModal');
        
        if (event.target === uploadModal) {
            closeModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
    };
});
