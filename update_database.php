<?php
/**
 * Database Update Script for QueueingPro
 * This script updates the transactions table structure to match the actual system flow
 */

// Include database connection
require_once 'connections/database.php';

try {
    echo "Starting database updates for QueueingPro...\n";
    
    // Read the SQL file
    $sqlFile = 'database_updates.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("Could not read SQL file: $sqlFile");
    }
    
    // Split SQL statements (basic splitting by semicolon)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $successCount = 0;
    $errorCount = 0;
    
    // Execute each statement
    foreach ($statements as $statement) {
        // Skip comments and empty statements
        if (empty(trim($statement)) || preg_match('/^\s*--/', $statement)) {
            continue;
        }
        
        try {
            // Handle DELIMITER statements for triggers and procedures
            if (preg_match('/DELIMITER\s+(\S+)/i', $statement, $matches)) {
                continue; // Skip delimiter changes in this simple implementation
            }
            
            // Execute the statement
            $pdo->exec($statement);
            $successCount++;
            echo "✓ Executed statement successfully\n";
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "✗ Error executing statement: " . $e->getMessage() . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
            
            // Continue with other statements even if one fails
            continue;
        }
    }
    
    echo "\n=== Database Update Summary ===\n";
    echo "Successful statements: $successCount\n";
    echo "Failed statements: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "✅ All database updates completed successfully!\n";
    } else {
        echo "⚠️  Some updates failed. Please check the errors above.\n";
    }
    
    // Test the new structure
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
    
    // Test stored procedure
    try {
        $stmt = $pdo->query("CALL GetTransactionStats('today')");
        $result = $stmt->fetch();
        echo "✓ Statistics procedure: {$result['total_today']} transactions today\n";
    } catch (PDOException $e) {
        echo "✗ Error testing statistics procedure: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Database update process completed!\n";
echo "You can now use the updated transaction management system.\n";
?>
