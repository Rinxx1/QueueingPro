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

// Create new video
function createVideo($title, $description, $location) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO video (Video_Title, Video_Description, Video_Location, Video_Status) 
            VALUES (?, ?, ?, 1)
        ");
        $stmt->execute([$title, $description, $location]);
        
        return ['success' => true, 'message' => 'Video created successfully'];
    } catch(PDOException $e) {
        error_log('Error in createVideo: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update video
function updateVideo($videoId, $title, $description, $location, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE video 
            SET Video_Title = ?, Video_Description = ?, Video_Location = ?, Video_Status = ?
            WHERE Video_ID = ?
        ");
        $stmt->execute([$title, $description, $location, $status, $videoId]);
        
        return ['success' => true, 'message' => 'Video updated successfully'];
    } catch(PDOException $e) {
        error_log('Error in updateVideo: ' . $e->getMessage());
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

// Update video status
function updateVideoStatus($videoId, $status) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE video SET Video_Status = ? WHERE Video_ID = ?");
        $stmt->execute([$status, $videoId]);
        
        $statusText = $status == 1 ? 'activated' : 'deactivated';
        return ['success' => true, 'message' => "Video {$statusText} successfully"];
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
?>
