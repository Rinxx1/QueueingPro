<?php
require_once 'connections/database.php';

echo "<h2>Debug Counter Assignment</h2>";

// Check current user session
session_start();
echo "<h3>Current Session:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
echo "Counter ID: " . ($_SESSION['counter_id'] ?? 'Not set') . "<br>";
echo "User Level: " . ($_SESSION['user_level'] ?? 'Not set') . "<br><br>";

// Check all counters
echo "<h3>Available Counters:</h3>";
try {
    $stmt = $pdo->prepare("SELECT Counter_ID, Counter_Name, Counter_Description FROM counters ORDER BY Counter_Name");
    $stmt->execute();
    $counters = $stmt->fetchAll();
    
    if (empty($counters)) {
        echo "No counters found!<br>";
    } else {
        foreach ($counters as $counter) {
            echo "ID: {$counter['Counter_ID']}, Name: {$counter['Counter_Name']}, Description: {$counter['Counter_Description']}<br>";
        }
    }
} catch(PDOException $e) {
    echo "Error fetching counters: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Check all users with level 1 (controllers)
echo "<h3>Controller Users:</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT u.User_ID, u.Username, u.Firstname, u.Lastname, u.User_Lvl, u.Counter_ID, c.Counter_Name 
        FROM users u 
        LEFT JOIN counters c ON u.Counter_ID = c.Counter_ID 
        WHERE u.User_Lvl = 1 
        ORDER BY u.Username
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "No controller users found!<br>";
    } else {
        foreach ($users as $user) {
            echo "ID: {$user['User_ID']}, Username: {$user['Username']}, Name: {$user['Firstname']} {$user['Lastname']}, Counter_ID: " . ($user['Counter_ID'] ?? 'NULL') . ", Counter: " . ($user['Counter_Name'] ?? 'Not Assigned') . "<br>";
        }
    }
} catch(PDOException $e) {
    echo "Error fetching users: " . $e->getMessage() . "<br>";
}

if (isset($_POST['assign_counter'])) {
    $userId = $_POST['user_id'];
    $counterId = $_POST['counter_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET Counter_ID = ? WHERE User_ID = ?");
        $stmt->execute([$counterId, $userId]);
        echo "<div style='color: green; margin: 20px 0; padding: 10px; border: 1px solid green;'>Counter assigned successfully! Please refresh the page.</div>";
        
        // Update session if it's the current user
        if ($_SESSION['user_id'] == $userId) {
            $_SESSION['counter_id'] = $counterId;
        }
    } catch(PDOException $e) {
        echo "<div style='color: red; margin: 20px 0; padding: 10px; border: 1px solid red;'>Error assigning counter: " . $e->getMessage() . "</div>";
    }
}
?>

<h3>Assign Counter to User:</h3>
<form method="POST" style="margin: 20px 0;">
    <label for="user_id">Select User:</label>
    <select name="user_id" required style="margin: 5px; padding: 5px;">
        <option value="">-- Select User --</option>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT User_ID, Username, Firstname, Lastname FROM users WHERE User_Lvl = 1");
            $stmt->execute();
            $users = $stmt->fetchAll();
            foreach ($users as $user) {
                echo "<option value='{$user['User_ID']}'>{$user['Username']} ({$user['Firstname']} {$user['Lastname']})</option>";
            }
        } catch(PDOException $e) {
            echo "<option value=''>Error loading users</option>";
        }
        ?>
    </select><br>
    
    <label for="counter_id">Select Counter:</label>
    <select name="counter_id" required style="margin: 5px; padding: 5px;">
        <option value="">-- Select Counter --</option>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT Counter_ID, Counter_Name FROM counters ORDER BY Counter_Name");
            $stmt->execute();
            $counters = $stmt->fetchAll();
            foreach ($counters as $counter) {
                echo "<option value='{$counter['Counter_ID']}'>{$counter['Counter_Name']}</option>";
            }
        } catch(PDOException $e) {
            echo "<option value=''>Error loading counters</option>";
        }
        ?>
    </select><br>
    
    <button type="submit" name="assign_counter" style="margin: 10px 5px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;">Assign Counter</button>
</form>

<a href="controller/" style="display: inline-block; margin: 10px 5px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;">Go to Controller</a>
<a href="admin/" style="display: inline-block; margin: 10px 5px; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px;">Go to Admin</a>
