<?php
/**
 * app/models/Analytics.php
 * Handles hiring analytics and statistics for Employer/Recruiter dashboards.
 */

class Analytics {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Get overall employer metrics
     */
    public function getEmployerOverview($employer_id) {
        $data = [
            'total_jobs' => 0,
            'active_jobs' => 0,
            'total_applications' => 0,
            'status_breakdown' => [
                'submitted' => 0,
                'reviewed' => 0,
                'shortlisted' => 0,
                'interview' => 0,
                'rejected' => 0,
                'withdrawn' => 0
            ]
        ];

        // Job stats
        $sqlJobs = "SELECT status, COUNT(*) as count FROM jobs WHERE employer_id = ? GROUP BY status";
        $stmtJobs = mysqli_prepare($this->db, $sqlJobs);
        if ($stmtJobs) {
            mysqli_stmt_bind_param($stmtJobs, "i", $employer_id);
            mysqli_stmt_execute($stmtJobs);
            $resJobs = mysqli_stmt_get_result($stmtJobs);
            while ($row = mysqli_fetch_assoc($resJobs)) {
                $data['total_jobs'] += $row['count'];
                if ($row['status'] == 'active') {
                    $data['active_jobs'] = $row['count'];
                }
            }
            mysqli_stmt_close($stmtJobs);
        }

        // Application stats
        $sqlApps = "SELECT a.status, COUNT(*) as count 
                    FROM applications a 
                    JOIN jobs j ON a.job_id = j.id 
                    WHERE j.employer_id = ? 
                    GROUP BY a.status";
        $stmtApps = mysqli_prepare($this->db, $sqlApps);
        if ($stmtApps) {
            mysqli_stmt_bind_param($stmtApps, "i", $employer_id);
            mysqli_stmt_execute($stmtApps);
            $resApps = mysqli_stmt_get_result($stmtApps);
            while ($row = mysqli_fetch_assoc($resApps)) {
                $data['total_applications'] += $row['count'];
                $data['status_breakdown'][$row['status']] = $row['count'];
            }
            mysqli_stmt_close($stmtApps);
        }

        return $data;
    }

    /**
     * Get metrics for a specific job
     */
    public function getJobMetrics($job_id) {
        $data = [
            'total' => 0,
            'submitted' => 0,
            'reviewed' => 0,
            'shortlisted' => 0,
            'interview' => 0,
            'rejected' => 0,
            'withdrawn' => 0
        ];

        $sql = "SELECT status, COUNT(*) as count FROM applications WHERE job_id = ? GROUP BY status";
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $job_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $data['total'] += $row['count'];
                $data[$row['status']] = $row['count'];
            }
            mysqli_stmt_close($stmt);
        }
        return $data;
    }
}
?>
