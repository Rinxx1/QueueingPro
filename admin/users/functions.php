<?php
require_once '../../connections/database.php';

// Get all users
function getAllUsers() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, c.Counter_Name 
            FROM users u 
            LEFT JOIN counters c ON u.Counter_ID = c.Counter_ID 
            ORDER BY u.User_Created DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get user by ID
function getUserById($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE User_ID = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return false;
    }
}

// Create new user
function createUser($firstname, $lastname, $username, $password, $userLevel) {
    global $pdo;
    try {
        // Check if username already exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (Firstname, Lastname, Username, Password, User_Lvl, Status, User_Created) 
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$firstname, $lastname, $username, $hashedPassword, $userLevel]);
        
        return ['success' => true, 'message' => 'User created successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Update user
function updateUser($userId, $firstname, $lastname, $username, $userLevel, $status, $password = null) {
    global $pdo;
    try {
        // Check if username exists for other users
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Username = ? AND User_ID != ?");
        $checkStmt->execute([$username, $userId]);
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        if ($password) {
            // Update with password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET Firstname = ?, Lastname = ?, Username = ?, Password = ?, User_Lvl = ?, Status = ?
                WHERE User_ID = ?
            ");
            $stmt->execute([$firstname, $lastname, $username, $hashedPassword, $userLevel, $status, $userId]);
        } else {
            // Update without password
            $stmt = $pdo->prepare("
                UPDATE users 
                SET Firstname = ?, Lastname = ?, Username = ?, User_Lvl = ?, Status = ?
                WHERE User_ID = ?
            ");
            $stmt->execute([$firstname, $lastname, $username, $userLevel, $status, $userId]);
        }
        
        return ['success' => true, 'message' => 'User updated successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Delete user
function deleteUser($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE User_ID = ?");
        $stmt->execute([$userId]);
        
        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}


?>
