<?php
require_once 'connections/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'generate_queue_number':
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            exit();
        }
        
        try {
            // Get counter info
            $stmt = $pdo->prepare("SELECT Counter_Name, Counter_CurrentNumber FROM counters WHERE Counter_ID = ?");
            $stmt->execute([$counterId]);
            $counter = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$counter) {
                echo json_encode(['success' => false, 'message' => 'Counter not found']);
                exit();
            }
            
            // Get the next available awaiting number for this counter
            $nextNumber = generateNextAwaitingNumber($counterId);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'awaiting_number' => $nextNumber,
                    'counter_name' => $counter['Counter_Name'],
                    'counter_current_number' => $counter['Counter_CurrentNumber']
                ]
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'add_to_queue':
        $counterId = $_POST['counter_id'] ?? 0;
        $awaitingNumber = $_POST['awaiting_number'] ?? '';
        
        if (empty($counterId) || empty($awaitingNumber)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID and awaiting number are required']);
            exit();
        }
        
        try {
            // Check if awaiting number already exists for this counter
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM awaiting WHERE Counter_ID = ? AND Awaiting_Number = ?");
            $checkStmt->execute([$counterId, $awaitingNumber]);
            
            if ($checkStmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Queue number already exists']);
                exit();
            }
            
            // Insert into awaiting table
            $stmt = $pdo->prepare("INSERT INTO awaiting (Counter_ID, Awaiting_Number) VALUES (?, ?)");
            $stmt->execute([$counterId, $awaitingNumber]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Successfully added to queue',
                'queue_id' => $pdo->lastInsertId()
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function generateNextAwaitingNumber($counterId) {
    global $pdo;
    
    try {
        // Get the counter's current number to determine the prefix
        $stmt = $pdo->prepare("SELECT Counter_CurrentNumber FROM counters WHERE Counter_ID = ?");
        $stmt->execute([$counterId]);
        $counter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$counter) {
            return 'A001';
        }
        
        // Extract the letter prefix from counter's current number
        $currentNumber = $counter['Counter_CurrentNumber'];
        $prefix = preg_match('/^([A-Z])/', $currentNumber, $matches) ? $matches[1] : 'A';
        
        // Get the highest awaiting number for this counter
        $stmt = $pdo->prepare("
            SELECT Awaiting_Number 
            FROM awaiting 
            WHERE Counter_ID = ? AND Awaiting_Number LIKE ?
            ORDER BY Awaiting_Number DESC 
            LIMIT 1
        ");
        $stmt->execute([$counterId, $prefix . '%']);
        $lastNumber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lastNumber) {
            // Extract the number part and increment
            $numberPart = intval(substr($lastNumber['Awaiting_Number'], 1));
            $nextNumber = $numberPart + 1;
        } else {
            // Start from the counter's current number + 1
            $currentNumberPart = intval(substr($currentNumber, 1));
            $nextNumber = $currentNumberPart + 1;
        }
        
        // Format the number with leading zeros
        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
    } catch (PDOException $e) {
        return 'A001'; // Fallback
    }
}
?>
