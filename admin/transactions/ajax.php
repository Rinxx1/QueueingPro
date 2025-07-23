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
        
    case 'add_awaiting':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $queueNumber = $_POST['queue_number'] ?? '';
            $counterId = $_POST['counter_id'] ?? 0;
            
            $result = addAwaitingTransaction($queueNumber, $counterId);
            echo json_encode($result);
        }
        break;
        
    case 'complete_transaction':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $awaitingId = $_POST['awaiting_id'] ?? 0;
            $duration = $_POST['duration'] ?? null;
            $result = completeTransaction($awaitingId, $duration);
            echo json_encode($result);
        }
        break;
        
    case 'cancel_transaction':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $awaitingId = $_POST['awaiting_id'] ?? 0;
            $result = cancelTransaction($awaitingId);
            echo json_encode($result);
        }
        break;
        
    case 'generate_sample_data':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = generateSampleTransactions();
            echo json_encode($result);
        }
        break;
        
    case 'update_operator':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $transactionId = $_POST['transaction_id'] ?? 0;
            $operatorId = $_POST['user_id'] ?? $_POST['operator_id'] ?? null; // Support both parameter names
            $result = updateTransactionOperator($transactionId, $operatorId);
            echo json_encode($result);
        }
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
