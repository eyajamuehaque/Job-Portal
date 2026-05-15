<?php
/**
 * views/auth/logout.php
 */

require_once '../../app/core/Session.php';

// 1. Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Clear all session variables
$_SESSION = array();

// 3. Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 4. Destroy the session on the server
session_destroy();

// 5. THE FIX: Redirect to the CORRECT login path
// Since you want them to log in again, send them to the views folder
header("Location: /Job-Portal/public/index.php");
exit();