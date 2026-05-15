<?php
/**
 * api/update-status.php
 * Consolidated endpoint for AJAX status updates.
 * Matches logic in public/js/status-updates.js
 */

require_once '../app/core/Session.php';
require_once '../app/controllers/EmployerController.php';
require_once '../app/controllers/RecruiterController.php';

// 1. Initialize session to identify the user
Session::init();

// 2. Security Check: Only Employers or Recruiters can update statuses
$role = Session::get('role');
if ($role !== 'employer' && $role !== 'recruiter') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$controller = $role === 'employer' ? new EmployerController() : new RecruiterController();

/**
 * 3. Route the request based on parameters
 * Matches the logic in your status-updates.js
 */

// Handle Job Status Toggle (GET request from toggleJobStatus function)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['job_id'])) {
    $controller->toggleJobStatus();
} 

// Handle Applicant Status Update (POST request from updateApplicantStatus function)
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {
    $controller->updateApplicantStatus();
} 

// Fallback for invalid requests
else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
}