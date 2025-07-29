<?php
session_start();
require_once '../connections/auth.php';
require_once '../connections/database.php';

header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'toggle') {
        $enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : false;
        
        // Update or insert dark mode setting
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES ('dark_mode_enabled', ?)
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        
        $enabledValue = $enabled ? '1' : '0';
        $stmt->execute([$enabledValue, $enabledValue]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Dark mode setting updated successfully',
            'dark_mode_enabled' => $enabled
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
