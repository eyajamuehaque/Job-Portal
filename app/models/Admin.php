<?php
/**
 * app/models/Admin.php
 * Handles complex queries for the Admin role including stats and user management.
 */

class Admin {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Get overall platform statistics for the dashboard
     */
    public function getPlatformStats() {
        $stats = [
            'total_users' => 0,
            'seekers' => 0,
            'employers' => 0,
            'recruiters' => 0,
            'active_jobs' => 0,
            'total_applications' => 0,
            'applications_today' => 0,
            'open_complaints' => 0,
            'pending_verifications' => 0
        ];

        // User stats
        $sql = "SELECT role, COUNT(*) as count FROM users WHERE role != 'admin' GROUP BY role";
        $result = mysqli_query($this->db, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $stats['total_users'] += $row['count'];
                $stats[$row['role'] . 's'] = $row['count'];
            }
        }

        // Job stats
        $sql = "SELECT COUNT(*) as count FROM jobs WHERE status = 'active'";
        $result = mysqli_query($this->db, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['active_jobs'] = $row['count'];
        }

        // Application stats
        $sql = "SELECT COUNT(*) as count FROM applications";
        $result = mysqli_query($this->db, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['total_applications'] = $row['count'];
        }

        // Complaint stats
        $sql = "SELECT COUNT(*) as count FROM complaints WHERE status = 'open'";
        $result = mysqli_query($this->db, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['open_complaints'] = $row['count'];
        }

        // Applications today
        $sql = "SELECT COUNT(*) as count FROM applications WHERE DATE(applied_at) = CURDATE()";
        $result = mysqli_query($this->db, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['applications_today'] = $row['count'];
        }

        // Pending verifications (users not verified and not admin)
        $sql = "SELECT COUNT(*) as count FROM users WHERE is_verified = 0 AND role != 'admin'";
        $result = mysqli_query($this->db, $sql);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $stats['pending_verifications'] = $row['count'];
        }

        return $stats;
    }

    /**
     * Get all users with optional filtering
     */
    public function getUsers($roleFilter = null) {
        $sql = "SELECT id, name, email, role, is_active, is_verified, created_at FROM users WHERE role != 'admin'";
        
        if ($roleFilter) {
            $sql .= " AND role = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            mysqli_stmt_bind_param($stmt, "s", $roleFilter);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        } else {
            $sql .= " ORDER BY created_at DESC";
            $result = mysqli_query($this->db, $sql);
        }

        $users = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $users[] = $row;
            }
        }
        return $users;
    }

    /**
     * Toggle User Active Status (Ban/Unban)
     */
    public function toggleUserStatus($userId) {
        $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'admin'";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Toggle User Verification Status
     */
    public function toggleUserVerification($userId) {
        $sql = "UPDATE users SET is_verified = NOT is_verified WHERE id = ? AND role != 'admin'";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Reject User Verification Request
     */
    public function rejectUserVerification($userId, $reason) {
        $sql = "UPDATE users SET is_verified = 0 WHERE id = ? AND role != 'admin'";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            // Send message to the user
            require_once __DIR__ . '/Message.php';
            $messageModel = new Message($this->db);
            
            $adminId = Session::get('user_id');
            $body = "[VERIFICATION REJECTED]\nYour account verification request was rejected by an administrator.\n\nReason:\n" . $reason;
            
            return $messageModel->send($adminId, $userId, $body);
        }
        return false;
    }

    /**
     * Get all jobs for admin moderation
     */
    public function getAllJobs($keyword = '', $status = '', $employer_id = null) {
        $sql = "SELECT j.*, c.name as category_name, 
                COALESCE(e.name, r.name) AS posted_by_name,
                CASE 
                    WHEN j.employer_id IS NOT NULL THEN 'Employer'
                    WHEN j.recruiter_id IS NOT NULL THEN 'Recruiter'
                    ELSE 'Unknown'
                END AS posted_by_type
                FROM jobs j 
                LEFT JOIN categories c ON j.category_id = c.id
                LEFT JOIN users e ON j.employer_id = e.id
                LEFT JOIN users r ON j.recruiter_id = r.id
                WHERE 1=1";
                
        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (j.title LIKE ? OR e.name LIKE ? OR r.name LIKE ?)";
            $search = "%{$keyword}%";
            $params[] = $search; $params[] = $search; $params[] = $search;
            $types .= "sss";
        }

        if (!empty($status)) {
            $sql .= " AND j.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        $sql .= " ORDER BY j.created_at DESC";

        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $jobs;
        }
        return [];
    }

    /**
     * Toggle featured status of a job
     */
    public function toggleFeaturedJob($job_id) {
        $sql = "UPDATE jobs SET is_featured = NOT is_featured WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $job_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Delete a job entirely (Admin moderation)
     */
    public function deleteJob($job_id) {
        $sql = "DELETE FROM jobs WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $job_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Settings Management
     */
    public function getSettings() {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $result = mysqli_query($this->db, $sql);
        $settings = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        return $settings;
    }

    public function updateSettings($settings) {
        $success = true;
        foreach ($settings as $key => $value) {
            $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = mysqli_prepare($this->db, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $value, $key);
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false;
                }
                mysqli_stmt_close($stmt);
            } else {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Analytics Data
     */
    public function getAnalytics() {
        $data = [
            'jobs_by_category' => [],
            'top_employers' => [],
            'new_users_by_month' => []
        ];

        // Jobs by Category
        $sql = "SELECT c.name, COUNT(j.id) as count 
                FROM categories c 
                LEFT JOIN jobs j ON c.id = j.category_id 
                GROUP BY c.id ORDER BY count DESC LIMIT 5";
        $result = mysqli_query($this->db, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['jobs_by_category'][] = $row;
            }
        }

        // Top Employers (by active jobs)
        $sql = "SELECT u.name, COUNT(j.id) as count 
                FROM users u 
                JOIN jobs j ON u.id = j.employer_id 
                WHERE j.status = 'active'
                GROUP BY u.id ORDER BY count DESC LIMIT 5";
        $result = mysqli_query($this->db, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['top_employers'][] = $row;
            }
        }

        // New Users by Month (last 6 months)
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, role, COUNT(*) as count 
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                AND role != 'admin'
                GROUP BY month, role 
                ORDER BY month ASC";
        $result = mysqli_query($this->db, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data['new_users_by_month'][] = $row;
            }
        }

        return $data;
    }

    /**
     * Announcements
     */
    public function getAnnouncements() {
        $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
        $result = mysqli_query($this->db, $sql);
        $announcements = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $announcements[] = $row;
            }
        }
        return $announcements;
    }

    public function createAnnouncement($title, $message, $target_role) {
        $sql = "INSERT INTO announcements (title, message, target_role) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $title, $message, $target_role);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    public function toggleAnnouncementStatus($id) {
        $sql = "UPDATE announcements SET is_active = NOT is_active WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>
