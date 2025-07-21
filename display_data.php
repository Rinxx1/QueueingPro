<?php
require_once 'connections/database.php';

header('Content-Type: application/json');

try {
    // Get all counters with their statistics, limited to 6
    $stmt = $pdo->prepare("
        SELECT 
            c.Counter_ID,
            c.Counter_Name,
            c.Counter_CurrentNumber,
            c.Counter_Status,
            c.Counter_Description
        FROM counters c
        ORDER BY c.Counter_ID ASC
        LIMIT 6
    ");
    $stmt->execute();
    $counterData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $counters = [];
    
    // For each counter, get statistics from complete table
    foreach ($counterData as $counter) {
        $counterId = $counter['Counter_ID'];
        
        // Get served today count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as served_today
            FROM complete 
            WHERE Counter_ID = ? 
            AND DATE(End_Time) = CURDATE()
        ");
        $stmt->execute([$counterId]);
        $servedResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $servedToday = $servedResult['served_today'] ?? 0;
        
        // Get average duration for today
        $stmt = $pdo->prepare("
            SELECT AVG(TIME_TO_SEC(Duration)) as avg_duration_seconds
            FROM complete 
            WHERE Counter_ID = ? 
            AND DATE(End_Time) = CURDATE()
            AND Duration IS NOT NULL
            AND Duration != ''
        ");
        $stmt->execute([$counterId]);
        $durationResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $avgDuration = 0;
        if ($durationResult['avg_duration_seconds']) {
            $avgSeconds = (int)$durationResult['avg_duration_seconds'];
            $avgDuration = round($avgSeconds / 60); // Convert to minutes
        }
        
        $counters[] = [
            'id' => $counter['Counter_ID'],
            'name' => $counter['Counter_Name'],
            'description' => $counter['Counter_Description'] ?? '',
            'current_number' => $counter['Counter_CurrentNumber'] ?? '--',
            'status' => $counter['Counter_Status'] ?? 'Offline',
            'served_today' => $servedToday,
            'avg_duration' => $avgDuration
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $counters,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
