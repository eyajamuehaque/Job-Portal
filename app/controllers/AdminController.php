<?php
/**
 * app/controllers/AdminController.php
 * Handles routing and logic for the Admin panel.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Job.php'; // For category methods
require_once __DIR__ . '/../models/Complaint.php';

class AdminController {
    private $db;
    private $adminModel;
    private $jobModel; // Re-use Job model for getCategories
    private $complaintModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
        
        $this->adminModel = new Admin($this->db);
        $this->jobModel = new Job($this->db);
        $this->complaintModel = new Complaint($this->db);

        // Secure this controller - only admins allowed
        Session::checkRole('admin');
    }

    /**
     * Dashboard Data
     */
    public function getDashboardStats() {
        return $this->adminModel->getPlatformStats();
    }

    /**
     * User Management
     */
    public function getUsers($roleFilter = null) {
        return $this->adminModel->getUsers($roleFilter);
    }

    public function toggleUserStatus($userId) {
        if ($this->adminModel->toggleUserStatus($userId)) {
            header("Location: users.php?msg=User status updated successfully.");
        } else {
            header("Location: users.php?error=Failed to update user status.");
        }
        exit();
    }

    public function toggleUserVerification($userId) {
        if ($this->adminModel->toggleUserVerification($userId)) {
            header("Location: users.php?msg=User verification updated.");
        } else {
            header("Location: users.php?error=Failed to verify user.");
        }
        exit();
    }

    /**
     * Category Management
     */
    public function getCategories() {
        return $this->jobModel->getCategories();
    }

    public function addCategory() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = htmlspecialchars($_POST['name']);
            $description = htmlspecialchars($_POST['description']);
            
            $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
            $stmt = mysqli_prepare($this->db, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $name, $description);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: categories.php?msg=Category added successfully.");
                    exit();
                }
            }
            header("Location: categories.php?error=Failed to add category.");
            exit();
        }
    }

    public function editCategory() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = intval($_POST['id']);
            $name = htmlspecialchars($_POST['name']);
            $description = htmlspecialchars($_POST['description']);
            
            $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: categories.php?msg=Category updated successfully.");
                    exit();
                }
            }
            header("Location: categories.php?error=Failed to update category.");
            exit();
        }
    }

    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                header("Location: categories.php?msg=Category deleted successfully.");
                exit();
            }
        }
        header("Location: categories.php?error=Failed to delete category. It might be in use.");
        exit();
    }

    /**
     * Dispute Resolution (Complaints)
     */
    public function getAllComplaints() {
        $sql = "SELECT c.*, s.name as submitter_name, s.role as submitter_role, 
                u.name as subject_name, u.role as subject_role
                FROM complaints c
                JOIN users s ON c.submitter_id = s.id
                LEFT JOIN users u ON c.subject_id = u.id
                ORDER BY c.status ASC, c.created_at DESC";
                
        $result = mysqli_query($this->db, $sql);
        return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
    }

    public function resolveComplaint() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = intval($_POST['complaint_id']);
            $note = htmlspecialchars($_POST['admin_note']);
            
            $sql = "UPDATE complaints SET status = 'resolved', admin_note = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "si", $note, $id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: complaints.php?msg=Complaint resolved.");
                    exit();
                }
            }
            header("Location: complaints.php?error=Failed to resolve complaint.");
            exit();
        }
    }
}
?>