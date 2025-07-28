<?php
require_once '../connections/database.php';
require_once '../connections/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle') {
        $enabled = $_POST['enabled'] ?? '0';
        
        try {
            // Update the voice announcements setting
            $stmt = $pdo->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES ('voice_announcements_enabled', ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$enabled, $enabled]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Voice announcements setting updated successfully',
                'enabled' => (bool)$enabled
            ]);
            
        } catch(PDOException $e) {
            error_log('Error updating voice announcements setting: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 