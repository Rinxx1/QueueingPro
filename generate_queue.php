<?php
session_start();
require_once 'connections/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$counterId = $_POST['counter_id'] ?? null;

if (!$counterId) {
    echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
    exit();
}

try {
    // Get counter information
    $stmt = $pdo->prepare("SELECT Counter_Name, Counter_Description, Counter_CurrentNumber FROM counters WHERE Counter_ID = ? AND Counter_Status = 'Active'");
    $stmt->execute([$counterId]);
    $counter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$counter) {
        echo json_encode(['success' => false, 'message' => 'Counter not found or not active']);
        exit();
    }
    
    // Generate next queue number for this counter
    $currentNumber = $counter['Counter_CurrentNumber'];
    $nextNumber = generateNextQueueNumber($currentNumber);
    
    // Add to awaiting queue
    $stmt = $pdo->prepare("INSERT INTO awaiting (Awaiting_Number, Counter_ID) VALUES (?, ?)");
    $stmt->execute([$nextNumber, $counterId]);
    
    // Get current queue position
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM awaiting WHERE Counter_ID = ?");
    $stmt->execute([$counterId]);
    $queuePosition = $stmt->fetchColumn();
    
    $response = [
        'success' => true,
        'queue_number' => $nextNumber,
        'counter_name' => $counter['Counter_Name'],
        'counter_description' => $counter['Counter_Description'],
        'queue_position' => $queuePosition,
        'current_serving' => $currentNumber,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

function generateNextQueueNumber($currentNumber) {
    // Extract letter and number from current number (e.g., "A001" -> "A" and "001")
    if (preg_match('/^([A-Z])(\d+)$/', $currentNumber, $matches)) {
        $letter = $matches[1];
        $number = intval($matches[2]);
        
        // Generate next number in sequence
        $nextNumber = $number + 1;
        
        // Format with leading zeros (3 digits)
        return $letter . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    // Fallback if current number format is invalid
    return 'A001';
}
?>
