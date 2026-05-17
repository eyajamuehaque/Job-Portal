<?php
/**
 * api/toggle-bookmark.php
 * JSON endpoint for AJAX job saving/unsaving.
 */

require_once '../app/controllers/SeekerController.php';

header('Content-Type: application/json');

// Ensure user is logged in
if (Session::get('user_id') === null) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $jobId = isset($data['job_id']) ? intval($data['job_id']) : 0;
    
    if ($jobId > 0) {
        $controller = new SeekerController();
        $result = $controller->toggleBookmark($jobId);
        echo json_encode($result);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid Job ID']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
