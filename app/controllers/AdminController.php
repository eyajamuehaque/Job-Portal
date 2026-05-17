<?php
/**
 * app/controllers/AdminController.php
 * Handles routing and logic for the Admin panel.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Job.php'; 
require_once __DIR__ . '/../models/Complaint.php';

class AdminController {
    private $db;
    private $adminModel;
    private $jobModel; 
    private $complaintModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->conn;
        
        $this->adminModel = new Admin($this->db);
        $this->jobModel = new Job($this->db);
        $this->complaintModel = new Complaint($this->db);

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
     * Platform Settings
     */
    public function getSettings() {
        return $this->adminModel->getSettings();
    }

    public function getAnalytics() {
        return $this->adminModel->getAnalytics();
    }

    public function updateSettings() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $settings = [
                'max_jobs_per_employer' => intval($_POST['max_jobs_per_employer']),
                'max_applications_per_seeker' => intval($_POST['max_applications_per_seeker']),
                'resume_visibility' => htmlspecialchars($_POST['resume_visibility'])
            ];
            
            if ($this->adminModel->updateSettings($settings)) {
                header("Location: settings.php?msg=Settings updated successfully.");
            } else {
                header("Location: settings.php?error=Failed to update settings.");
            }
            exit();
        }
    }

    /**
     * Announcements
     */
    public function getAnnouncements() {
        return $this->adminModel->getAnnouncements();
    }

    public function createAnnouncement() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $title = htmlspecialchars($_POST['title']);
            $message = htmlspecialchars($_POST['message']);
            $target_role = $_POST['target_role'];
            
            if ($this->adminModel->createAnnouncement($title, $message, $target_role)) {
                header("Location: announcements.php?msg=Announcement created successfully.");
            } else {
                header("Location: announcements.php?error=Failed to create announcement.");
            }
            exit();
        }
    }

    public function toggleAnnouncementStatus($id) {
        if ($this->adminModel->toggleAnnouncementStatus($id)) {
            header("Location: announcements.php?msg=Announcement status updated.");
        } else {
            header("Location: announcements.php?error=Failed to update announcement.");
        }
        exit();
    }

    public function rejectUserVerification($userId, $reason) {
        if ($this->adminModel->rejectUserVerification($userId, $reason)) {
            header("Location: users.php?msg=User verification rejected and notified.");
        } else {
            header("Location: users.php?error=Failed to reject user verification.");
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
     * Job Moderation
     */
    public function getAllJobs() {
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';
        return $this->adminModel->getAllJobs($keyword, $status);
    }

    public function toggleFeaturedJob($jobId) {
        if ($this->adminModel->toggleFeaturedJob($jobId)) {
            header("Location: manage_jobs.php?msg=Featured status updated.");
        } else {
            header("Location: manage_jobs.php?error=Failed to update featured status.");
        }
        exit();
    }

    public function deleteJob($jobId) {
        if ($this->adminModel->deleteJob($jobId)) {
            header("Location: manage_jobs.php?msg=Job deleted successfully.");
        } else {
            header("Location: manage_jobs.php?error=Failed to delete job.");
        }
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