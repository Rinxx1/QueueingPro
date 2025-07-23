<?php
// Include database connection directly
require_once 'connections/database.php';

// Manually include the functions with corrected path
$functionsFile = file_get_contents('admin/transactions/functions.php');
$functionsFile = str_replace("require_once '../../connections/database.php';", "", $functionsFile);
$functionsFile = str_replace("<?php", "", $functionsFile);
eval($functionsFile);

echo "Testing Updated Transaction Management System (with User_ID)\n";
echo "===========================================================\n\n";

// Test 1: Add some sample transactions
echo "1. Adding sample transactions...\n";
$result = generateSampleTransactions();
if ($result['success']) {
    echo "✅ " . $result['message'] . "\n";
} else {
    echo "❌ " . $result['message'] . "\n";
}
echo "\n";

// Test 2: Get transaction statistics
echo "2. Getting transaction statistics...\n";
$stats = getTransactionStats();
print_r($stats);
echo "\n";

// Test 3: Get all transactions
echo "3. Getting all transactions...\n";
$transactions = getAllTransactions(['limit' => 3]);
echo "Found " . count($transactions) . " transactions\n";
if (!empty($transactions)) {
    echo "Sample transaction:\n";
    print_r($transactions[0]);
}
echo "\n";

// Test 4: Test operator assignment
if (!empty($transactions)) {
    echo "4. Testing operator assignment...\n";
    $transactionId = $transactions[0]['transaction_id'];
    $operators = getOperatorsForDropdown();
    if (!empty($operators)) {
        $operatorId = $operators[0]['User_ID'];
        $result = updateTransactionOperator($transactionId, $operatorId);
        if ($result['success']) {
            echo "✅ " . $result['message'] . "\n";
            
            // Verify the update
            $updated = getTransactionById($transactionId);
            echo "Updated transaction operator: " . ($updated['operator_name'] ?? 'None') . "\n";
        } else {
            echo "❌ " . $result['message'] . "\n";
        }
    }
}

echo "\nTest completed!\n";
?>
