<?php
/**
 * views/auth/logout.php
 */

require_once '../../app/core/Session.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = array();


if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}


session_destroy();

header("Location: /Job-Portal/public/index.php");
exit();