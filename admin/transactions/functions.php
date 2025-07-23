<?php
require_once '../../connections/database.php';

// Get all transactions from the new transactions table and views
function getAllTransactions($filters = []) {
    global $pdo;
    try {
        // Use the unified view for transactions
        $sql = "
            SELECT 
                transaction_id,
                transaction_ref,
                queue_number,
                counter_id,
                operator_id,
                Counter_Name,
                operator_name,
                status,
                time_created,
                time_served,
                duration,
                formatted_duration,
                created_at,
                notes
            FROM v_all_transactions
            WHERE 1=1
        ";
        
        $params = [];
        
        // Apply date filter
        if (!empty($filters['date_range'])) {
            switch ($filters['date_range']) {
                case 'today':
                    $sql .= " AND DATE(created_at) = CURDATE()";
                    break;
                case 'week':
                    $sql .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $sql .= " AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
                    break;
            }
        }
        
        // Apply status filter
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Waiting') {
                $sql .= " AND status = 'waiting'";
            } elseif ($filters['status'] === 'Completed') {
                $sql .= " AND status = 'completed'";
            }
        }
        
        // Apply counter filter
        if (!empty($filters['counter_id'])) {
            $sql .= " AND counter_id = ?";
            $params[] = $filters['counter_id'];
        }
        
        // Apply search filter
        if (!empty($filters['search'])) {
            $sql .= " AND (queue_number LIKE ? OR Counter_Name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC, time_served DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getAllTransactions: ' . $e->getMessage());
        return [];
    }
}

