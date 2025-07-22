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
        <div class="video-library-header">
            <h3 class="video-library-title">
                <i class="fas fa-play-circle"></i>
                Video Library
            </h3>
            <select id="statusFilter">
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
            <h3 id="modalTitle">Upload New Video</h3>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
        <!-- Upload Form -->
        <form id="uploadForm" enctype="multipart/form-data">
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

<!-- Edit Video Modal -->
<div id="editVideoModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="editModalTitle">Edit Video</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <!-- Edit Form -->
        <form id="editVideoForm">
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <label for="edit_title">Video Title *</label>
                    <input type="text" id="edit_title" name="edit_title" required placeholder="Enter video title">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="edit_description">Description</label>
                    <textarea id="edit_description" name="edit_description" rows="3" placeholder="Enter video description"></textarea>
                </div>
                <div class="edit-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Note: Only the title and description can be edited. To change the video file, please delete this video and upload a new one.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Video
                </button>
            </div>
        </form>
    </div>
</div>

<script src="video/scripts.js"></script>
<?php include 'components/footer.php'; ?>
