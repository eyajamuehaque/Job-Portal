<?php
/**
 * app/controllers/AuthController.php
 * Handles user authentication: Login, Registration, and Logout.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $db;

    public function __construct() {
        // Initialize Database and Model
        $database = new Database();
        $this->db = $database->conn;
        $this->userModel = new User($this->db);
    }

    /**
     * Handle Login Request
     */
    public function login() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Basic Validation (Page 19 of your notes)
            if (empty($email) || empty($password)) {
                return "Please fill in all fields.";
            }

            // Find user in database
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                // Verify password (using standard PHP password_verify)
                if (password_verify($password, $user['password_hash'])) {
                    
                    // Start session and store user data (Page 41 of notes)
                    Session::init();
                    Session::set('user_id', $user['id']);
                    Session::set('name', $user['name']);
                    Session::set('role', $user['role']);

                    // Role-Based Redirection
                    $this->redirectByRole($user['role']);
                } else {
                    return "Invalid password.";
                }
            } else {
                return "No account found with that email.";
            }
        }
    }

    /**
     * Handle Registration Request
     */
    public function register() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $phone = trim($_POST['phone']);
            $role = $_POST['role'];

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($role)) {
                return "Required fields are missing.";
            }

            if ($this->userModel->emailExists($email)) {
                return "Email is already registered.";
            }

            // Create user
            $success = $this->userModel->create($name, $email, $password, $phone, $role);

            if ($success) {
                header("Location: login.php?msg=Registration successful! Please login.");
                exit();
            } else {
                return "Something went wrong. Please try again.";
            }
        }
    }

    /**
     * Logout logic
     */
    public function logout() {
        Session::destroy();
    }

    /**
     * Helper to redirect users to their specific dashboards
     */
    private function redirectByRole($role) {
    // We use the full path starting from the project root folder
    $basePath = "/JOB-PORTAL/views/";

    switch ($role) {
        case 'seeker':
            header("Location: " . $basePath . "seeker/dashboard.php");
            break;
        case 'employer':
            header("Location: " . $basePath . "employer/dashboard.php");
            break;
        case 'recruiter':
            header("Location: " . $basePath . "recruiter/dashboard.php");
            break;
        case 'admin':
            header("Location: " . $basePath . "admin/dashboard.php");
            break;
        default:
            // Goes back to the public landing page
            header("Location: /JOB-PORTAL/public/index.php");
    }
    exit();
}
}
?>