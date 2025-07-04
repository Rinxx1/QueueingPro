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
    case 'get_users':
        $users = getAllUsers();
        echo json_encode(['success' => true, 'data' => $users]);
        break;
        
    case 'get_user':
        $userId = $_GET['id'] ?? 0;
        $user = getUserById($userId);
        if ($user) {
            echo json_encode(['success' => true, 'data' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        break;
        
    case 'create_user':
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $userLevel = $_POST['user_level'] ?? 1;
        $counterId = $_POST['counter_id'] ?? null;
        
        if (empty($firstname) || empty($lastname) || empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }
        
        $result = createUser($firstname, $lastname, $username, $password, $userLevel, $counterId);
        echo json_encode($result);
        break;
        
    case 'update_user':
        $userId = $_POST['user_id'] ?? 0;
        $firstname = trim($_POST['firstname'] ?? '');
        $lastname = trim($_POST['lastname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $userLevel = $_POST['user_level'] ?? 1;
        $status = $_POST['status'] ?? 'Active';
        $counterId = $_POST['counter_id'] ?? null;
        
        if (empty($firstname) || empty($lastname) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
            break;
        }
        
        $result = updateUser($userId, $firstname, $lastname, $username, $userLevel, $status, $counterId, $password);
        echo json_encode($result);
        break;
        
    case 'delete_user':
        $userId = $_POST['user_id'] ?? 0;
        $result = deleteUser($userId);
        echo json_encode($result);
        break;
        
    case 'get_counters':
        $counters = getCountersForDropdown();
        echo json_encode(['success' => true, 'data' => $counters]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
