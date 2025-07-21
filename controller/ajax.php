<?php
session_start();
require_once '../connections/auth.php';
require_once '../connections/database.php';
require_once 'functions.php';

// Check if user is logged in and is controller
if (!isLoggedIn() || !isController()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_awaiting_queue':
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        $awaitingQueue = getAwaitingQueueForCounter($counterId);
        echo json_encode(['success' => true, 'data' => $awaitingQueue]);
        break;

    case 'update_counter_status':
        $counterId = $_POST['counter_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if (empty($counterId) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID and status are required']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE counters SET Counter_Status = ? WHERE Counter_ID = ?");
            $stmt->execute([$status, $counterId]);
            echo json_encode(['success' => true, 'message' => 'Counter status updated']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update_counter_number':
        $counterId = $_POST['counter_id'] ?? 0;
        $currentNumber = $_POST['current_number'] ?? '';
        
        if (empty($counterId) || empty($currentNumber)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID and current number are required']);
            break;
        }
        
        try {
            // Get the current number before changing it
            $stmt = $pdo->prepare("SELECT Counter_CurrentNumber FROM counters WHERE Counter_ID = ?");
            $stmt->execute([$counterId]);
            $currentCounter = $stmt->fetch(PDO::FETCH_ASSOC);
            $oldNumber = $currentCounter ? $currentCounter['Counter_CurrentNumber'] : null;
            
            // Save the old current number to completed table (if it exists and is not empty)
            if ($oldNumber && $oldNumber !== '' && $oldNumber !== 'A001') {
                saveCompletedNumber($oldNumber, $counterId);
            }
            
            // Update to new number
            $stmt = $pdo->prepare("UPDATE counters SET Counter_CurrentNumber = ? WHERE Counter_ID = ?");
            $stmt->execute([$currentNumber, $counterId]);
            
            // Update the start time for the new customer
            updateCounterStartTime($counterId);
            echo json_encode([
                'success' => true, 
                'message' => 'Counter number updated',
                'previous' => $oldNumber
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'next_number':
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        try {
            // Get the current number before changing it
            $stmt = $pdo->prepare("SELECT Counter_CurrentNumber FROM counters WHERE Counter_ID = ?");
            $stmt->execute([$counterId]);
            $currentCounter = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentNumber = $currentCounter ? $currentCounter['Counter_CurrentNumber'] : null;
            
            // Get the next number from the awaiting queue
            $nextNumber = getNextAwaitingNumber($counterId);
            
            if ($nextNumber) {
                // Save the current number to completed table (if it exists and is not empty)
                if ($currentNumber && $currentNumber !== '' && $currentNumber !== 'A001') {
                    saveCompletedNumber($currentNumber, $counterId);
                }
                
                // Update the counter's current number
                $stmt = $pdo->prepare("UPDATE counters SET Counter_CurrentNumber = ? WHERE Counter_ID = ?");
                $stmt->execute([$nextNumber, $counterId]);
                
                // Update the start time for the new customer
                updateCounterStartTime($counterId);
                
                // Remove the number from the awaiting queue
                removeFirstFromAwaitingQueue($counterId);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Next number called',
                    'number' => $nextNumber,
                    'previous' => $currentNumber
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No customers in queue']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'get_completed_numbers':
        $counterId = $_POST['counter_id'] ?? 0;
        $limit = $_POST['limit'] ?? 10;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        $completedNumbers = getCompletedNumbers($counterId, $limit);
        echo json_encode(['success' => true, 'data' => $completedNumbers]);
        break;

    case 'get_timing_stats':
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        try {
            // Get current start time
            $stmt = $pdo->prepare("SELECT Start_Time FROM counters WHERE Counter_ID = ?");
            $stmt->execute([$counterId]);
            $counter = $stmt->fetch(PDO::FETCH_ASSOC);
            $startTime = $counter ? $counter['Start_Time'] : null;
            
            // Get average service time for today
            $stmt = $pdo->prepare("
                SELECT AVG(TIME_TO_SEC(Duration)) as avg_duration_seconds,
                       COUNT(*) as completed_today
                FROM complete 
                WHERE Counter_ID = ? 
                AND DATE(End_Time) = CURDATE()
                AND Duration IS NOT NULL
            ");
            $stmt->execute([$counterId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $avgDuration = null;
            if ($stats['avg_duration_seconds']) {
                $avgSeconds = (int)$stats['avg_duration_seconds'];
                $hours = floor($avgSeconds / 3600);
                $minutes = floor(($avgSeconds % 3600) / 60);
                $seconds = $avgSeconds % 60;
                $avgDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'start_time' => $startTime,
                    'avg_duration' => $avgDuration,
                    'completed_today' => $stats['completed_today'] ?? 0
                ]
            ]);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
