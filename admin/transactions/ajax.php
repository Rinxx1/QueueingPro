<?php
require_once 'functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_transactions':
        $filters = [
            'status' => $_GET['status'] ?? '',
            'counter_id' => $_GET['counter_id'] ?? '',
            'date_range' => $_GET['date_range'] ?? 'today',
            'search' => $_GET['search'] ?? '',
            'limit' => $_GET['limit'] ?? 100
        ];
        
        $transactions = getAllTransactions($filters);
        echo json_encode(['success' => true, 'data' => $transactions]);
        break;
        
    case 'get_stats':
        $period = $_GET['period'] ?? 'today';
        $stats = getTransactionStats($period);
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case 'get_counter_analytics':
        $period = $_GET['period'] ?? 'today';
        $analytics = getCounterAnalytics($period);
        echo json_encode(['success' => true, 'data' => $analytics]);
        break;
        
    case 'get_hourly_analytics':
        $date = $_GET['date'] ?? date('Y-m-d');
        $analytics = getHourlyAnalytics($date);
        echo json_encode(['success' => true, 'data' => $analytics]);
        break;
        
    case 'get_counters':
        $counters = getCountersForDropdown();
        echo json_encode(['success' => true, 'data' => $counters]);
        break;
        
    case 'get_operators':
        $operators = getOperatorsForDropdown();
        echo json_encode(['success' => true, 'data' => $operators]);
        break;
        
    // Transaction modification endpoints removed - this is now a read-only log
    case 'add_awaiting':
    case 'complete_transaction':
    case 'cancel_transaction':
    case 'generate_sample_data':
    case 'update_operator':
        echo json_encode(['success' => false, 'message' => 'Transaction modifications not allowed - this is a read-only log']);
        break;
        
    case 'get_transaction':
        $transactionId = $_GET['transaction_id'] ?? 0;
        $transaction = getTransactionById($transactionId);
        if ($transaction) {
            echo json_encode(['success' => true, 'data' => $transaction]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
