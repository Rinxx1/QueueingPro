<?php
$pageTitle = "Video Management - QueueingPro";
$currentPage = "video";
include 'components/header.php';
?>
<link rel="stylesheet" href="video/video.css">
<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-video"></i>
        Video Management
    </h2>
    <p class="page-description">Manage promotional videos, announcements, and display content for the queue system.</p>
</div>

<!-- Video Management Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-play-circle"></i>
                Video Library
            </h3>
            <select id="statusFilter" style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <button id="addVideoBtn" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add New Video
        </button>
    </div>
</div>

<!-- Videos Grid -->
<div class="content-grid" id="videosContainer">
    <!-- Videos will be loaded here via JavaScript -->
</div>


<!-- Video Modal -->
<div id="videoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Video</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
        <!-- Tab Navigation -->
        <div class="modal-tabs">
            <button type="button" class="tab-btn active" onclick="switchTab('manual')">Manual Entry</button>
            <button type="button" class="tab-btn" onclick="switchTab('upload')">Upload Video</button>
        </div>
        
        <!-- Manual Entry Form -->
        <form id="videoForm" style="display: block;">
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <label for="title">Video Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter video title">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Enter video description"></textarea>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="location">Video Location *</label>
                    <input type="text" id="location" name="location" required placeholder="Enter video file path or URL">
                    <small style="color: var(--medium-gray);">Enter the file path (e.g., videos/example.mp4) or URL</small>
                </div>
                <div id="statusField" style="margin-bottom: 1rem; display: none;">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Video</button>
            </div>
        </form>
        
        <!-- Upload Form -->
        <form id="uploadForm" enctype="multipart/form-data" style="display: none;">
            <div class="modal-body">
                <div style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 2rem; text-align: center; margin-bottom: 1rem; background: var(--light-gray);">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                    <p style="color: var(--medium-gray); margin-bottom: 1rem;">Select a video file to upload</p>
                    <input type="file" id="video_file" name="video_file" accept="video/*" style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('video_file').click()">
                        <i class="fas fa-folder-open"></i>
                        Browse Files
                    </button>
                    <div id="fileInfo"></div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="upload_title">Video Title *</label>
                    <input type="text" id="upload_title" name="upload_title" required placeholder="Enter video title">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="upload_description">Description</label>
                    <textarea id="upload_description" name="upload_description" rows="3" placeholder="Enter video description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-upload"></i>
                    Upload Video
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ...existing styles... */

/* Modal Tab Styles */
.modal-tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color);
}

.tab-btn {
    flex: 1;
    padding: 1rem;
    border: none;
    background: var(--light-gray);
    color: var(--medium-gray);
    cursor: pointer;
    font-family: inherit;
    font-weight: 600;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: white;
    color: var(--primary-color);
    border-bottom: 2px solid var(--accent-color);
}

.tab-btn:hover {
    background: var(--light-gray);
}

/* File Upload Styles */
#fileInfo {
    margin-top: 1rem;
}

.file-selected {
    color: var(--success-color);
    font-weight: 600;
}

/* Progress bar styles */
.upload-progress {
    background: #f0f0f0;
    border-radius: 10px;
    overflow: hidden;
    height: 20px;
    margin: 1rem 0;
}

.upload-progress-bar {
    background: var(--accent-color);
    height: 100%;
    transition: width 0.3s ease;
}
</style>

<script src="video/scripts.js"></script>
<?php include 'components/footer.php'; ?>
