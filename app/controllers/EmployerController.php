<?php
/**
 * app/controllers/EmployerController.php
 * Handles Employer specific logic: Company Profiles, Job Postings, and Applicant Management.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Complaint.php';

class EmployerController {
    private $db;
    private $profileModel;
    private $jobModel;
    private $applicationModel;
    private $messageModel;
    private $complaintModel;

    public function __construct() {
        // Initialize Database and Models
        $database = new Database();
        $this->db = $database->conn;
        
        $this->profileModel = new Profile($this->db);
        $this->jobModel = new Job($this->db);
        $this->applicationModel = new Application($this->db);
        $this->messageModel = new Message($this->db);
        $this->complaintModel = new Complaint($this->db);

        // Secure this controller - only employers allowed
        Session::checkRole('employer');
    }

    /**
     * Handle Company Profile Update
     */
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $userId = Session::get('user_id');
            
            $data = [
                'company_name' => htmlspecialchars($_POST['company_name']),
                'industry'     => htmlspecialchars($_POST['industry']),
                'company_size' => htmlspecialchars($_POST['company_size']),
                'description'  => htmlspecialchars($_POST['description']),
                'website'      => htmlspecialchars($_POST['website']),
                'address'      => htmlspecialchars($_POST['address']),
                'logo_path'    => $_POST['existing_logo']
            ];

            // Handle Logo Upload (Page 40 of notes)
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
                $targetDir = __DIR__ . "/../../public/uploads/logos/";
                $fileName = time() . "_" . basename($_FILES["logo"]["name"]);
                if (move_uploaded_file($_FILES["logo"]["tmp_name"], $targetDir . $fileName)) {
                    $data['logo_path'] = $fileName;
                }
            }

            $success = $this->profileModel->saveEmployer($userId, $data);
            return $success ? "Company profile updated!" : "Update failed.";
        }
    }

    public function postJob() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $employerId = Session::get('user_id');
            
            $success = $this->jobModel->create(
                $employerId,
                intval($_POST['category_id']),
                htmlspecialchars($_POST['title']),
                htmlspecialchars($_POST['description']),
                htmlspecialchars($_POST['requirements'] ?? ''),
                htmlspecialchars($_POST['benefits'] ?? ''),
                htmlspecialchars($_POST['location']),
                $_POST['job_type'],
                $_POST['experience_level'] ?? 'entry',
                $_POST['salary_min'],
                $_POST['salary_max'],
                $_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days'))
            );

            if ($success) {
                header("Location: dashboard.php?msg=Job posted successfully!");
                exit();
            }
            return "Error posting job.";
        }
    }

    /**
     * Edit a Job Posting
     */
    public function editJob() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $jobId = intval($_POST['job_id']);
            
            // Verify ownership first (for security, ideally checking employer_id matches)
            $job = $this->jobModel->getById($jobId);
            if (!$job || $job['employer_id'] != Session::get('user_id')) {
                header("Location: dashboard.php?error=Unauthorized edit.");
                exit();
            }

            $success = $this->jobModel->edit(
                $jobId,
                intval($_POST['category_id']),
                htmlspecialchars($_POST['title']),
                htmlspecialchars($_POST['description']),
                htmlspecialchars($_POST['requirements'] ?? ''),
                htmlspecialchars($_POST['benefits'] ?? ''),
                htmlspecialchars($_POST['location']),
                $_POST['job_type'],
                $_POST['experience_level'] ?? 'entry',
                $_POST['salary_min'],
                $_POST['salary_max'],
                $_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days'))
            );

            if ($success) {
                header("Location: dashboard.php?msg=Job updated successfully!");
                exit();
            }
            return "Error updating job.";
        }
    }

    /**
     * Delete a Job Posting
     */
    public function deleteJob($id) {
        $job = $this->jobModel->getById($id);
        if ($job && $job['employer_id'] == Session::get('user_id')) {
            $this->jobModel->delete($id);
            header("Location: dashboard.php?msg=Job deleted successfully.");
        } else {
            header("Location: dashboard.php?error=Unauthorized delete.");
        }
        exit();
    }

    /**
     * AJAX Toggle Job Status (Active/Closed)
     * Requirement: AJAX with JSON response (Page 55 & 64)
     */
    public function toggleJobStatus() {
        if (isset($_GET['job_id']) && isset($_GET['status'])) {
            $jobId = intval($_GET['job_id']);
            $status = $_GET['status']; // 'active' or 'closed'

            $success = $this->jobModel->updateStatus($jobId, $status);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'new_status' => $status]);
            exit();
        }
    }

    /**
     * AJAX Update Application Status
     * Requirement: drop-down update via AJAX
     */
    public function updateApplicantStatus() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $appId = intval($_POST['application_id']);
            $status = $_POST['status'];

            $success = $this->applicationModel->updateStatus($appId, $status);

            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit();
        }
    }

    /**
     * Dashboard Data
     */
    public function getDashboardData() {
        $employerId = Session::get('user_id');
        return $this->jobModel->getByEmployer($employerId);
    }

    /**
     * Messaging Handlers
     */
    public function getInbox() {
        $employerId = Session::get('user_id');
        return $this->messageModel->getInboxSummary($employerId);
    }

    public function getConversation($contactId) {
        $employerId = Session::get('user_id');
        $this->messageModel->markAsRead($employerId, $contactId);
        return $this->messageModel->getConversation($employerId, $contactId);
    }

    public function sendMessage() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $employerId = Session::get('user_id');
            $recipientId = intval($_POST['recipient_id']);
            $body = htmlspecialchars($_POST['body']);
            
            $result = $this->messageModel->send($employerId, $recipientId, $body);
            
            if ($result) {
                header("Location: messages.php?contact_id=" . $recipientId);
            } else {
                header("Location: messages.php?error=Failed to send message.");
            }
            exit();
        }
    }

    /**
     * Complaint Handler
     */
    public function submitComplaint() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $employerId = Session::get('user_id');
            $subjectId = intval($_POST['subject_id'] ?? 0);
            $description = htmlspecialchars($_POST['description']);
            
            $result = $this->complaintModel->submit($employerId, $subjectId, $description);
            
            if ($result) {
                header("Location: complaints.php?msg=Complaint submitted successfully!");
            } else {
                header("Location: complaints.php?error=Failed to submit complaint.");
            }
            exit();
        }
    }
}
?>