// Get transaction statistics
function getTransactionStats($period = 'today') {
    global $pdo;
    try {
        $stats = [
            'total_today' => 0,
            'completed_today' => 0,
            'waiting' => 0,
            'average_wait_time' => 0
        ];
        
        // Today's completed transactions
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as completed
            FROM transactions 
            WHERE status = 'Complete' AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['completed_today'] = $result['completed'] ?? 0;
        
        // Currently waiting
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as waiting
            FROM transactions 
            WHERE status = 'Awaiting'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['waiting'] = $result['waiting'] ?? 0;
        
        // Total today (completed + waiting with today's date)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_today
            FROM transactions 
            WHERE DATE(created_at) = CURDATE()
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_today'] = $result['total_today'] ?? 0;
        
        // Average duration from transactions table
        $stmt = $pdo->prepare("
            SELECT AVG(TIME_TO_SEC(duration)/60) as avg_duration
            FROM transactions 
            WHERE status = 'Complete' AND DATE(created_at) = CURDATE() AND duration IS NOT NULL
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['average_wait_time'] = round($result['avg_duration'] ?? 0); // In minutes
        
        return $stats;
    } catch(PDOException $e) {
        error_log('Error in getTransactionStats: ' . $e->getMessage());
        return [];
    }
}

// Get counter analytics
function getCounterAnalytics($period = 'today') {
    global $pdo;
    try {
        $sql = "
            SELECT 
                c.Counter_Name,
                COUNT(t.id) as count,
                ROUND(COUNT(t.id) * 100.0 / (
                    SELECT COUNT(*) FROM transactions 
                    WHERE status = 'Complete' AND ";
        
        if ($period === 'today') {
            $sql .= "DATE(created_at) = CURDATE()";
        } elseif ($period === 'week') {
            $sql .= "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($period === 'month') {
            $sql .= "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
        } else {
            $sql .= "1=1";
        }
        
        $sql .= "), 1) as percentage
            FROM transactions t
            LEFT JOIN counters c ON t.counter_id = c.Counter_ID
            WHERE t.status = 'Complete' AND ";
            
        if ($period === 'today') {
            $sql .= "DATE(t.created_at) = CURDATE()";
        } elseif ($period === 'week') {
            $sql .= "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($period === 'month') {
            $sql .= "MONTH(t.created_at) = MONTH(NOW()) AND YEAR(t.created_at) = YEAR(NOW())";
        } else {
            $sql .= "1=1";
        }
        
        $sql .= " AND c.Counter_Name IS NOT NULL GROUP BY c.Counter_Name, c.Counter_ID ORDER BY count DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getCounterAnalytics: ' . $e->getMessage());
        return [];
    }
}

// Get hourly analytics
function getHourlyAnalytics($date = null) {
    global $pdo;
    try {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                HOUR(time_served) as hour,
                COUNT(*) as count
            FROM transactions 
            WHERE status = 'Complete' AND DATE(time_served) = ?
            GROUP BY HOUR(time_served)
            ORDER BY hour
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getHourlyAnalytics: ' . $e->getMessage());
        return [];
    }
}

// Get counters for dropdown
function getCountersForDropdown() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Counter_ID, Counter_Name 
            FROM counters 
            ORDER BY Counter_Name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get operators for dropdown
function getOperatorsForDropdown() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT User_ID, CONCAT(Firstname, ' ', Lastname) as full_name
            FROM users 
            WHERE User_Lvl = 1 AND Status = 1
            ORDER BY Firstname, Lastname
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Move transaction from awaiting to complete
function completeTransaction($awaitingId, $duration = null) {
    global $pdo;
    try {
        // Get awaiting transaction details
        $stmt = $pdo->prepare("
            SELECT a.*, c.Counter_Name 
            FROM awaiting a 
            LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID 
            WHERE a.Awaiting_ID = ?
        ");
        $stmt->execute([$awaitingId]);
        $awaiting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$awaiting) {
            return ['success' => false, 'message' => 'Awaiting transaction not found'];
        }
        
        // Calculate duration if not provided (in minutes)
        if (!$duration) {
            $duration = 5; // Default 5 minutes
        }
        
        // Insert into transactions table as completed
        $durationTime = sprintf('%02d:%02d:%02d', 0, intval($duration), ($duration - intval($duration)) * 60);
        $stmt = $pdo->prepare("
            INSERT INTO transactions (queue_number, counter_id, User_ID, status, time_created, time_served, duration) 
            VALUES (?, ?, ?, 'Complete', NOW(), NOW(), ?)
        ");
        $stmt->execute([$awaiting['Awaiting_Number'], $awaiting['Counter_ID'], null, $durationTime]);
        
        // Insert into complete table for backward compatibility
        $stmt = $pdo->prepare("
            INSERT INTO complete (Complete_Number, End_Time, Duration, Counter_ID) 
            VALUES (?, NOW(), ?, ?)
        ");
        $stmt->execute([$awaiting['Awaiting_Number'], $durationTime, $awaiting['Counter_ID']]);
        
        // Remove from awaiting table
        $stmt = $pdo->prepare("DELETE FROM awaiting WHERE Awaiting_ID = ?");
        $stmt->execute([$awaitingId]);
        
        return ['success' => true, 'message' => 'Transaction completed successfully'];
    } catch(PDOException $e) {
        error_log('Error in completeTransaction: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Cancel/remove awaiting transaction
function cancelTransaction($awaitingId) {
    global $pdo;
    try {
        // Get awaiting transaction details for transactions table
        $stmt = $pdo->prepare("
            SELECT a.* 
            FROM awaiting a 
            WHERE a.Awaiting_ID = ?
        ");
        $stmt->execute([$awaitingId]);
        $awaiting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($awaiting) {
            // Add to transactions table as cancelled (we'll need to add this status to the enum)
            $stmt = $pdo->prepare("
                INSERT INTO transactions (queue_number, counter_id, User_ID, status, time_created) 
                VALUES (?, ?, ?, 'Awaiting', NOW())
            ");
            $stmt->execute([$awaiting['Awaiting_Number'], $awaiting['Counter_ID'], null]);
        }
        
        // Remove from awaiting table
        $stmt = $pdo->prepare("DELETE FROM awaiting WHERE Awaiting_ID = ?");
        $stmt->execute([$awaitingId]);
        
        return ['success' => true, 'message' => 'Transaction cancelled successfully'];
    } catch(PDOException $e) {
        error_log('Error in cancelTransaction: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Add new awaiting transaction
function addAwaitingTransaction($queueNumber, $counterId) {
    global $pdo;
    try {
        // Insert into awaiting table
        $stmt = $pdo->prepare("
            INSERT INTO awaiting (Awaiting_Number, Counter_ID) 
            VALUES (?, ?)
        ");
        $stmt->execute([$queueNumber, $counterId]);
        $awaitingId = $pdo->lastInsertId();
        
        // Also add to transactions table as waiting
        $stmt = $pdo->prepare("
            INSERT INTO transactions (queue_number, counter_id, User_ID, status, time_created) 
            VALUES (?, ?, ?, 'Awaiting', NOW())
        ");
        $stmt->execute([$queueNumber, $counterId, null]);
        
        return ['success' => true, 'message' => 'Transaction added to queue successfully', 'id' => $awaitingId];
    } catch(PDOException $e) {
        error_log('Error in addAwaitingTransaction: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update transaction operator
function updateTransactionOperator($transactionId, $operatorId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            UPDATE transactions 
            SET User_ID = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$operatorId, $transactionId]);
        
        return ['success' => true, 'message' => 'Operator assigned successfully'];
    } catch(PDOException $e) {
        error_log('Error in updateTransactionOperator: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Get transaction by ID
function getTransactionById($transactionId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT 
                t.*,
                c.Counter_Name,
                CONCAT(u.Firstname, ' ', u.Lastname) as operator_name
            FROM transactions t
            LEFT JOIN counters c ON t.counter_id = c.Counter_ID
            LEFT JOIN users u ON t.User_ID = u.User_ID
            WHERE t.id = ?
        ");
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log('Error in getTransactionById: ' . $e->getMessage());
        return false;
    }
}

// Generate sample data using existing tables
function generateSampleTransactions() {
    global $pdo;
    try {
        // Check if we have counters
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM counters");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            return ['success' => false, 'message' => 'Please create counters first'];
        }
        
        // Get available counters
        $stmt = $pdo->prepare("SELECT Counter_ID FROM counters LIMIT 5");
        $stmt->execute();
        $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add some sample completed transactions
        $sampleCompleted = [];
        for ($i = 1; $i <= 20; $i++) {
            $counter = $counters[array_rand($counters)];
            $queueNumber = chr(65 + ($i % 5)) . str_pad($i, 3, '0', STR_PAD_LEFT);
            $duration = sprintf('%02d:%02d:%02d', 0, rand(2, 15), rand(0, 59)); // 2-15 minutes
            $endTime = date('Y-m-d H:i:s', strtotime('-' . rand(1, 480) . ' minutes'));
            
            $sampleCompleted[] = [
                'Complete_Number' => $queueNumber,
                'End_Time' => $endTime,
                'Duration' => $duration,
                'Counter_ID' => $counter['Counter_ID']
            ];
        }
        
        // Insert completed transactions
        $stmt = $pdo->prepare("
            INSERT INTO complete (Complete_Number, End_Time, Duration, Counter_ID) 
            VALUES (?, ?, ?, ?)
        ");
        
        $transactionStmt = $pdo->prepare("
            INSERT INTO transactions (queue_number, counter_id, User_ID, status, time_created, time_served, duration) 
            VALUES (?, ?, ?, 'Complete', ?, ?, ?)
        ");
        
        foreach ($sampleCompleted as $data) {
            // Insert into complete table
            $stmt->execute([
                $data['Complete_Number'], $data['End_Time'], 
                $data['Duration'], $data['Counter_ID']
            ]);
            
            // Also insert into transactions table
            $transactionStmt->execute([
                $data['Complete_Number'], $data['Counter_ID'], null, 
                $data['End_Time'], $data['End_Time'], $data['Duration']
            ]);
        }
        
        // Add some sample awaiting transactions
        $awaitingStmt = $pdo->prepare("
            INSERT INTO awaiting (Awaiting_Number, Counter_ID) 
            VALUES (?, ?)
        ");
        
        $awaitingTransactionStmt = $pdo->prepare("
            INSERT INTO transactions (queue_number, counter_id, User_ID, status, time_created) 
            VALUES (?, ?, ?, 'Awaiting', NOW())
        ");
        
        for ($i = 1; $i <= 5; $i++) {
            $counter = $counters[array_rand($counters)];
            $queueNumber = chr(65 + rand(0, 4)) . str_pad(rand(100, 200), 3, '0', STR_PAD_LEFT);
            
            // Insert into awaiting table
            $awaitingStmt->execute([$queueNumber, $counter['Counter_ID']]);
            
            // Also insert into transactions table
            $awaitingTransactionStmt->execute([$queueNumber, $counter['Counter_ID'], null]);
        }
        
        return ['success' => true, 'message' => 'Sample transactions generated successfully'];
    } catch(PDOException $e) {
        error_log('Error in generateSampleTransactions: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
