<?php
/**
 * app/core/Session.php
 * Handles session management and Role-Based Access Control (RBAC).
 */

class Session {
    
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function set($key, $val) {
        self::init();
        $_SESSION[$key] = $val;
    }

    public static function get($key) {
        self::init();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Role-Based Access Control (RBAC)
     * Checks if the user is logged in and has the required role.
     * Redirects to login if unauthorized.
     */
    public static function checkRole($role) {
        self::init();
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../public/login.php?error=Please login first");
            exit();
        }

        if ($_SESSION['role'] !== $role) {
            $userRole = $_SESSION['role'];
            header("Location: ../../views/$userRole/dashboard.php?error=Unauthorized access");
            exit();
        }
    }


    public static function destroy() {
        self::init();
        session_unset(); 
        session_destroy(); 
        header("Location: ../../public/login.php");
        exit();
    }
}
?>