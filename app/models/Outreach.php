outreach_history.php<?php
/**
 * app/models/Outreach.php
 * Handles recruiter headhunting and outreach to job seekers.
 */

class Outreach {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Send an outreach message to a job seeker
     */
    public function send($recruiter_id, $seeker_id, $job_id, $message) {
        $sql = "INSERT INTO recruiter_outreach (recruiter_id, seeker_id, job_id, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiis", $recruiter_id, $seeker_id, $job_id, $message);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get outreach history for a specific recruiter
     */
    public function getByRecruiter($recruiter_id) {
        $sql = "SELECT o.*, u.name as seeker_name, j.title as job_title 
                FROM recruiter_outreach o
                JOIN users u ON o.seeker_id = u.id
                JOIN jobs j ON o.job_id = j.id
                WHERE o.recruiter_id = ?
                ORDER BY o.sent_at DESC";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $data;
        }
        return [];
    }

    /**
     * Search Job Seekers (Headhunting)
     */
    public function searchSeekers($query = "", $skills = "") {
        $sql = "SELECT sp.*, u.name, u.email 
                FROM seeker_profiles sp
                JOIN users u ON sp.user_id = u.id
                WHERE u.is_active = 1";
        
        $params = [];
        $types = "";

       if (!empty($query)) {
    $sql .= " AND (
                sp.headline LIKE ? 
                OR sp.summary LIKE ? 
                OR u.name LIKE ? 
                OR sp.preferred_location LIKE ?
                OR sp.years_experience LIKE ?
                OR sp.expected_salary LIKE ?
            )";

    $search = "%{$query}%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "ssssss";
}

        if (!empty($skills)) {
            $sql .= " AND sp.skills LIKE ?";
            $params[] = "%{$skills}%";
            $types .= "s";
        }

        $sql .= " ORDER BY sp.years_experience DESC LIMIT 50";

        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $seekers = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $seekers;
        }
        return [];
    }
}
?>
