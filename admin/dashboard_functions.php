<?php
require_once __DIR__ . '/../connections/database.php';

/**
 * Dashboard Functions for QueueingPro Admin Panel
 * Provides real-time data for the dashboard based on actual system flow
 */

// Get dashboard statistics
function getDashboardStats() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total customers served today from complete table (using Date_Completed)
        $stmt = $pdo->query("
            SELECT COUNT(*) as total_served 
            FROM complete 
            WHERE Date_Completed = CURDATE()
        ");
        $stats['customers_served'] = $stmt->fetch()['total_served'] ?? 0;
        
        // Current customers waiting from awaiting table
        $stmt = $pdo->query("SELECT COUNT(*) as total_waiting FROM awaiting");
        $stats['customers_waiting'] = $stmt->fetch()['total_waiting'] ?? 0;
        
        // Active counters (counters with assigned operators and active status)
        $stmt = $pdo->query("
            SELECT COUNT(*) as active_counters 
            FROM counters c 
            JOIN users u ON c.User_ID = u.User_ID 
            WHERE c.Counter_Status = 'Active'
        ");
        $stats['active_counters'] = $stmt->fetch()['active_counters'] ?? 0;
        
        // Average service time calculation (only for regular customers, not initial numbers)
        $stmt = $pdo->query("
            SELECT AVG(TIME_TO_SEC(Duration) / 60) as avg_service_time 
            FROM complete 
            WHERE Date_Completed = CURDATE() 
            AND Duration IS NOT NULL 
            AND Complete_Number NOT REGEXP '^[A-Z]000$'
        ");
        $avgTime = $stmt->fetch()['avg_service_time'] ?? 0;
        $stats['avg_wait_time'] = round($avgTime, 1);
        
        return $stats;
    } catch (PDOException $e) {
        return [
            'customers_served' => 0,
            'customers_waiting' => 0,
            'active_counters' => 0,
            'avg_wait_time' => 0
        ];
    }
}

// Get recent activity from complete table
function getRecentActivity($limit = 5) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.Complete_Number,
                c.End_Time,
                c.Duration,
                c.Date_Completed,
                cnt.Counter_Name,
                u.Firstname,
                u.Lastname,
                TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(c.Date_Completed, ' ', COALESCE(c.End_Time, '12:00:00'))) * -1 as minutes_ago
            FROM complete c
            LEFT JOIN counters cnt ON c.Counter_ID = cnt.Counter_ID
            LEFT JOIN users u ON cnt.User_ID = u.User_ID
            WHERE c.Date_Completed = CURDATE()
            ORDER BY c.Complete_ID DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get counter performance data
function getCounterPerformance() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                cnt.Counter_Name,
                cnt.Counter_Status,
                COUNT(c.Complete_ID) as customers_served,
                AVG(TIME_TO_SEC(c.Duration) / 60) as avg_service_time,
                CONCAT(u.Firstname, ' ', u.Lastname) as operator_name
            FROM counters cnt
            LEFT JOIN complete c ON cnt.Counter_ID = c.Counter_ID AND c.Date_Completed = CURDATE()
            LEFT JOIN users u ON cnt.User_ID = u.User_ID
            GROUP BY cnt.Counter_ID, cnt.Counter_Name, cnt.Counter_Status, u.Firstname, u.Lastname
            ORDER BY customers_served DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get system status
function getSystemStatus() {
    global $pdo;
    
    try {
        $status = [];
        
        // Database connection test
        $status['database'] = 'Connected';
        
        // Check if there are active counters
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM counters WHERE Counter_Status = 'Active'");
        $activeCounters = $stmt->fetch()['count'];
        $status['queue_system'] = $activeCounters > 0 ? 'Online' : 'Idle';
        
        // Check if there's recent activity (last 5 minutes)
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM complete 
            WHERE Date_Completed = CURDATE() 
            AND End_Time IS NOT NULL 
            AND TIMESTAMPDIFF(MINUTE, CONCAT(Date_Completed, ' ', End_Time), NOW()) <= 5
        ");
        $recentActivity = $stmt->fetch()['count'];
        $status['display_screen'] = $recentActivity > 0 ? 'Active' : 'Standby';
        
        // Check active videos
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM videos WHERE Status = 'Active'");
        $activeVideos = $stmt->fetch()['count'];
        $status['audio_system'] = $activeVideos > 0 ? 'Playing' : 'Ready';
        
        return $status;
    } catch (PDOException $e) {
        return [
            'database' => 'Error',
            'queue_system' => 'Unknown',
            'display_screen' => 'Unknown',
            'audio_system' => 'Unknown'
        ];
    }
}

// Get hourly performance data for today
function getHourlyPerformance() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                HOUR(End_Time) as hour,
                COUNT(*) as customers_served
            FROM complete 
            WHERE Date_Completed = CURDATE() AND End_Time IS NOT NULL
            GROUP BY HOUR(End_Time)
            ORDER BY hour
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get current awaiting queue summary
function getAwaitingQueueSummary() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("
            SELECT 
                cnt.Counter_Name,
                COUNT(a.Awaiting_ID) as queue_count,
                MIN(a.Awaiting_Number) as next_number
            FROM awaiting a
            JOIN counters cnt ON a.Counter_ID = cnt.Counter_ID
            GROUP BY a.Counter_ID, cnt.Counter_Name
            ORDER BY queue_count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Get efficiency metrics
function getEfficiencyMetrics() {
    global $pdo;
    
    try {
        $metrics = [];
        
        // Service efficiency (completed vs total processed)
        $stmt = $pdo->query("
            SELECT 
                (SELECT COUNT(*) FROM complete WHERE Date_Completed = CURDATE()) as completed,
                (SELECT COUNT(*) FROM awaiting) as waiting
        ");
        $data = $stmt->fetch();
        $total = $data['completed'] + $data['waiting'];
        $total = max($total, 1); // Avoid division by zero
        $metrics['service_efficiency'] = $total > 0 ? round(($data['completed'] / $total) * 100, 1) : 0;
        
        // Average service time (excluding initial numbers like A000, B000)
        $stmt = $pdo->query("
            SELECT AVG(TIME_TO_SEC(Duration) / 60) as avg_time 
            FROM complete 
            WHERE Date_Completed = CURDATE() 
            AND Duration IS NOT NULL 
            AND Complete_Number NOT REGEXP '^[A-Z]000$'
        ");
        $metrics['avg_service_time'] = round($stmt->fetch()['avg_time'] ?? 0, 1);
        
        return $metrics;
    } catch (PDOException $e) {
        return [
            'service_efficiency' => 0,
            'avg_service_time' => 0
        ];
    }
}
?>
