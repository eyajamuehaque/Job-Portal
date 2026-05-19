<?php
/**
 * views/employer/dashboard.php
 * Main dashboard for Employers to manage their job postings and view applicant counts.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/EmployerController.php';

// Secure the page
Session::init();
Session::checkRole('employer');

$employerController = new EmployerController();

// Handle job deletion
if (isset($_GET['delete_job'])) {
    $employerController->deleteJob(intval($_GET['delete_job']));
}

$jobsResult = $employerController->getDashboardData();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
        <h1 style="margin: 0;">Employer Dashboard</h1>
        <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 10px;">
            <div>
                <a href="analytics.php" class="btn-primary" style="background: #e8491d;">Analytics</a>
                <a href="shortlisted.php" class="btn-primary" style="background: #17a2b8;">Shortlisted</a>
                <a href="profile.php" class="btn-primary" style="background: #35424a;">Company Profile</a>
                <a href="post_job.php" class="btn-primary">Post New Job</a>
            </div>
            <!-- Secondary Navigation -->
            <div>
                <a href="messages.php" class="btn-primary" style="background: #ffc107; color: #333;">Messages</a>
                <a href="recruiters.php" class="btn-primary" style="background: #28a745;">Linked Recruiters</a>
                <a href="complaints.php" class="btn-primary" style="background: #dc3545;">Support/Complaints</a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="job-list">
        <h2>Your Job Postings</h2>
        
        <?php if (!empty($jobsResult)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Posted Date</th>
                        <th>Deadline</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobsResult as $job): ?>
                        <?php 
                            $deadline = new DateTime($job['deadline']);
                            $today = new DateTime();
                            $days_left = $today->diff($deadline)->format('%r%a');
                            $deadlineText = $days_left < 0 ? 'Expired' : ($days_left == 0 ? 'Today' : $days_left . ' days left');
                        ?>
                        <tr id="job-row-<?= $job['id'] ?>">
                            <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                            <td><?= $deadlineText ?></td>
                            <td><?= htmlspecialchars($job['job_type']) ?></td>
                            <td>
                                <span id="status-badge-<?= $job['id'] ?>" class="badge" style="background: <?= $job['status'] == 'active' ? '#d1e7dd' : '#f8d7da' ?>; color: #333;">
                                    <?= strtoupper($job['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button id="toggle-btn-<?= $job['id'] ?>" 
                                        onclick="toggleJobStatus(<?= $job['id'] ?>, '<?= $job['status'] ?>')" 
                                        class="btn-primary" style="padding: 5px 10px; font-size: 12px; background: #35424a; border: none; cursor: pointer; color: white;">
                                    <?= $job['status'] == 'active' ? 'Close Job' : 'Reopen' ?>
                                </button>
                                <a href="view_applicants.php?job_id=<?= $job['id'] ?>" style="margin-left: 10px; color: #e8491d; text-decoration: none; font-size: 14px;">View Applicants (<?= $job['application_count'] ?? 0 ?>)</a>
                                <a href="edit_job.php?id=<?= $job['id'] ?>" style="margin-left: 10px; color: #007bff; text-decoration: none; font-size: 14px;">Edit</a>
                                <a href="dashboard.php?delete_job=<?= $job['id'] ?>" style="margin-left: 10px; color: #dc3545; text-decoration: none; font-size: 14px;" onclick="return confirm('Are you sure you want to delete this job? This will also delete all applications for it.');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="job-card">
                <p>You haven't posted any jobs yet. <a href="post_job.php" style="color: #e8491d;">Create your first listing!</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- AJAX Script for status toggling -->
<script src="../../public/js/status-updates.js"></script>

<?php include '../partials/footer.php'; ?>