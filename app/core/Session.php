<?php
/**
 * app/core/Session.php
 * Handles session management and Role-Based Access Control (RBAC).
 * Refer to Page 41 of your notes for session basics.
 */

class Session {
    
    /**
     * Initializes the session if it hasn't been started already.
     * Must be called before any HTML output.
     */
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a session variable.
     * @param string $key
     * @param mixed $val
     */
    public static function set($key, $val) {
        self::init();
        $_SESSION[$key] = $val;
    }

    /**
     * Retrieves a session variable.
     * @param string $key
     * @return mixed|null
     */
    public static function get($key) {
        self::init();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Role-Based Access Control (RBAC)
     * Checks if the user is logged in and has the required role.
     * Redirects to login if unauthorized.
     * * @param string $role The required role (e.g., 'seeker', 'admin')
     */
    public static function checkRole($role) {
        self::init();
        
        // 1. Check if user is logged in (Page 41 of notes)
        if (!isset($_SESSION['user_id'])) {
            header("Location: ../../public/login.php?error=Please login first");
            exit();
        }

        // 2. Check if the user's role matches the required role
        if ($_SESSION['role'] !== $role) {
            // Redirect to their own dashboard if they try to access another role's page
            $userRole = $_SESSION['role'];
            header("Location: ../../views/$userRole/dashboard.php?error=Unauthorized access");
            exit();
        }
    }

    /**
     * Ends the session and logs the user out.
     * Refer to Page 41 of your notes for session_destroy().
     */
    public static function destroy() {
        self::init();
        session_unset(); // Removes all variables
        session_destroy(); // Destroys the session
        header("Location: ../../public/login.php");
        exit();
    }
}
?>