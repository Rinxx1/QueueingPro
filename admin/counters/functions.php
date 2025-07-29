<?php
require_once '../../connections/database.php';

// Get all counters
function getAllCounters() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.Username, u.Firstname, u.Lastname,
                   CONCAT(u.Firstname, ' ', u.Lastname) as OperatorName
            FROM counters c 
            LEFT JOIN users u ON c.User_ID = u.User_ID 
            ORDER BY c.Counter_ID ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get next alphabetical current number
function getNextCounterName() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT Counter_CurrentNumber FROM counters ORDER BY Counter_CurrentNumber ASC");
        $stmt->execute();
        $counters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $usedLetters = [];
        foreach ($counters as $counter) {
            $currentNumber = $counter['Counter_CurrentNumber'];
            if (preg_match('/^([A-Z])/', $currentNumber, $matches)) {
                $usedLetters[] = $matches[1];
            }
        }
        
        // Find the first available letter
        for ($i = 0; $i < 26; $i++) {
            $letter = chr(65 + $i); // A = 65, B = 66, etc.
            if (!in_array($letter, $usedLetters)) {
                return $letter . '000'; // Changed from 001 to 000
            }
        }
        
        // If all letters are used, return null or handle as needed
        return null;
    } catch(PDOException $e) {
        return 'A000'; // Default fallback changed to A000
    }
}

