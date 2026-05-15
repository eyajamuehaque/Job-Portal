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
            'open_complaints' => 0
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
}
?>
