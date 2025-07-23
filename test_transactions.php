<?php
// Include database connection directly
require_once 'connections/database.php';

// Manually include the functions with corrected path
$functionsFile = file_get_contents('admin/transactions/functions.php');
$functionsFile = str_replace("require_once '../../connections/database.php';", "", $functionsFile);
$functionsFile = str_replace("<?php", "", $functionsFile);
eval($functionsFile);

echo "Testing Transaction Management System\n";
echo "=====================================\n\n";

// Test 1: Get transaction statistics
echo "1. Getting transaction statistics...\n";
$stats = getTransactionStats();
print_r($stats);
echo "\n";

// Test 2: Get all transactions
echo "2. Getting all transactions...\n";
$transactions = getAllTransactions(['limit' => 5]);
echo "Found " . count($transactions) . " transactions\n";
if (!empty($transactions)) {
    echo "Sample transaction:\n";
    print_r($transactions[0]);
}
echo "\n";

// Test 3: Get counter analytics
echo "3. Getting counter analytics...\n";
$analytics = getCounterAnalytics();
print_r($analytics);
echo "\n";

// Test 4: Get counters for dropdown
echo "4. Getting counters for dropdown...\n";
$counters = getCountersForDropdown();
echo "Found " . count($counters) . " counters\n";
foreach ($counters as $counter) {
    echo "- " . $counter['Counter_Name'] . " (ID: " . $counter['Counter_ID'] . ")\n";
}
echo "\n";

// Test 5: Get operators for dropdown
echo "5. Getting operators for dropdown...\n";
$operators = getOperatorsForDropdown();
echo "Found " . count($operators) . " operators\n";
foreach ($operators as $operator) {
    echo "- " . $operator['full_name'] . " (ID: " . $operator['User_ID'] . ")\n";
}

echo "\nTest completed!\n";
?>
