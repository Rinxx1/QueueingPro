<?php
session_start();
require_once '../../connections/auth.php';
require_once 'functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_counters':
        $counters = getAllCounters();
        echo json_encode(['success' => true, 'data' => $counters]);
        break;
        
    case 'get_counter':
        $counterId = $_GET['id'] ?? 0;
        $counter = getCounterById($counterId);
        if ($counter) {
            echo json_encode(['success' => true, 'data' => $counter]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Counter not found']);
        }
        break;
        
    case 'create_counter':
        $counterName = trim($_POST['counter_name'] ?? '');
        $counterDescription = trim($_POST['counter_description'] ?? '');
        $currentNumber = trim($_POST['current_number'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $userId = $_POST['user_id'] ?? null;
        
        if (empty($counterName) || empty($currentNumber)) {
            echo json_encode(['success' => false, 'message' => 'Counter name and current number are required']);
            break;
        }
        
        $result = createCounter($counterName, $counterDescription, $currentNumber, $status, $userId);
        echo json_encode($result);
        break;
        
    case 'update_counter':
        $counterId = $_POST['counter_id'] ?? 0;
        $counterName = trim($_POST['counter_name'] ?? '');
        $counterDescription = trim($_POST['counter_description'] ?? '');
        $currentNumber = trim($_POST['current_number'] ?? '');
        $status = $_POST['status'] ?? 'Active';
        $userId = $_POST['user_id'] ?? null;
        
        if (empty($counterName) || empty($currentNumber)) {
            echo json_encode(['success' => false, 'message' => 'Counter name and current number are required']);
            break;
        }
        
        $result = updateCounter($counterId, $counterName, $counterDescription, $currentNumber, $status, $userId);
        echo json_encode($result);
        break;
        
    case 'delete_counter':
        $counterId = $_POST['counter_id'] ?? 0;
        $result = deleteCounter($counterId);
        echo json_encode($result);
        break;
        
    case 'get_users':
        $users = getUsersForDropdown();
        echo json_encode(['success' => true, 'data' => $users]);
        break;

    case 'get_available_users':
        $counterId = $_GET['counter_id'] ?? null;
        $users = getAvailableUsersForCounter($counterId);
        echo json_encode(['success' => true, 'data' => $users]);
        break;

    case 'get_next_counter_name':
        $nextCurrentNumber = getNextCounterName();
        echo json_encode(['success' => true, 'data' => $nextCurrentNumber]);
        break;
        
    case 'get_stats':
        $stats = getCounterStats();
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    case 'get_awaiting':
        $awaiting = getAllAwaiting();
        echo json_encode(['success' => true, 'data' => $awaiting]);
        break;
        
    case 'get_awaiting_item':
        $awaitingId = $_GET['id'] ?? 0;
        $awaiting = getAwaitingById($awaitingId);
        if ($awaiting) {
            echo json_encode(['success' => true, 'data' => $awaiting]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Awaiting entry not found']);
        }
        break;
        
    case 'create_awaiting':
        $awaitingNumber = trim($_POST['awaiting_number'] ?? '');
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($awaitingNumber) || empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Awaiting number and counter are required']);
            break;
        }
        
        $result = createAwaiting($awaitingNumber, $counterId);
        echo json_encode($result);
        break;
        
    case 'update_awaiting':
        $awaitingId = $_POST['awaiting_id'] ?? 0;
        $awaitingNumber = trim($_POST['awaiting_number'] ?? '');
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($awaitingNumber) || empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Awaiting number and counter are required']);
            break;
        }
        
        $result = updateAwaiting($awaitingId, $awaitingNumber, $counterId);
        echo json_encode($result);
        break;
        
    case 'delete_awaiting':
        $awaitingId = $_POST['awaiting_id'] ?? 0;
        $result = deleteAwaiting($awaitingId);
        echo json_encode($result);
        break;
        
    case 'update_counter_status':
        $counterId = $_POST['counter_id'] ?? 0;
        $status = $_POST['status'] ?? 'Active';
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        // Get current counter data
        $counter = getCounterById($counterId);
        if (!$counter) {
            echo json_encode(['success' => false, 'message' => 'Counter not found']);
            break;
        }
        
        $result = updateCounter($counterId, $counter['Counter_Name'], $counter['Counter_CurrentNumber'], $status, $counter['User_ID']);
        echo json_encode($result);
        break;
        
    case 'update_counter_number':
        $counterId = $_POST['counter_id'] ?? 0;
        $currentNumber = $_POST['current_number'] ?? '';
        
        if (empty($counterId) || empty($currentNumber)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID and current number are required']);
            break;
        }
        
        // Get current counter data
        $counter = getCounterById($counterId);
        if (!$counter) {
            echo json_encode(['success' => false, 'message' => 'Counter not found']);
            break;
        }
        
        $result = updateCounter($counterId, $counter['Counter_Name'], $currentNumber, $counter['Counter_Status'], $counter['User_ID']);
        echo json_encode($result);
        break;

    case 'get_awaiting_queue':
        $counterId = $_POST['counter_id'] ?? 0;
        
        if (empty($counterId)) {
            echo json_encode(['success' => false, 'message' => 'Counter ID is required']);
            break;
        }
        
        $awaitingQueue = getAwaitingQueueForCounter($counterId);
        echo json_encode(['success' => true, 'data' => $awaitingQueue]);
        break;

    case 'reset_queue_numbers':
        $result = manualResetQueueNumbers();
        echo json_encode($result);
        break;
        
    case 'check_daily_reset':
        $result = checkAndPerformDailyReset();
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
