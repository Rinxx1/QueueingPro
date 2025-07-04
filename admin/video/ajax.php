<?php
// Start output buffering to catch any unwanted output
ob_start();

session_start();
require_once '../../connections/auth.php';
require_once 'functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    ob_clean(); // Clear any output
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Clear any output that might have been generated
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_videos':
        $videos = getAllVideos();
        echo json_encode(['success' => true, 'data' => $videos]);
        break;
        
    case 'get_video':
        $videoId = $_GET['id'] ?? 0;
        $video = getVideoById($videoId);
        if ($video) {
            echo json_encode(['success' => true, 'data' => $video]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Video not found']);
        }
        break;
        
    case 'upload_video':
        $title = trim($_POST['upload_title'] ?? '');
        $description = trim($_POST['upload_description'] ?? '');
        
        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            break;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No video file uploaded or upload error']);
            break;
        }
        
        $result = handleVideoUpload($_FILES['video_file'], $title, $description);
        echo json_encode($result);
        break;
        
    case 'create_video':
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        
        if (empty($title) || empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Title and location are required']);
            break;
        }
        
        $result = createVideo($title, $description, $location);
        echo json_encode($result);
        break;
        
    case 'update_video':
        $videoId = $_POST['video_id'] ?? 0;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = $_POST['status'] ?? 1;
        
        if (empty($title) || empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Title and location are required']);
            break;
        }
        
        $result = updateVideo($videoId, $title, $description, $location, $status);
        echo json_encode($result);
        break;
        
    case 'delete_video':
        $videoId = $_POST['video_id'] ?? 0;
        $result = deleteVideo($videoId);
        echo json_encode($result);
        break;
        
    case 'toggle_status':
        $videoId = $_POST['video_id'] ?? 0;
        $status = $_POST['status'] ?? 0;
        $result = updateVideoStatus($videoId, $status);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Ensure we exit cleanly
exit();
?>
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
