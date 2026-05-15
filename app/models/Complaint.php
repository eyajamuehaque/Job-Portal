<?php
/**
 * app/models/Complaint.php
 * Handles database operations for the 'complaints' table.
 */

class Complaint {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Submit a new complaint
     */
    public function submit($submitter_id, $subject_id, $description) {
        $sql = "INSERT INTO complaints (submitter_id, subject_id, description) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            $subject_id = empty($subject_id) ? null : $subject_id;
            mysqli_stmt_bind_param($stmt, "iis", $submitter_id, $subject_id, $description);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get complaints submitted by a specific user
     */
    public function getBySubmitter($submitter_id) {
        $sql = "SELECT c.*, u.name as subject_name, j.title as job_title
                FROM complaints c
                LEFT JOIN users u ON c.subject_id = u.id AND c.subject_id IS NOT NULL AND (u.role='employer' OR u.role='recruiter')
                LEFT JOIN jobs j ON c.subject_id = j.id AND c.subject_id IS NOT NULL AND c.description LIKE '%job%'
                WHERE c.submitter_id = ?
                ORDER BY c.created_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $submitter_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $complaints = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $complaints;
        }
        return [];
    }
}
?>
