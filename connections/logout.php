<?php
session_start();

// Clear all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear remember me cookies
setcookie('username', '', time() - 3600, '/');
setcookie('password', '', time() - 3600, '/');

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../login.php?logout=success");
exit();
?>