<?php
require_once '../../connections/database.php';

// Get all videos
function getAllVideos() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM video ORDER BY Video_ID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getAllVideos: ' . $e->getMessage());
        return [];
    }
}

// Get video by ID
function getVideoById($videoId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM video WHERE Video_ID = ?");
        $stmt->execute([$videoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getVideoById: ' . $e->getMessage());
        return false;
    }
}

// Create new video - inactive by default to maintain single active video rule
function createVideo($title, $description, $location) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO video (Video_Title, Video_Description, Video_Location, Video_Status) 
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$title, $description, $location]);
        
        return ['success' => true, 'message' => 'Video created successfully (inactive by default)'];
    } catch(PDOException $e) {
        error_log('Error in createVideo: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update video - ensure only one video can be active
function updateVideo($videoId, $title, $description, $location, $status) {
    global $pdo;
    try {
        // If setting this video to active, deactivate all others first
        if ($status == 1) {
            $stmt = $pdo->prepare("UPDATE video SET Video_Status = 0");
            $stmt->execute();
        }
        
        $stmt = $pdo->prepare("
            UPDATE video 
            SET Video_Title = ?, Video_Description = ?, Video_Location = ?, Video_Status = ?
            WHERE Video_ID = ?
        ");
        $stmt->execute([$title, $description, $location, $status, $videoId]);
        
        $message = $status == 1 ? 
            'Video updated and activated successfully. All other videos have been deactivated.' : 
            'Video updated successfully';
            
        return ['success' => true, 'message' => $message];
    } catch(PDOException $e) {
        error_log('Error in updateVideo: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update video details (title and description only)
function updateVideoDetails($videoId, $title, $description) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE video 
            SET Video_Title = ?, Video_Description = ?
            WHERE Video_ID = ?
        ");
        $stmt->execute([$title, $description, $videoId]);
        
        return ['success' => true, 'message' => 'Video details updated successfully'];
    } catch(PDOException $e) {
        error_log('Error in updateVideoDetails: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Delete video
function deleteVideo($videoId) {
    global $pdo;
    try {
        // Get video details to delete file
        $stmt = $pdo->prepare("SELECT Video_Location FROM video WHERE Video_ID = ?");
        $stmt->execute([$videoId]);
        $video = $stmt->fetch();
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM video WHERE Video_ID = ?");
        $stmt->execute([$videoId]);
        
        // Delete physical file if it exists in uploads folder
        if ($video && strpos($video['Video_Location'], 'uploads/videos/') === 0) {
            $filePath = '../../' . $video['Video_Location'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return ['success' => true, 'message' => 'Video deleted successfully'];
    } catch(PDOException $e) {
        error_log('Error in deleteVideo: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Get currently active video
function getActiveVideo() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM video WHERE Video_Status = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getActiveVideo: ' . $e->getMessage());
        return false;
    }
}

// Update video status - only one video can be active at a time
function updateVideoStatus($videoId, $status) {
    global $pdo;
    try {
        // If activating a video, first deactivate all other videos
        if ($status == 1) {
            $stmt = $pdo->prepare("UPDATE video SET Video_Status = 0");
            $stmt->execute();
        }
        
        // Now update the target video
        $stmt = $pdo->prepare("UPDATE video SET Video_Status = ? WHERE Video_ID = ?");
        $stmt->execute([$status, $videoId]);
        
        $statusText = $status == 1 ? 'activated' : 'deactivated';
        $message = $status == 1 ? 
            "Video {$statusText} successfully. All other videos have been deactivated." : 
            "Video {$statusText} successfully";
            
        return ['success' => true, 'message' => $message];
    } catch(PDOException $e) {
        error_log('Error in updateVideoStatus: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Handle video file upload
function handleVideoUpload($file, $title, $description) {
    // Define upload directory
    $uploadDir = '../../uploads/videos/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }
    }
    
    // Validate file type
    $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'];
    $allowedExtensions = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
    
    $fileType = $file['type'];
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileType, $allowedTypes) && !in_array($fileExtension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Invalid file type. Only video files (MP4, AVI, MOV, WMV, WebM) are allowed'];
    }
    
    // Validate file size (max 100MB)
    $maxSize = 100 * 1024 * 1024; // 100MB in bytes
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum size is 100MB'];
    }
    
    // Generate unique filename
    $uniqueName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $uploadDir . $uniqueName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Save to database with relative path
        $relativePath = 'uploads/videos/' . $uniqueName;
        $result = createVideo($title, $description, $relativePath);
        
        if (!$result['success']) {
            // If database save failed, remove the uploaded file
            unlink($targetPath);
        }
        
        return $result;
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Global Mute Control Functions

// Get global mute status
function getGlobalMuteStatus() {
    global $pdo;
    try {
        // First, check if settings table exists and has our mute setting
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = 'settings'
        ");
        $stmt->execute();
        $tableExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        
        if (!$tableExists) {
            // Create settings table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS settings (
                    setting_key VARCHAR(100) PRIMARY KEY,
                    setting_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default mute setting
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES ('global_video_muted', '0')
            ");
            $stmt->execute();
            
            return ['success' => true, 'data' => ['is_muted' => false]];
        }
        
        // Get the mute status
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'global_video_muted'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Insert default if not exists
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES ('global_video_muted', '0')
            ");
            $stmt->execute();
            
            return ['success' => true, 'data' => ['is_muted' => false]];
        }
        
        return ['success' => true, 'data' => ['is_muted' => (bool)$result['setting_value']]];
        
    } catch(PDOException $e) {
        error_log('Error in getGlobalMuteStatus: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Set global mute status
function setGlobalMuteStatus($isMuted) {
    global $pdo;
    try {
        $muteValue = $isMuted ? '1' : '0';
        
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('global_video_muted', ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$muteValue, $muteValue]);
        
        return ['success' => true, 'message' => 'Global mute status updated successfully'];
        
    } catch(PDOException $e) {
        error_log('Error in setGlobalMuteStatus: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Global Volume Control Functions

// Get global volume setting
function getGlobalVolume() {
    global $pdo;
    try {
        // Get the volume setting
        $stmt = $pdo->prepare("SELECT Volume FROM settings WHERE setting_key = 'global_video_volume'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            // Insert default volume if not exists (50%)
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, Volume) 
                VALUES ('global_video_volume', '50', 50)
            ");
            $stmt->execute();
            
            return ['success' => true, 'data' => ['volume' => 50]];
        }
        
        return ['success' => true, 'data' => ['volume' => (int)$result['Volume']]];
        
    } catch(PDOException $e) {
        error_log('Error in getGlobalVolume: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Set global volume setting
function setGlobalVolume($volume) {
    global $pdo;
    try {
        // Ensure volume is within valid range (0-100)
        $volume = max(0, min(100, (int)$volume));
        
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value, Volume) 
            VALUES ('global_video_volume', ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, Volume = ?, updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$volume, $volume, $volume, $volume]);
        
        return ['success' => true, 'message' => 'Global volume updated successfully'];
        
    } catch(PDOException $e) {
        error_log('Error in setGlobalVolume: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

?>
