<?php
$pageTitle = "Video Management - QueueingPro";
$currentPage = "video";
include 'components/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h2 class="page-title">
        <i class="fas fa-video"></i>
        Video Management
    </h2>
    <p class="page-description">Manage promotional videos, announcements, and display content for the queue system.</p>
</div>

<!-- Video Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">8</div>
        <div class="stat-label">Total Videos</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">3</div>
        <div class="stat-label">Currently Playing</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">2.5 GB</div>
        <div class="stat-label">Storage Used</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">45 min</div>
        <div class="stat-label">Total Duration</div>
    </div>
</div>

<!-- Video Management Actions -->
<div class="content-card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; color: var(--primary-color);">
                <i class="fas fa-play-circle"></i>
                Video Library
            </h3>
            <select style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option>All Categories</option>
                <option>Promotional</option>
                <option>Announcements</option>
                <option>Educational</option>
                <option>Entertainment</option>
            </select>
            <select style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
                <option>Scheduled</option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-success">
                <i class="fas fa-upload"></i>
                Upload Video
            </button>
            <button class="btn btn-primary">
                <i class="fas fa-calendar-alt"></i>
                Schedule Playlist
            </button>
        </div>
    </div>
</div>

<!-- Video Grid -->
<div class="content-grid">
    <!-- Video Card 1 -->
    <div class="content-card">
        <div style="position: relative; background: var(--light-gray); height: 200px; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-play-circle" style="font-size: 3rem; color: var(--accent-color);"></i>
            <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                <span class="status-badge status-active">Active</span>
            </div>
        </div>
        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Bank Services Overview</h4>
        <p style="color: var(--medium-gray); font-size: 0.9rem; margin-bottom: 1rem;">Comprehensive guide to our banking services and products.</p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.85rem; color: var(--medium-gray);">
            <span><i class="fas fa-clock"></i> 5:30 min</span>
            <span><i class="fas fa-eye"></i> 1,234 views</span>
            <span><i class="fas fa-calendar"></i> Today</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-warning" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-edit"></i>
                Edit
            </button>
            <button class="btn btn-danger" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-pause"></i>
                Stop
            </button>
        </div>
    </div>

    <!-- Video Card 2 -->
    <div class="content-card">
        <div style="position: relative; background: var(--light-gray); height: 200px; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-play-circle" style="font-size: 3rem; color: var(--accent-color);"></i>
            <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                <span class="status-badge status-active">Active</span>
            </div>
        </div>
        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Digital Banking Tutorial</h4>
        <p style="color: var(--medium-gray); font-size: 0.9rem; margin-bottom: 1rem;">Learn how to use our mobile and online banking platforms.</p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.85rem; color: var(--medium-gray);">
            <span><i class="fas fa-clock"></i> 8:15 min</span>
            <span><i class="fas fa-eye"></i> 892 views</span>
            <span><i class="fas fa-calendar"></i> Yesterday</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-warning" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-edit"></i>
                Edit
            </button>
            <button class="btn btn-danger" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-pause"></i>
                Stop
            </button>
        </div>
    </div>

    <!-- Video Card 3 -->
    <div class="content-card">
        <div style="position: relative; background: var(--light-gray); height: 200px; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-pause-circle" style="font-size: 3rem; color: var(--medium-gray);"></i>
            <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                <span class="status-badge status-inactive">Inactive</span>
            </div>
        </div>
        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Loan Application Process</h4>
        <p style="color: var(--medium-gray); font-size: 0.9rem; margin-bottom: 1rem;">Step-by-step guide for applying for personal and business loans.</p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.85rem; color: var(--medium-gray);">
            <span><i class="fas fa-clock"></i> 6:45 min</span>
            <span><i class="fas fa-eye"></i> 567 views</span>
            <span><i class="fas fa-calendar"></i> 2 days ago</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-success" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-play"></i>
                Play
            </button>
            <button class="btn btn-warning" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-edit"></i>
                Edit
            </button>
        </div>
    </div>

    <!-- Video Card 4 -->
    <div class="content-card">
        <div style="position: relative; background: var(--light-gray); height: 200px; border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-play-circle" style="font-size: 3rem; color: var(--accent-color);"></i>
            <div style="position: absolute; top: 0.5rem; right: 0.5rem;">
                <span class="status-badge status-break">Scheduled</span>
            </div>
        </div>
        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Security Tips</h4>
        <p style="color: var(--medium-gray); font-size: 0.9rem; margin-bottom: 1rem;">Important security tips for protecting your financial information.</p>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; font-size: 0.85rem; color: var(--medium-gray);">
            <span><i class="fas fa-clock"></i> 4:20 min</span>
            <span><i class="fas fa-eye"></i> 1,045 views</span>
            <span><i class="fas fa-calendar"></i> Next week</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-warning" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-edit"></i>
                Edit
            </button>
            <button class="btn btn-primary" style="flex: 1; padding: 0.6rem;">
                <i class="fas fa-calendar"></i>
                Reschedule
            </button>
        </div>
    </div>
