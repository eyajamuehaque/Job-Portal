<?php
/**
 * app/models/Application.php
 * Handles database operations for the 'applications' table.
 */

class Application {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Submit a new job application
     * Role: Job Seeker
     */
    public function apply($job_id, $seeker_id, $cover_letter, $resume_path, $recruiter_id = null) {
       
        if ($this->hasAlreadyApplied($job_id, $seeker_id)) {
            return "You have already applied for this position.";
        }

        $sql = "INSERT INTO applications (job_id, seeker_id, recruiter_id, cover_letter, resume_path) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->db, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiiss", $job_id, $seeker_id, $recruiter_id, $cover_letter, $resume_path);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Check if a seeker has already applied to a specific job
     */
    public function hasAlreadyApplied($job_id, $seeker_id) {
        $sql = "SELECT id FROM applications WHERE job_id = ? AND seeker_id = ? AND status != 'withdrawn'";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $job_id, $seeker_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $count = mysqli_stmt_num_rows($stmt);
            mysqli_stmt_close($stmt);
            return $count > 0;
        }
        return false;
    }

    /**
     * Get all applications for a seeker
     * Role: Job Seeker (Tracking Page)
     */
    public function getBySeeker($seeker_id) {
        $sql = "SELECT a.*, j.title as job_title, u.name as company_name 
                FROM applications a
                JOIN jobs j ON a.job_id = j.id
                JOIN users u ON j.employer_id = u.id
                WHERE a.seeker_id = ? 
                ORDER BY a.applied_at DESC";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $seeker_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $applications = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $applications;
        }
        return [];
    }

    /**
     * Get all applications for a specific job with filters
     * Role: Employer/Recruiter
     */
    public function getByJob($job_id, $status = null, $experience_level = null, $date_sort = 'DESC') {
        $sql = "SELECT a.*, u.name as applicant_name, u.email as applicant_email, sp.years_experience, sp.headline 
                FROM applications a
                JOIN users u ON a.seeker_id = u.id
                LEFT JOIN seeker_profiles sp ON a.seeker_id = sp.user_id
                WHERE a.job_id = ?";
                
        $params = [$job_id];
        $types = "i";

        if (!empty($status)) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if (!empty($experience_level)) {
            if ($experience_level == 'entry') {
                $sql .= " AND (sp.years_experience < 2 OR sp.years_experience IS NULL)";
            } elseif ($experience_level == 'mid') {
                $sql .= " AND sp.years_experience >= 2 AND sp.years_experience <= 5";
            } elseif ($experience_level == 'senior') {
                $sql .= " AND sp.years_experience > 5";
            }
        }

        $sql .= " ORDER BY a.applied_at " . ($date_sort == 'ASC' ? 'ASC' : 'DESC');
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $applications = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $applications;
        }
        return [];
    }

    /**
     * Update application status
     * Role: Employer/Recruiter (AJAX requirement)
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $status, $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Withdraw an application
     * Role: Job Seeker
     */
    public function withdraw($id, $seeker_id) {
        // Only allow if seeker owns the application and it's not already reviewed
        $sql = "UPDATE applications SET status = 'withdrawn' 
                WHERE id = ? AND seeker_id = ? AND status = 'submitted'";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $id, $seeker_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}
?>