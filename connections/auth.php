<?php
require_once 'database.php';

// Handle AJAX login request
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'login') {
    header('Content-Type: application/json');
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = ($_POST['remember_me'] ?? '') === '1' || ($_POST['remember_me'] ?? '') === 'on';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }
    
    $result = loginUser($username, $password, $remember);
    echo json_encode($result);
    exit;
}

// Login function
function loginUser($username, $password, $remember = false) {
    global $pdo;
    
    try {
        // Get user from database
        $stmt = $pdo->prepare("SELECT User_ID, Username, Password, User_Lvl, Counter_ID FROM users WHERE Username = ? AND Password IS NOT NULL");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Check if password is hashed or plain text
            $passwordMatch = false;
            
            // Try password_verify first (for hashed passwords)
            if (password_verify($password, $user['Password'])) {
                $passwordMatch = true;
            } 
            // If that fails, try direct comparison (for plain text passwords)
            else if ($password === $user['Password']) {
                $passwordMatch = true;
                
                // Update to hashed password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE users SET Password = ? WHERE User_ID = ?");
                $updateStmt->execute([$hashedPassword, $user['User_ID']]);
            }
            
            if ($passwordMatch) {
                // Start session and store user data
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['User_ID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['user_level'] = $user['User_Lvl'];
                $_SESSION['counter_id'] = $user['Counter_ID'];
                $_SESSION['logged_in'] = true;
                
                // Handle remember me
                if ($remember) {
                    $hour = time() + 3600 * 24 * 30; // 30 days
                    setcookie('username', $username, $hour);
                    setcookie('password', $password, $hour);
                }
                
                return [
                    'success' => true, 
                    'user' => [
                        'User_ID' => $user['User_ID'],
                        'Username' => $user['Username'],
                        'User_Lvl' => $user['User_Lvl'],
                        'Counter_ID' => $user['Counter_ID']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
        } else {
            return ['success' => false, 'message' => 'User not found'];
        }
        
    } catch(PDOException $e) {
        error_log('Database error in loginUser: ' . $e->getMessage());
        return ['success' => false, 'message' => 'A system error occurred. Please try again later.'];
    }
}

// Check if user is logged in
function isLoggedIn() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if user is admin
function isAdmin() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_level']) && $_SESSION['user_level'] == 0;
}

// Check if user is controller
function isController() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_level']) && $_SESSION['user_level'] == 1;
}

// Get all counters for dropdown
function getCounters() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT Counter_ID, Counter_Name FROM counters ORDER BY Counter_Name");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Logout function
function logout() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>
