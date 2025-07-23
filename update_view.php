<?php
require_once 'connections/database.php';

try {
    // Drop the existing view first
    $pdo->exec("DROP VIEW IF EXISTS v_all_transactions");
    echo "Dropped existing view\n";
    
    // Create the updated view with User_ID
    $createView = "
        CREATE VIEW v_all_transactions AS
        SELECT 
            t.id as transaction_id,
            CONCAT('TXN-', LPAD(t.id, 6, '0')) as transaction_ref,
            t.queue_number,
            t.counter_id,
            t.User_ID as operator_id,
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
        LEFT JOIN users u ON t.User_ID = u.User_ID
    ";
    
    $pdo->exec($createView);
    echo "View recreated successfully with User_ID column\n";
    
    // Test the view
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM v_all_transactions');
    $stmt->execute();
    $count = $stmt->fetch();
    echo "View contains {$count['count']} records\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
