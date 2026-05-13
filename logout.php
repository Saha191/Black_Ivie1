<?php
// 1. Initialize the session.
session_start();

// 2. Unset all of the session variables.
$_SESSION = array();

// 3. Destroy the session cookie in the browser.
// This ensures that even if a hacker hijacked the session ID, it is now useless.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finally, destroy the session on the server.
session_destroy();

// 5. Redirect the user back to the login page (or homepage).
header("Location: login.php");
exit;
?>