// Get counter by ID
function getCounterById($counterId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM counters WHERE Counter_ID = ?");
        $stmt->execute([$counterId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Create new counter
function createCounter($counterName, $counterDescription, $currentNumber, $status, $userId) {
    global $pdo;
    try {
        // Check if counter name already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM counters WHERE Counter_Name = ?");
        $checkStmt->execute([$counterName]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Counter name already exists'];
        }

        // Check if user is already assigned to another counter
        if ($userId) {
            $checkUserStmt = $pdo->prepare("SELECT Counter_Name FROM counters WHERE User_ID = ?");
            $checkUserStmt->execute([$userId]);
            $existingCounter = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
            if ($existingCounter) {
                return ['success' => false, 'message' => 'This operator is already assigned to ' . $existingCounter['Counter_Name']];
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO counters (Counter_Name, Counter_Description, Counter_CurrentNumber, Counter_Status, User_ID) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$counterName, $counterDescription, $currentNumber, $status, $userId]);
        
        return ['success' => true, 'message' => 'Counter created successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update counter
function updateCounter($counterId, $counterName, $counterDescription, $currentNumber, $status, $userId) {
    global $pdo;
    try {
        // Check if counter name exists for other counters
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM counters WHERE Counter_Name = ? AND Counter_ID != ?");
        $checkStmt->execute([$counterName, $counterId]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Counter name already exists'];
        }

        // Check if user is already assigned to another counter (excluding current counter)
        if ($userId) {
            $checkUserStmt = $pdo->prepare("SELECT Counter_Name FROM counters WHERE User_ID = ? AND Counter_ID != ?");
            $checkUserStmt->execute([$userId, $counterId]);
            $existingCounter = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
            if ($existingCounter) {
                return ['success' => false, 'message' => 'This operator is already assigned to ' . $existingCounter['Counter_Name']];
            }
        }

        $stmt = $pdo->prepare("
            UPDATE counters 
            SET Counter_Name = ?, Counter_Description = ?, Counter_CurrentNumber = ?, Counter_Status = ?, User_ID = ?
            WHERE Counter_ID = ?
        ");
        $stmt->execute([$counterName, $counterDescription, $currentNumber, $status, $userId, $counterId]);
        
        return ['success' => true, 'message' => 'Counter updated successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Delete counter
function deleteCounter($counterId) {
    global $pdo;
    try {
        // Check if counter is assigned to any users
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Counter_ID = ?");
        $checkStmt->execute([$counterId]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cannot delete counter that is assigned to users'];
        }

        $stmt = $pdo->prepare("DELETE FROM counters WHERE Counter_ID = ?");
        $stmt->execute([$counterId]);
        
        return ['success' => true, 'message' => 'Counter deleted successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Get users for dropdown (controllers only) - exclude already assigned operators
function getUsersForDropdown() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT u.User_ID, u.Username, u.Firstname, u.Lastname, 
                   CONCAT(u.Firstname, ' ', u.Lastname) as FullName
            FROM users u 
            WHERE u.User_Lvl = 1 AND u.Status = 1
            AND u.User_ID NOT IN (
                SELECT c.User_ID 
                FROM counters c 
                WHERE c.User_ID IS NOT NULL
            )
            ORDER BY u.Firstname, u.Lastname
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get available users for a specific counter (when editing, include current operator)
function getAvailableUsersForCounter($counterId = null) {
    global $pdo;
    try {
        $sql = "
            SELECT u.User_ID, u.Username, u.Firstname, u.Lastname, 
                   CONCAT(u.Firstname, ' ', u.Lastname) as FullName,
                   c.Counter_ID as CurrentCounterID,
                   c.Counter_Name as CurrentCounterName
            FROM users u 
            LEFT JOIN counters c ON u.User_ID = c.User_ID
            WHERE u.User_Lvl = 1 AND u.Status = 1
            AND (c.User_ID IS NULL OR c.Counter_ID = ?)
            ORDER BY u.Firstname, u.Lastname
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$counterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get counter statistics
function getCounterStats() {
    global $pdo;
    try {
        $stats = [
            'total' => 0,
            'active' => 0,
            'break' => 0,
            'offline' => 0
        ];

        // Get total counters
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM counters");
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();

        // Get counters by status
        $stmt = $pdo->prepare("SELECT Counter_Status, COUNT(*) as count FROM counters GROUP BY Counter_Status");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['Counter_Status']) {
                case 'Active':
                    $stats['active'] = $row['count'];
                    break;
                case 'Break':
                    $stats['break'] = $row['count'];
                    break;
                case 'Offline':
                    $stats['offline'] = $row['count'];
                    break;
            }
        }

        return $stats;
    } catch(PDOException $e) {
        return ['total' => 0, 'active' => 0, 'break' => 0, 'offline' => 0];
    }
}

// Get all awaiting entries with counter information
function getAllAwaiting() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, c.Counter_Name, c.Counter_CurrentNumber, c.Counter_Status
            FROM awaiting a
            INNER JOIN counters c ON a.Counter_ID = c.Counter_ID
            ORDER BY a.Awaiting_ID ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get awaiting by ID
function getAwaitingById($awaitingId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT a.*, c.Counter_Name, c.Counter_CurrentNumber
            FROM awaiting a
            INNER JOIN counters c ON a.Counter_ID = c.Counter_ID
            WHERE a.Awaiting_ID = ?
        ");
        $stmt->execute([$awaitingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Create new awaiting entry
function createAwaiting($awaitingNumber, $counterId) {
    global $pdo;
    try {
        // Check if awaiting number already exists for this counter
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM awaiting WHERE Awaiting_Number = ? AND Counter_ID = ?");
        $checkStmt->execute([$awaitingNumber, $counterId]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Awaiting number already exists for this counter'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO awaiting (Awaiting_Number, Counter_ID) 
            VALUES (?, ?)
        ");
        $stmt->execute([$awaitingNumber, $counterId]);
        
        return ['success' => true, 'message' => 'Awaiting entry created successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update awaiting entry
function updateAwaiting($awaitingId, $awaitingNumber, $counterId) {
    global $pdo;
    try {
        // Check if awaiting number exists for other entries in the same counter
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM awaiting WHERE Awaiting_Number = ? AND Counter_ID = ? AND Awaiting_ID != ?");
        $checkStmt->execute([$awaitingNumber, $counterId, $awaitingId]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Awaiting number already exists for this counter'];
        }

        $stmt = $pdo->prepare("
            UPDATE awaiting 
            SET Awaiting_Number = ?, Counter_ID = ?
            WHERE Awaiting_ID = ?
        ");
        $stmt->execute([$awaitingNumber, $counterId, $awaitingId]);
        
        return ['success' => true, 'message' => 'Awaiting entry updated successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Delete awaiting entry
function deleteAwaiting($awaitingId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM awaiting WHERE Awaiting_ID = ?");
        $stmt->execute([$awaitingId]);
        
        return ['success' => true, 'message' => 'Awaiting entry deleted successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Get awaiting queue for a specific counter
function getAwaitingQueueForCounter($counterId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT Awaiting_ID, Awaiting_Number, Counter_ID
            FROM awaiting
            WHERE Counter_ID = ?
            ORDER BY Awaiting_Number ASC
        ");
        $stmt->execute([$counterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Create system_logs table if it doesn't exist
function createSystemLogsTable() {
    global $pdo;
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                log_action VARCHAR(50) NOT NULL,
                log_details TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        return true;
    } catch(PDOException $e) {
        error_log('Error creating system_logs table: ' . $e->getMessage());
        return false;
    }
}

// Reset all queue numbers daily
function resetDailyQueueNumbers() {
    global $pdo;
    try {
        // Ensure system_logs table exists
        createSystemLogsTable();
        
        // Reset all counters to their base numbers (A000, B000, etc.)
        $stmt = $pdo->prepare("
            UPDATE counters 
            SET Counter_CurrentNumber = CONCAT(LEFT(Counter_CurrentNumber, 1), '000')
            WHERE Counter_CurrentNumber REGEXP '^[A-Z][0-9]{3}$'
        ");
        $stmt->execute();
        
        // Clear any awaiting queues
        $stmt = $pdo->prepare("DELETE FROM awaiting");
        $stmt->execute();
        
        // Log the reset action
        $stmt = $pdo->prepare("
            INSERT INTO system_logs (log_date, log_action, log_details) 
            VALUES (NOW(), 'DAILY_RESET', 'Queue numbers reset to 000 for all counters')
        ");
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Queue numbers reset successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Check if daily reset is needed and perform it
function checkAndPerformDailyReset() {
    global $pdo;
    try {
        // Ensure system_logs table exists
        createSystemLogsTable();
        
        // Check if we've already reset today
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as reset_count 
            FROM system_logs 
            WHERE DATE(log_date) = CURDATE() 
            AND log_action = 'DAILY_RESET'
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['reset_count'] == 0) {
            // No reset performed today, do it now
            return resetDailyQueueNumbers();
        }
        
        return ['success' => true, 'message' => 'Reset already performed today'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Manual reset function for admin
function manualResetQueueNumbers() {
    global $pdo;
    try {
        // Ensure system_logs table exists
        createSystemLogsTable();
        
        // Reset all counters to their base numbers (A000, B000, etc.)
        $stmt = $pdo->prepare("
            UPDATE counters 
            SET Counter_CurrentNumber = CONCAT(LEFT(Counter_CurrentNumber, 1), '000')
            WHERE Counter_CurrentNumber REGEXP '^[A-Z][0-9]{3}$'
        ");
        $stmt->execute();
        
        // Clear any awaiting queues
        $stmt = $pdo->prepare("DELETE FROM awaiting");
        $stmt->execute();
        
        // Log the manual reset action
        $stmt = $pdo->prepare("
            INSERT INTO system_logs (log_date, log_action, log_details) 
            VALUES (NOW(), 'MANUAL_RESET', 'Queue numbers manually reset by admin')
        ");
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Queue numbers reset successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
