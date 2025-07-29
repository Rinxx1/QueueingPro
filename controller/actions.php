<?php
session_start();
require_once '../connections/database.php';
require_once '../connections/auth.php';

header('Content-Type: application/json');

// Check if user is logged in and is controller
if (!isLoggedIn() || !isController()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ...existing cases...
        
        case 'repeat_number':
            $counterId = $_POST['counter_id'] ?? '';
            $currentNumber = $_POST['current_number'] ?? '';
            $counterName = $_POST['counter_name'] ?? '';
            
            if (empty($counterId) || empty($currentNumber) || empty($counterName)) {
                throw new Exception('Missing required parameters');
            }
            
            // Insert repeat announcement into announcements table
            $stmt = $pdo->prepare("
                INSERT INTO announcements (
                    Counter_ID, 
                    Announcement_Number, 
                    Counter_Name,
                    Announcement_Type,
                    Announcement_Text,
                    Created_At
                ) VALUES (?, ?, ?, 'repeat', ?, NOW())
            ");
            
            $announcementText = "Number {$currentNumber} please proceed to {$counterName}";
            
            $stmt->execute([
                $counterId,
                $currentNumber,
                $counterName,
                $announcementText
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Number repeated successfully',
                'data' => [
                    'current_number' => $currentNumber,
                    'counter_name' => $counterName,
                    'announcement_text' => $announcementText
                ]
            ]);
            break;
            
        // ...existing cases...
        
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
