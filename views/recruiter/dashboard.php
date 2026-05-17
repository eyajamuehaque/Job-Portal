<?php


require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

$controller = new RecruiterController();

// Handle job deletion if any (requires adding a delete method to RecruiterController, but we can just use the job model directly or add it later)
// For now, let's assume we just display jobs.

$jobs = $controller->getDashboardData();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Recruiter Dashboard</h1>
        <div>
            <a href="profile.php" class="btn-primary" style="background: #35424a;">Agency Profile</a>
            <a href="clients.php" class="btn-primary" style="background: #17a2b8;">Manage Clients</a>
            <a href="search_seekers.php" class="btn-primary" style="background: #ffc107; color: #333;">Headhunting</a>
            <a href="messages.php" class="btn-primary" style="background: #28a745;">Messages</a>
            <a href="complaints.php" class="btn-primary" style="background: #dc3545;">Complaints</a>
            <a href="client_report.php" class="btn-primary" style="background:#6610f2;">Client Report</a>
        
        
            <a href="post_job.php" class="btn-primary">Post Job for Client</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="job-list">
        <h2>Jobs Posted by You</h2>
        
        <?php if (!empty($jobs)): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #000000ff; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Job Title</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Client</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Posted Date</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Status</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><span style="background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 12px;"><?= htmlspecialchars($job['client_name']) ?></span></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <span id="status-badge-<?= $job['id'] ?>" class="badge" style="background: <?= $job['status'] == 'active' ? '#d1e7dd' : '#f8d7da' ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #333;">
                                    <?= strtoupper($job['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <button id="toggle-btn-<?= $job['id'] ?>" onclick="toggleJobStatus(<?= $job['id'] ?>, '<?= $job['status'] ?>')" 
                                        class="btn-primary" style="padding: 5px 10px; font-size: 12px; background: #35424a; border: none; cursor: pointer; color: white;">
                                    <?= $job['status'] == 'active' ? 'Close Job' : 'Reopen' ?>
                                </button>
                                <a href="view_applicants.php?job_id=<?= $job['id'] ?>" style="margin-left: 10px; color: #e8491d; text-decoration: none; font-size: 14px;">Applicants (<?= $job['application_count'] ?? 0 ?>)</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background: #e9ecef; padding: 20px; text-align: center; border-radius: 5px;">
                <p style="margin:0;">You haven't posted any jobs for your clients yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../../public/js/status-updates.js"></script>
<?php include '../partials/footer.php'; ?>