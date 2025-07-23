<?php
/**
 * Simple Database Update Script for QueueingPro
 * This script creates the transactions table and updates the structure step by step
 */

// Include database connection
require_once 'connections/database.php';

function executeSQL($pdo, $sql, $description) {
    try {
        $pdo->exec($sql);
        echo "✓ $description\n";
        return true;
    } catch (PDOException $e) {
        echo "✗ Error in $description: " . $e->getMessage() . "\n";
        return false;
    }
}

try {
    echo "Starting database updates for QueueingPro...\n\n";
    
    // 1. Drop existing transactions table if it exists
    executeSQL($pdo, "DROP TABLE IF EXISTS `transactions`", "Dropping existing transactions table");
    
    // 2. Create the new transactions table
    $createTableSQL = "
    CREATE TABLE `transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `queue_number` varchar(10) NOT NULL,
        `counter_id` int(11) DEFAULT NULL,
        `operator_id` int(11) DEFAULT NULL,
        `status` enum('Awaiting','Complete') DEFAULT 'Awaiting',
        `time_created` timestamp DEFAULT CURRENT_TIMESTAMP,
        `time_served` timestamp NULL DEFAULT NULL,
        `duration` time NULL DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `queue_number` (`queue_number`),
        KEY `counter_id` (`counter_id`),
        KEY `operator_id` (`operator_id`),
        KEY `status` (`status`),
        KEY `time_created` (`time_created`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    executeSQL($pdo, $createTableSQL, "Creating transactions table");
    
    // 3. Create indexes for better performance
    executeSQL($pdo, "CREATE INDEX `idx_transactions_status_date` ON `transactions` (`status`, `time_created`)", "Creating status-date index");
    executeSQL($pdo, "CREATE INDEX `idx_transactions_counter_date` ON `transactions` (`counter_id`, `time_created`)", "Creating counter-date index");
    
    // 4. Create the view for unified transaction data
    $createViewSQL = "
    CREATE OR REPLACE VIEW `v_all_transactions` AS
    (
        SELECT 
            a.Awaiting_ID as transaction_id,
            c.Counter_CurrentNumber as queue_number,
            a.Counter_ID as counter_id,
            a.User_ID as operator_id,
            c.Counter_Name,
            u.Username as operator_name,
            'Awaiting' as status,
            a.Start_Time as time_created,
            NULL as time_served,
            NULL as duration,
            '00:00:00' as formatted_duration,
            a.Start_Time as created_at
        FROM awaiting a
        LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
        LEFT JOIN users u ON a.User_ID = u.User_ID
    )
    UNION ALL
    (
        SELECT 
            comp.Complete_ID as transaction_id,
            comp.Complete_Number as queue_number,
            comp.Counter_ID as counter_id,
            NULL as operator_id,
            c.Counter_Name,
            'System' as operator_name,
            'Complete' as status,
            NULL as time_created,
            comp.End_Time as time_served,
            comp.Duration as duration,
            CONCAT(
                LPAD(HOUR(comp.Duration), 2, '0'), ':',
                LPAD(MINUTE(comp.Duration), 2, '0'), ':',
                LPAD(SECOND(comp.Duration), 2, '0')
            ) as formatted_duration,
            comp.End_Time as created_at
        FROM complete comp
        LEFT JOIN counters c ON comp.Counter_ID = c.Counter_ID
    )
    ORDER BY created_at DESC";
    
    executeSQL($pdo, $createViewSQL, "Creating unified transactions view");
    
    // 5. Populate transactions table with existing data from awaiting table
    $populateAwaitingSQL = "
    INSERT INTO `transactions` (queue_number, counter_id, operator_id, status, time_created)
    SELECT 
        c.Counter_CurrentNumber,
        a.Counter_ID,
        a.User_ID,
        'Awaiting',
        a.Start_Time
    FROM awaiting a
    LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
    ON DUPLICATE KEY UPDATE
        counter_id = VALUES(counter_id),
        operator_id = VALUES(operator_id),
        status = 'Awaiting',
        time_created = VALUES(time_created)";
    
    executeSQL($pdo, $populateAwaitingSQL, "Populating transactions with awaiting data");
    
    // 6. Populate transactions table with existing data from complete table
    $populateCompleteSQL = "
    INSERT INTO `transactions` (queue_number, counter_id, status, time_served, duration)
    SELECT 
        comp.Complete_Number,
        comp.Counter_ID,
        'Complete',
        comp.End_Time,
        comp.Duration
    FROM complete comp
    ON DUPLICATE KEY UPDATE
        status = 'Complete',
        time_served = VALUES(time_served),
        duration = VALUES(duration)";
    
    executeSQL($pdo, $populateCompleteSQL, "Populating transactions with completed data");
    
    // 7. Test the new structure
    echo "\n=== Testing New Structure ===\n";
    
    // Test transactions table
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM transactions");
        $result = $stmt->fetch();
        echo "✓ Transactions table: {$result['count']} records\n";
    } catch (PDOException $e) {
        echo "✗ Error testing transactions table: " . $e->getMessage() . "\n";
    }
    
    // Test view
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM v_all_transactions");
        $result = $stmt->fetch();
        echo "✓ Transactions view: {$result['count']} records\n";
    } catch (PDOException $e) {
        echo "✗ Error testing transactions view: " . $e->getMessage() . "\n";
    }
    
    // Test data integrity
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM transactions GROUP BY status");
        $results = $stmt->fetchAll();
        foreach ($results as $result) {
            echo "✓ Status '{$result['status']}': {$result['count']} transactions\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error testing data integrity: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Database update completed successfully!\n";
    echo "The transactions table has been created and populated with existing data.\n";
    echo "Your transaction management system is now ready to use.\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
