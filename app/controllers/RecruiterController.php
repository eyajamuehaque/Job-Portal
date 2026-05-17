<?php
/**
 * app/controllers/RecruiterController.php
 * Handles Recruiter specific logic: Profiles, Clients, Outreach, Jobs, and Applications.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Outreach.php';

class RecruiterController {
    private $db;
    private $profileModel;
    private $jobModel;
    private $applicationModel;
    private $messageModel;
    private $complaintModel;
    private $clientModel;
    private $outreachModel;

    public function __construct() {
        // Initialize Database and Models
        $database = new Database();
        $this->db = $database->conn;
        
        $this->profileModel = new Profile($this->db);
        $this->jobModel = new Job($this->db);
        $this->applicationModel = new Application($this->db);
        $this->messageModel = new Message($this->db);
        $this->complaintModel = new Complaint($this->db);
        $this->clientModel = new Client($this->db);
        $this->outreachModel = new Outreach($this->db);

        // Secure this controller - only recruiters allowed
        Session::checkRole('recruiter');
    }

    /**
     * Handle Agency Profile Update
     */
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $userId = Session::get('user_id');
            
            $data = [
                'agency_name'    => htmlspecialchars($_POST['agency_name']),
                'specialization' => htmlspecialchars($_POST['specialization']),
                'description'    => htmlspecialchars($_POST['description']),
                'website'        => htmlspecialchars($_POST['website'])
            ];

            $success = $this->profileModel->saveRecruiter($userId, $data);
            return $success ? "Agency profile updated!" : "Update failed.";
        }
    }

    /**
     * Dashboard Data (Jobs posted by this recruiter)
     */
    public function getDashboardData() {
        $recruiterId = Session::get('user_id');
        return $this->jobModel->getByRecruiter($recruiterId);
    }

    /**
     * Job Posting for Clients
     */
    public function postJob() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $recruiterId = Session::get('user_id');
            
            // Client could be linked employer (numeric) or standalone (null employer_id, tracked differently, but for Jobs table we only store employer_id and recruiter_id)
            // If standalone, employer_id is NULL, recruiter_id is set.
            $clientIdStr = $_POST['client_id']; // This could be "emp_5" or "rc_2"
            $employerId = null;
            
            if (strpos($clientIdStr, 'emp_') === 0) {
                $employerId = intval(substr($clientIdStr, 4));
            }

            $success = $this->jobModel->create(
                $employerId, // Could be null for standalone
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
                $_POST['deadline'] ?? date('Y-m-d', strtotime('+30 days')),
                $recruiterId
            );

            if ($success) {
                header("Location: dashboard.php?msg=Job posted successfully for client!");
                exit();
            }
            return "Error posting job.";
        }
    }

    /**
     * Messaging
     */
    public function getInbox() {
        $recruiterId = Session::get('user_id');
        return $this->messageModel->getInboxSummary($recruiterId);
    }

    public function getConversation($contactId) {
        $recruiterId = Session::get('user_id');
        $this->messageModel->markAsRead($recruiterId, $contactId);
        return $this->messageModel->getConversation($recruiterId, $contactId);
    }

    public function sendMessage() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $recruiterId = Session::get('user_id');
            $recipientId = intval($_POST['recipient_id']);
            $body = htmlspecialchars($_POST['body']);
            
            $result = $this->messageModel->send($recruiterId, $recipientId, $body);
            
            if ($result) {
                header("Location: messages.php?contact_id=" . $recipientId);
            } else {
                header("Location: messages.php?error=Failed to send message.");
            }
            exit();
        }
    }

    /**
     * AJAX Toggle Job Status
     */
    public function toggleJobStatus() {
        if (isset($_GET['job_id']) && isset($_GET['status'])) {
            $jobId = intval($_GET['job_id']);
            $status = $_GET['status'];
            $success = $this->jobModel->updateStatus($jobId, $status);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'new_status' => $status]);
            exit();
        }
    }

    /**
     * AJAX Update Application Status
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
     * Complaints
     */
    public function submitComplaint() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $recruiterId = Session::get('user_id');
            $subjectId = intval($_POST['subject_id'] ?? 0);
            $description = htmlspecialchars($_POST['description']);
            
            $result = $this->complaintModel->submit($recruiterId, $subjectId, $description);
            
            if ($result) {
                header("Location: complaints.php?msg=Complaint submitted successfully!");
            } else {
                header("Location: complaints.php?error=Failed to submit complaint.");
            }
            exit();
        }
    }

    /**
     * Client Management
     */
    public function getClients() {
        $recruiterId = Session::get('user_id');
        return $this->clientModel->getByRecruiter($recruiterId);
    }

    public function addClient() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $recruiterId = Session::get('user_id');
            $employerId = !empty($_POST['employer_id']) ? intval($_POST['employer_id']) : null;
            $companyOverride = !empty($_POST['company_name_override']) ? htmlspecialchars($_POST['company_name_override']) : null;

            $success = $this->clientModel->add($recruiterId, $employerId, $companyOverride);
            
            if ($success) {
                header("Location: clients.php?msg=Client added successfully.");
            } else {
                header("Location: clients.php?error=Failed to add client. Check if they are already added.");
            }
            exit();
        }
    }

    public function removeClient($id) {
        $recruiterId = Session::get('user_id');
        $success = $this->clientModel->remove($id, $recruiterId);
        
        if ($success) {
            header("Location: clients.php?msg=Client removed successfully.");
        } else {
            header("Location: clients.php?error=Failed to remove client.");
        }
        exit();
    }

    /**
     * Headhunting & Outreach
     */
    public function searchSeekers() {
        $query = $_GET['q'] ?? '';
        $skills = $_GET['skills'] ?? '';
        return $this->outreachModel->searchSeekers($query, $skills);
    }

    public function getActiveJobs() {
        $recruiterId = Session::get('user_id');
        return $this->jobModel->getByRecruiter($recruiterId);
    }

    public function sendOutreach() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $recruiterId = Session::get('user_id');
            $seekerId = intval($_POST['seeker_id']);
            $jobId = intval($_POST['job_id']);
            $message = htmlspecialchars($_POST['message']);

            $success = $this->outreachModel->send($recruiterId, $seekerId, $jobId, $message);
            
            // Also send a standard message so it appears in their inbox
            $this->messageModel->send($recruiterId, $seekerId, "Job Opportunity: " . $message);

            if ($success) {
                header("Location: search_seekers.php?msg=Outreach sent successfully!");
            } else {
                header("Location: search_seekers.php?error=Failed to send outreach.");
            }
            exit();
        }
    }
}
?>