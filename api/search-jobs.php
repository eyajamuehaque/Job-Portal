<?php
/**
 * api/search-jobs.php
 * JSON endpoint for AJAX job searching and filtering.
 */

require_once '../app/core/Database.php';
require_once '../app/core/Session.php';
require_once '../app/models/Job.php';

Session::init();

header('Content-Type: application/json');

$database = new Database();
$jobModel = new Job($database->conn);

$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;
$location = isset($_GET['location']) ? $_GET['location'] : "";
$job_type = isset($_GET['job_type']) ? $_GET['job_type'] : "";
$experience_level = isset($_GET['experience_level']) ? $_GET['experience_level'] : "";
$salary_min = isset($_GET['salary_min']) ? floatval($_GET['salary_min']) : null;

$jobs = $jobModel->search($keyword, $category_id, $location, $job_type, $experience_level, $salary_min);

$savedJobIds = [];
$role = Session::get('role');
if ($role === 'seeker') {
    require_once '../app/models/SavedJob.php';
    $savedJobModel = new SavedJob($database->conn);
    $savedJobs = $savedJobModel->getByUser(Session::get('user_id'));
    foreach ($savedJobs as $sj) {
        $savedJobIds[] = $sj['job_id'];
    }
}


foreach ($jobs as &$job) {
    $job['isBookmarked'] = in_array($job['id'], $savedJobIds);
    $job['description_short'] = (strlen($job['description']) > 180) 
        ? substr(htmlspecialchars($job['description']), 0, 180) . '...' 
        : htmlspecialchars($job['description']);
    $job['title_safe'] = htmlspecialchars($job['title']);
    $job['location_safe'] = htmlspecialchars($job['location']);
    $job['job_type_safe'] = htmlspecialchars($job['job_type']);
}

echo json_encode(['success' => true, 'jobs' => $jobs, 'role' => $role]);
?>