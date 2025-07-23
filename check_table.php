<?php
require_once 'connections/database.php';

try {
    $stmt = $pdo->prepare('DESCRIBE transactions');
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Transactions table structure:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")" . (($col['Key'] == 'PRI') ? ' [PRIMARY KEY]' : '') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
