<?php
/**
 * app/models/Job.php
 * Handles all database operations for the 'jobs' table.
 */

class Job {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Create a new job posting
     * Role: Employer or Recruiter
     */
    public function create($employer_id, $category_id, $title, $description, $requirements, $benefits, $location, $job_type, $experience_level, $salary_min, $salary_max, $deadline, $recruiter_id = null) {
        $sql = "INSERT INTO jobs (employer_id, recruiter_id, category_id, title, description, requirements, benefits, location, job_type, experience_level, salary_min, salary_max, deadline) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->db, $sql);

        if ($stmt) {
            $employer_id = empty($employer_id) ? null : $employer_id;
            
            mysqli_stmt_bind_param($stmt, "iiissssssssss", 
                $employer_id, $recruiter_id, $category_id, $title, $description, 
                $requirements, $benefits, $location, $job_type, $experience_level, 
                $salary_min, $salary_max, $deadline
            );
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Edit a job posting
     */
    public function edit($id, $category_id, $title, $description, $requirements, $benefits, $location, $job_type, $experience_level, $salary_min, $salary_max, $deadline) {
        $sql = "UPDATE jobs SET category_id=?, title=?, description=?, requirements=?, benefits=?, location=?, job_type=?, experience_level=?, salary_min=?, salary_max=?, deadline=? WHERE id=?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issssssssssi", 
                $category_id, $title, $description, $requirements, $benefits, 
                $location, $job_type, $experience_level, $salary_min, $salary_max, $deadline, $id
            );
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Delete a job posting
     */
    public function delete($id) {
        $sql = "DELETE FROM jobs WHERE id=?";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }

    /**
     * Get all jobs posted by an employer
     */
    public function getByEmployer($employer_id) {
        $sql = "SELECT j.*, c.name as category_name, 
                (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count
                FROM jobs j 
                JOIN categories c ON j.category_id = c.id 
                WHERE j.employer_id = ?
                ORDER BY j.created_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $employer_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $jobs;
        }
        return [];
    }

    /**
     * Get all jobs posted by a recruiter
     */
    public function getByRecruiter($recruiter_id) {
        $sql = "SELECT j.*, c.name as category_name, 
                (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as application_count,
                COALESCE(e.name, 'Confidential Client') as client_name
                FROM jobs j 
                JOIN categories c ON j.category_id = c.id 
                LEFT JOIN users e ON j.employer_id = e.id
                WHERE j.recruiter_id = ?
                ORDER BY j.created_at DESC";
                
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
            return $jobs;
        }
        return [];
    }

    /**
     * Search jobs with filters (Used for AJAX search)
     * Role: Job Seeker
     */
    public function search($keyword = "", $category_id = null, $location = "", $job_type = "", $experience_level = "", $salary_min = null) {
        $keywordParam = "%" . $keyword . "%";
        
        $sql = "SELECT j.*, c.name as category_name 
                FROM jobs j 
                JOIN categories c ON j.category_id = c.id 
                WHERE j.status = 'active'";

        $params = [];
        $types = "";

        if (!empty($keyword)) {
            $sql .= " AND (j.title LIKE ? OR j.description LIKE ?)";
            $params[] = $keywordParam;
            $params[] = $keywordParam;
            $types .= "ss";
        }

        if (!empty($category_id)) {
            $sql .= " AND j.category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if (!empty($location)) {
            $sql .= " AND j.location LIKE ?";
            $params[] = "%" . $location . "%";
            $types .= "s";
        }

        if (!empty($job_type)) {
            $sql .= " AND j.job_type = ?";
            $params[] = $job_type;
            $types .= "s";
        }

        if (!empty($experience_level)) {
            $sql .= " AND j.experience_level = ?";
            $params[] = $experience_level;
            $types .= "s";
        }

        if (!empty($salary_min)) {
            $sql .= " AND j.salary_min >= ?";
            $params[] = $salary_min;
            $types .= "d";
        }

        $sql .= " ORDER BY j.created_at DESC";

        $stmt = mysqli_prepare($this->db, $sql);

        if ($stmt) {
            if (!empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }

            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $jobs = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $jobs[] = $row;
            }
            
            mysqli_stmt_close($stmt);
            return $jobs;
        }
        return [];
    }

    /**
     * Get single job details
     */
    public function getById($id) {
        $sql = "SELECT j.*, c.name as category_name, u.name as employer_name 
                FROM jobs j 
                JOIN categories c ON j.category_id = c.id 
                LEFT JOIN users u ON j.employer_id = u.id 
                WHERE j.id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $job = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $job;
        }
        return null;
    }

    /**
     * Update job status (e.g., Close a job)
     * Role: Employer (AJAX Toggle feature)
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE jobs SET status = ? WHERE id = ?";
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
     * Get all categories for dropdowns
     */
    public function getCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}
?>