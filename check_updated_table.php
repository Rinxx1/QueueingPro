<?php
require_once 'connections/database.php';

try {
    $stmt = $pdo->prepare('DESCRIBE transactions');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Updated transactions table structure:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")" . (($col['Key'] == 'PRI') ? ' [PRIMARY KEY]' : '') . "\n";
    }
    
    // Check record count
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM transactions');
    $stmt->execute();
    $count = $stmt->fetch();
    echo "\nRecord count: " . $count['count'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
