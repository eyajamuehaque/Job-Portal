<?php
/**
 * app/controllers/SeekerController.php
 * Handles Job Seeker specific logic: Profiles, Job Searching, and Applications.
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Profile.php';
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/Application.php';
require_once __DIR__ . '/../models/SavedJob.php';
require_once __DIR__ . '/../models/JobAlert.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Complaint.php';

class SeekerController {
    private $db;
    private $profileModel;
    private $jobModel;
    private $applicationModel;
    private $savedJobModel;
    private $jobAlertModel;
    private $messageModel;
    private $complaintModel;

    public function __construct() {
        // Initialize Database and Models
        $database = new Database();
        $this->db = $database->conn;
        
        $this->profileModel = new Profile($this->db);
        $this->jobModel = new Job($this->db);
        $this->applicationModel = new Application($this->db);
        $this->savedJobModel = new SavedJob($this->db);
        $this->jobAlertModel = new JobAlert($this->db);
        $this->messageModel = new Message($this->db);
        $this->complaintModel = new Complaint($this->db);

        // Secure this controller - only seekers allowed
        Session::checkRole('seeker');
    }

    /**
     * Handle Profile Update
     */
    public function updateProfile() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $userId = Session::get('user_id');
            
            // Collect form data (Page 39 of notes)
            $data = [
                'headline'           => htmlspecialchars($_POST['headline']),
                'summary'            => htmlspecialchars($_POST['summary']),
                'skills'             => htmlspecialchars($_POST['skills']),
                'years_experience'   => intval($_POST['years_experience']),
                'education_level'    => htmlspecialchars($_POST['education_level']),
                'expected_salary'    => floatval($_POST['expected_salary']),
                'preferred_location' => htmlspecialchars($_POST['preferred_location']),
                'resume_path'        => $_POST['existing_resume'] // Default to old one
            ];

            // Handle Resume Upload (Page 40 & 100 of notes)
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
                // Fix: Use __DIR__ to get the absolute path to the public folder
                $targetDir = __DIR__ . "/../../public/uploads/resumes/";
                
                // Ensure the directory exists, create it if not
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $fileName = time() . "_" . basename($_FILES["resume"]["name"]);
                $targetFilePath = $targetDir . $fileName;

                if (move_uploaded_file($_FILES["resume"]["tmp_name"], $targetFilePath)) {
                    $data['resume_path'] = $fileName;
                }
            } else {
                // Optional: Keep the old resume path if no new file is uploaded
                $data['resume_path'] = $_POST['existing_resume'] ?? null;
            }

            // Handle Profile Picture Upload
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
                $picDir = __DIR__ . "/../../public/uploads/profile_pics/";
                if (!is_dir($picDir)) {
                    mkdir($picDir, 0777, true);
                }
                $picName = time() . "_pic_" . basename($_FILES["profile_pic"]["name"]);
                $picPath = $picDir . $picName;

                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $picPath)) {
                    $sqlPic = "UPDATE users SET profile_pic=? WHERE id=?";
                    $stmtPic = mysqli_prepare($this->db, $sqlPic);
                    mysqli_stmt_bind_param($stmtPic, "si", $picName, $userId);
                    mysqli_stmt_execute($stmtPic);
                    mysqli_stmt_close($stmtPic);
                }
            }

            // Call the model
            $success = $this->profileModel->saveSeeker($userId, $data);
            
            if ($success) {
                return "Profile updated successfully!";
            } else {
                return "Error updating profile.";
            }
        }
    }

    /**
     * Handle Job Search (Used by normal page and AJAX)
     * Page 55 (AJAX) and 64 (JSON) of notes
     */
    public function search() {
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : "";
        $category = isset($_GET['category']) ? $_GET['category'] : null;

        $jobs = $this->jobModel->search($keyword, $category);

        // If it's an AJAX request, return JSON (Page 64 of notes)
        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: application/json');
            echo json_encode($jobs);
            exit();
        }

        return $jobs;
    }

    /**
     * Handle Job Application
     */
    public function apply() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $jobId = intval($_POST['job_id']);
            $seekerId = Session::get('user_id');
            $coverLetter = htmlspecialchars($_POST['cover_letter']);
            
            // Get seeker profile to use their saved resume
            $profile = $this->profileModel->getSeeker($seekerId);
            $resume = $profile['resume_path'];

            $result = $this->applicationModel->apply($jobId, $seekerId, $coverLetter, $resume);

            if ($result === true) {
                header("Location: ../views/seeker/dashboard.php?msg=Applied successfully!");
                exit();
            } else {
                return $result; // Returns the error message string
            }
        }
    }

    /**
     * View Tracking Page
     */
    public function getMyApplications() {
        $seekerId = Session::get('user_id');
        return $this->applicationModel->getBySeeker($seekerId);
    }

    /**
     * Get user bookmarks
     */
    public function getBookmarks() {
        $seekerId = Session::get('user_id');
        return $this->savedJobModel->getByUser($seekerId);
    }

    /**
     * Toggle bookmark via AJAX
     */
    public function toggleBookmark($jobId) {
        $seekerId = Session::get('user_id');
        
        if ($this->savedJobModel->isSaved($seekerId, $jobId)) {
            $success = $this->savedJobModel->remove($seekerId, $jobId);
            return ['status' => 'removed', 'success' => $success];
        } else {
            $success = $this->savedJobModel->save($seekerId, $jobId);
            return ['status' => 'saved', 'success' => $success];
        }
    }

    /**
     * Job Alerts Handlers
     */
    public function getAlerts() {
        $seekerId = Session::get('user_id');
        return $this->jobAlertModel->getBySeeker($seekerId);
    }

    public function createAlert() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $seekerId = Session::get('user_id');
            $keyword = htmlspecialchars($_POST['keyword'] ?? '');
            $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? intval($_POST['category_id']) : null;
            $location = htmlspecialchars($_POST['location'] ?? '');
            $job_type = htmlspecialchars($_POST['job_type'] ?? '');

            $result = $this->jobAlertModel->create($seekerId, $keyword, $category_id, $location, $job_type);
            
            if ($result) {
                header("Location: job_alerts.php?msg=Alert created successfully!");
            } else {
                header("Location: job_alerts.php?error=Failed to create alert.");
            }
            exit();
        }
    }

    public function deleteAlert($id) {
        $seekerId = Session::get('user_id');
        $this->jobAlertModel->delete($id, $seekerId);
        header("Location: job_alerts.php?msg=Alert deleted.");
        exit();
    }

    public function getMatchingAlertJobs() {
        $seekerId = Session::get('user_id');
        return $this->jobAlertModel->getMatchingJobs($seekerId);
    }

    /**
     * Messaging Handlers
     */
    public function getInbox() {
        $seekerId = Session::get('user_id');
        return $this->messageModel->getInboxSummary($seekerId);
    }

    public function getConversation($contactId) {
        $seekerId = Session::get('user_id');
        $this->messageModel->markAsRead($seekerId, $contactId);
        return $this->messageModel->getConversation($seekerId, $contactId);
    }

    public function sendMessage() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $seekerId = Session::get('user_id');
            $recipientId = intval($_POST['recipient_id']);
            $body = htmlspecialchars($_POST['body']);
            
            $result = $this->messageModel->send($seekerId, $recipientId, $body);
            
            if ($result) {
                header("Location: messages.php?contact_id=" . $recipientId);
            } else {
                header("Location: messages.php?error=Failed to send message.");
            }
            exit();
        }
    }

    /**
     * Application Withdrawal
     */
    public function withdrawApplication($id) {
        $seekerId = Session::get('user_id');
        $success = $this->applicationModel->withdraw($id, $seekerId);
        
        if ($success) {
            header("Location: dashboard.php?msg=Application withdrawn.");
        } else {
            header("Location: dashboard.php?error=Cannot withdraw this application.");
        }
        exit();
    }

    /**
     * Complaints Handlers
     */
    public function submitComplaint() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $seekerId = Session::get('user_id');
            $subjectId = intval($_POST['subject_id'] ?? 0);
            $description = htmlspecialchars($_POST['description']);
            
            $result = $this->complaintModel->submit($seekerId, $subjectId, $description);
            
            if ($result) {
                header("Location: complaints.php?msg=Complaint submitted successfully!");
            } else {
                header("Location: complaints.php?error=Failed to submit complaint.");
            }
            exit();
        }
    }

    public function getMyComplaints() {
        $seekerId = Session::get('user_id');
        return $this->complaintModel->getBySubmitter($seekerId);
    }
}
?>