</div>

<!-- Video Management Tools -->
<div class="content-grid">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-upload"></i>
                Upload New Video
            </h3>
        </div>
        <div class="upload-form">
            <div style="border: 2px dashed var(--border-color); border-radius: 8px; padding: 2rem; text-align: center; margin-bottom: 1rem; background: var(--light-gray);">
                <i class="fas fa-cloud-upload-alt" style="font-size: 3rem; color: var(--medium-gray); margin-bottom: 1rem;"></i>
                <p style="color: var(--medium-gray); margin-bottom: 1rem;">Drag and drop your video file here or click to browse</p>
                <button class="btn btn-primary">
                    <i class="fas fa-folder-open"></i>
                    Browse Files
                </button>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Video Title:</label>
                <input type="text" placeholder="Enter video title" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Description:</label>
                <textarea placeholder="Enter video description" rows="3" style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; resize: vertical;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Category:</label>
                    <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        <option>Select Category</option>
                        <option>Promotional</option>
                        <option>Announcements</option>
                        <option>Educational</option>
                        <option>Entertainment</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--primary-color);">Auto-Play:</label>
                    <select style="width: 100%; padding: 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit;">
                        <option>No</option>
                        <option>Yes</option>
                    </select>
                </div>
            </div>
            <button class="btn btn-success" style="width: 100%;">
                <i class="fas fa-upload"></i>
                Upload Video
            </button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                Current Playlist
            </h3>
        </div>
        <div class="playlist">
            <div style="background: var(--light-gray); padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-color);">1. Bank Services Overview</div>
                        <div style="font-size: 0.85rem; color: var(--medium-gray);">5:30 min • Now Playing</div>
                    </div>
                    <span class="status-badge status-active">Playing</span>
                </div>
            </div>
            <div style="background: var(--light-gray); padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-color);">2. Digital Banking Tutorial</div>
                        <div style="font-size: 0.85rem; color: var(--medium-gray);">8:15 min • Up Next</div>
                    </div>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                </div>
            </div>
            <div style="background: var(--light-gray); padding: 0.8rem; border-radius: 6px; margin-bottom: 0.8rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; color: var(--primary-color);">3. Security Tips</div>
                        <div style="font-size: 0.85rem; color: var(--medium-gray);">4:20 min • Queued</div>
                    </div>
                    <button class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                </div>
            </div>
            <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                <i class="fas fa-random"></i>
                Shuffle Playlist
            </button>
        </div>
    </div>

    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-bar"></i>
                Video Analytics
            </h3>
        </div>
        <div class="video-analytics">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">3,740</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Total Views Today</div>
                </div>
                <div style="text-align: center; padding: 1rem; background: var(--light-gray); border-radius: 8px;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--accent-color);">89%</div>
                    <div style="font-size: 0.85rem; color: var(--medium-gray);">Completion Rate</div>
                </div>
            </div>
            <h4 style="color: var(--primary-color); margin-bottom: 1rem;">Popular Videos</h4>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Bank Services Overview</span>
                    <span style="font-weight: 600; color: var(--success-color);">1,234 views</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--success-color); height: 100%; width: 85%;"></div>
                </div>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Security Tips</span>
                    <span style="font-weight: 600; color: var(--accent-color);">1,045 views</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--accent-color); height: 100%; width: 75%;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-weight: 600;">Digital Banking Tutorial</span>
                    <span style="font-weight: 600; color: var(--warning-color);">892 views</span>
                </div>
                <div style="background: var(--light-gray); height: 6px; border-radius: 3px; overflow: hidden;">
                    <div style="background: var(--warning-color); height: 100%; width: 65%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'components/footer.php'; ?>
