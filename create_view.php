<?php
require_once 'connections/database.php';

try {
    // Check if the view exists
    $stmt = $pdo->prepare('SHOW TABLES LIKE "v_all_transactions"');
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "View v_all_transactions already exists\n";
        
        // Show some sample data
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM v_all_transactions');
        $stmt->execute();
        $count = $stmt->fetch();
        echo "View contains {$count['count']} records\n";
    } else {
        echo "View v_all_transactions does not exist\n";
        echo "Creating the view...\n";
        
        $createView = "
            CREATE VIEW v_all_transactions AS
            SELECT 
                t.id as transaction_id,
                CONCAT('TXN-', LPAD(t.id, 6, '0')) as transaction_ref,
                t.queue_number,
                t.counter_id,
                t.operator_id,
                c.Counter_Name,
                CONCAT(u.Firstname, ' ', u.Lastname) as operator_name,
                CASE 
                    WHEN t.status = 'Awaiting' THEN 'waiting'
                    WHEN t.status = 'Complete' THEN 'completed'
                    ELSE LOWER(t.status)
                END as status,
                t.time_created,
                t.time_served,
                CASE 
                    WHEN t.duration IS NOT NULL THEN TIME_TO_SEC(t.duration) / 60
                    ELSE NULL
                END as duration,
                CASE 
                    WHEN t.duration IS NOT NULL THEN CONCAT(FLOOR(TIME_TO_SEC(t.duration) / 60), 'm')
                    ELSE 'N/A'
                END as formatted_duration,
                t.created_at,
                NULL as notes
            FROM transactions t
            LEFT JOIN counters c ON t.counter_id = c.Counter_ID
            LEFT JOIN users u ON t.operator_id = u.User_ID
        ";
        
        $pdo->exec($createView);
        echo "View created successfully\n";
        
        // Show some sample data
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM v_all_transactions');
        $stmt->execute();
        $count = $stmt->fetch();
        echo "View contains {$count['count']} records\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
