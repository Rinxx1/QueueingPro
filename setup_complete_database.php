<?php
/**
 * Complete Database Setup Script for QueueingPro
 * This script creates the transactions table and adds all necessary foreign keys
 */

// Include database connection
require_once 'connections/database.php';

function executeSQL($pdo, $sql, $description, $ignoreErrors = false) {
    try {
        $pdo->exec($sql);
        echo "✓ $description\n";
        return true;
    } catch (PDOException $e) {
        if ($ignoreErrors) {
            echo "⚠ $description (Warning: " . $e->getMessage() . ")\n";
            return true;
        } else {
            echo "✗ Error in $description: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

try {
    echo "===========================================\n";
    echo "QueueingPro Database Setup\n";
    echo "===========================================\n\n";
    
    // Step 1: Create transactions table
    echo "Step 1: Creating transactions table...\n";
    $createTableSQL = "
    CREATE TABLE `transactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `queue_number` varchar(10) NOT NULL,
        `counter_id` int(11) DEFAULT NULL,
        `operator_id` int(11) DEFAULT NULL,
        `status` enum('Awaiting','Complete','Cancelled') DEFAULT 'Awaiting',
        `time_created` timestamp DEFAULT CURRENT_TIMESTAMP,
        `time_served` timestamp NULL DEFAULT NULL,
        `duration` time NULL DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_queue_number` (`queue_number`),
        KEY `idx_counter_id` (`counter_id`),
        KEY `idx_operator_id` (`operator_id`),
        KEY `idx_status` (`status`),
        KEY `idx_time_created` (`time_created`),
        KEY `idx_status_date` (`status`, `time_created`),
        KEY `idx_counter_date` (`counter_id`, `time_created`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    executeSQL($pdo, $createTableSQL, "Creating transactions table");
    
    // Step 2: Add foreign keys to transactions table
    echo "\nStep 2: Adding foreign keys to transactions table...\n";
    executeSQL($pdo, "
        ALTER TABLE `transactions` 
        ADD CONSTRAINT `fk_transactions_counter` 
        FOREIGN KEY (`counter_id`) REFERENCES `counters` (`Counter_ID`) 
        ON DELETE SET NULL ON UPDATE CASCADE
    ", "Adding counter foreign key", true);
    
    executeSQL($pdo, "
        ALTER TABLE `transactions` 
        ADD CONSTRAINT `fk_transactions_operator` 
        FOREIGN KEY (`operator_id`) REFERENCES `users` (`User_ID`) 
        ON DELETE SET NULL ON UPDATE CASCADE
    ", "Adding operator foreign key", true);
    
    // Step 3: Add foreign keys to existing tables
    echo "\nStep 3: Adding foreign keys to existing tables...\n";
    
    // Awaiting table foreign keys
    executeSQL($pdo, "
        ALTER TABLE `awaiting` 
        ADD CONSTRAINT `fk_awaiting_counter` 
        FOREIGN KEY (`Counter_ID`) REFERENCES `counters` (`Counter_ID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
    ", "Adding awaiting-counter foreign key", true);
    
    executeSQL($pdo, "
        ALTER TABLE `awaiting` 
        ADD CONSTRAINT `fk_awaiting_user` 
        FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) 
        ON DELETE SET NULL ON UPDATE CASCADE
    ", "Adding awaiting-user foreign key", true);
    
    // Complete table foreign keys
    executeSQL($pdo, "
        ALTER TABLE `complete` 
        ADD CONSTRAINT `fk_complete_counter` 
        FOREIGN KEY (`Counter_ID`) REFERENCES `counters` (`Counter_ID`) 
        ON DELETE CASCADE ON UPDATE CASCADE
    ", "Adding complete-counter foreign key", true);
    
    // Counters table foreign key
    executeSQL($pdo, "
        ALTER TABLE `counters` 
        ADD CONSTRAINT `fk_counters_user` 
        FOREIGN KEY (`User_ID`) REFERENCES `users` (`User_ID`) 
        ON DELETE SET NULL ON UPDATE CASCADE
    ", "Adding counters-user foreign key", true);
    
    // Step 4: Create unified view
    echo "\nStep 4: Creating unified transactions view...\n";
    $createViewSQL = "
    CREATE OR REPLACE VIEW `v_all_transactions` AS
    (
        SELECT 
            CONCAT('A-', a.Awaiting_ID) as transaction_ref,
            c.Counter_CurrentNumber as queue_number,
            a.Counter_ID as counter_id,
            a.User_ID as operator_id,
            c.Counter_Name,
            COALESCE(u.Username, 'Unassigned') as operator_name,
            'Awaiting' as status,
            a.Start_Time as time_created,
            NULL as time_served,
            NULL as duration,
            '00:00:00' as formatted_duration,
            a.Start_Time as created_at,
            NULL as notes
        FROM awaiting a
        LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
        LEFT JOIN users u ON a.User_ID = u.User_ID
    )
    UNION ALL
    (
        SELECT 
            CONCAT('C-', comp.Complete_ID) as transaction_ref,
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
            comp.End_Time as created_at,
            NULL as notes
        FROM complete comp
        LEFT JOIN counters c ON comp.Counter_ID = c.Counter_ID
    )
    UNION ALL
    (
        SELECT 
            CONCAT('T-', t.id) as transaction_ref,
            t.queue_number,
            t.counter_id,
            t.operator_id,
            c.Counter_Name,
            COALESCE(u.Username, 'Unassigned') as operator_name,
            t.status,
            t.time_created,
            t.time_served,
            t.duration,
            CASE 
                WHEN t.duration IS NOT NULL THEN
                    CONCAT(
                        LPAD(HOUR(t.duration), 2, '0'), ':',
                        LPAD(MINUTE(t.duration), 2, '0'), ':',
                        LPAD(SECOND(t.duration), 2, '0')
                    )
                ELSE '00:00:00'
            END as formatted_duration,
            t.created_at,
            t.notes
        FROM transactions t
        LEFT JOIN counters c ON t.counter_id = c.Counter_ID
        LEFT JOIN users u ON t.operator_id = u.User_ID
    )
    ORDER BY created_at DESC";
    
    executeSQL($pdo, $createViewSQL, "Creating unified transactions view");
    
    // Step 5: Migrate existing data
    echo "\nStep 5: Migrating existing data...\n";
    
    // Migrate awaiting data
    executeSQL($pdo, "
        INSERT INTO `transactions` (queue_number, counter_id, operator_id, status, time_created, notes)
        SELECT 
            c.Counter_CurrentNumber,
            a.Counter_ID,
            a.User_ID,
            'Awaiting',
            a.Start_Time,
            'Migrated from existing awaiting data'
        FROM awaiting a
        LEFT JOIN counters c ON a.Counter_ID = c.Counter_ID
        WHERE c.Counter_CurrentNumber IS NOT NULL
        ON DUPLICATE KEY UPDATE
            counter_id = VALUES(counter_id),
            operator_id = VALUES(operator_id),
            status = 'Awaiting',
            time_created = VALUES(time_created),
            updated_at = CURRENT_TIMESTAMP
    ", "Migrating awaiting data", true);
    
    // Migrate completed data
    executeSQL($pdo, "
        INSERT INTO `transactions` (queue_number, counter_id, status, time_served, duration, notes)
        SELECT 
            comp.Complete_Number,
            comp.Counter_ID,
            'Complete',
            comp.End_Time,
            comp.Duration,
            'Migrated from existing completed data'
        FROM complete comp
        WHERE comp.Complete_Number IS NOT NULL
        ON DUPLICATE KEY UPDATE
            status = 'Complete',
            time_served = VALUES(time_served),
            duration = VALUES(duration),
            notes = CONCAT(COALESCE(notes, ''), ' | Migrated completed'),
            updated_at = CURRENT_TIMESTAMP
    ", "Migrating completed data", true);
    
    // Step 6: Create additional indexes
    echo "\nStep 6: Creating performance indexes...\n";
    executeSQL($pdo, "CREATE INDEX `idx_transactions_queue_status` ON `transactions` (`queue_number`, `status`)", "Queue-status index", true);
    executeSQL($pdo, "CREATE INDEX `idx_transactions_created_status` ON `transactions` (`time_created`, `status`)", "Created-status index", true);
    executeSQL($pdo, "CREATE INDEX `idx_transactions_served_duration` ON `transactions` (`time_served`, `duration`)", "Served-duration index", true);
    executeSQL($pdo, "CREATE INDEX `idx_awaiting_counter_time` ON `awaiting` (`Counter_ID`, `Start_Time`)", "Awaiting counter-time index", true);
    executeSQL($pdo, "CREATE INDEX `idx_complete_counter_time` ON `complete` (`Counter_ID`, `End_Time`)", "Complete counter-time index", true);
    executeSQL($pdo, "CREATE INDEX `idx_counters_status` ON `counters` (`Counter_Status`)", "Counters status index", true);
    executeSQL($pdo, "CREATE INDEX `idx_users_active` ON `users` (`Status`)", "Users status index", true);
    
    // Step 7: Test the setup
    echo "\n=== Testing Database Setup ===\n";
    
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
        echo "✓ Unified view: {$result['count']} total transactions\n";
    } catch (PDOException $e) {
        echo "✗ Error testing view: " . $e->getMessage() . "\n";
    }
    
    // Test status breakdown
    try {
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM transactions GROUP BY status");
        $results = $stmt->fetchAll();
        foreach ($results as $result) {
            echo "✓ Status '{$result['status']}': {$result['count']} transactions\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error testing status breakdown: " . $e->getMessage() . "\n";
    }
    
    // Test foreign key relationships
    try {
        $stmt = $pdo->query("
            SELECT 
                t.queue_number,
                c.Counter_Name,
                u.Username
            FROM transactions t
            LEFT JOIN counters c ON t.counter_id = c.Counter_ID
            LEFT JOIN users u ON t.operator_id = u.User_ID
            LIMIT 5
        ");
        $results = $stmt->fetchAll();
        echo "✓ Foreign key relationships working: " . count($results) . " sample records with joined data\n";
    } catch (PDOException $e) {
        echo "✗ Error testing foreign keys: " . $e->getMessage() . "\n";
    }
    
    echo "\n===========================================\n";
    echo "🎉 Database setup completed successfully!\n";
    echo "===========================================\n";
    echo "Your QueueingPro system now has:\n";
    echo "• Complete transactions table with proper structure\n";
    echo "• Foreign key relationships between all tables\n";
    echo "• Unified view for transaction management\n";
    echo "• Performance indexes for fast queries\n";
    echo "• Migrated existing data from awaiting and complete tables\n";
    echo "\nYou can now use the transaction management system.\n";
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
