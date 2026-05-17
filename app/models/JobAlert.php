<?php
/**
 * app/models/JobAlert.php
 * Handles database operations for the 'job_alerts' table.
 */

class JobAlert {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Create a new job alert
     */
    public function create($seeker_id, $keyword, $category_id, $location, $job_type) {
        $sql = "INSERT INTO job_alerts (seeker_id, keyword, category_id, location, job_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            $category_id = empty($category_id) ? null : $category_id;
            $job_type = empty($job_type) ? null : $job_type;
            
            mysqli_stmt_bind_param($stmt, "isiss", $seeker_id, $keyword, $category_id, $location, $job_type);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Delete an alert
     */
    public function delete($id, $seeker_id) {
        $sql = "DELETE FROM job_alerts WHERE id = ? AND seeker_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $id, $seeker_id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get all alerts for a seeker
     */
    public function getBySeeker($seeker_id) {
        $sql = "SELECT a.*, c.name as category_name 
                FROM job_alerts a
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.seeker_id = ?
                ORDER BY a.created_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $seeker_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $alerts = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $alerts;
        }
        return [];
    }

    /**
     * Get jobs that match the seeker's active alerts
     */
    public function getMatchingJobs($seeker_id) {
        $alerts = $this->getBySeeker($seeker_id);
        if (empty($alerts)) {
            return [];
        }

        $conditions = [];
        $params = [];
        $types = "";

        foreach ($alerts as $alert) {
            $alertConditions = [];
            
            if (!empty($alert['keyword'])) {
                $alertConditions[] = "(j.title LIKE ? OR j.description LIKE ?)";
                $params[] = '%' . $alert['keyword'] . '%';
                $params[] = '%' . $alert['keyword'] . '%';
                $types .= "ss";
            }
            if (!empty($alert['category_id'])) {
                $alertConditions[] = "j.category_id = ?";
                $params[] = $alert['category_id'];
                $types .= "i";
            }
            if (!empty($alert['location'])) {
                $alertConditions[] = "j.location LIKE ?";
                $params[] = '%' . $alert['location'] . '%';
                $types .= "s";
            }
            if (!empty($alert['job_type'])) {
                $alertConditions[] = "j.job_type = ?";
                $params[] = $alert['job_type'];
                $types .= "s";
            }

            // If an alert is completely empty, skip it to avoid fetching all jobs
            if (!empty($alertConditions)) {
                $conditions[] = "(" . implode(" AND ", $alertConditions) . ")";
            }
        }

        if (empty($conditions)) {
            return [];
        }

        $sql = "SELECT DISTINCT j.*, c.name as category_name, 
                COALESCE(e.name, r.name) AS company_name
                FROM jobs j 
                LEFT JOIN categories c ON j.category_id = c.id
                LEFT JOIN users e ON j.employer_id = e.id 
                LEFT JOIN users r ON j.recruiter_id = r.id
                WHERE " . implode(" OR ", $conditions) . " 
                ORDER BY j.created_at DESC LIMIT 50";

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
}
?>
