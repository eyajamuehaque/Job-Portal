<?php
/**
 * views/seeker/dashboard.php
 * Main dashboard for Job Seekers to track their applications.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/SeekerController.php';

// Secure the page - only seekers allowed
Session::init();
Session::checkRole('seeker');


$seekerController = new SeekerController();

if (isset($_GET['withdraw'])) {
    $seekerController->withdrawApplication(intval($_GET['withdraw']));
}

$applications = $seekerController->getMyApplications();
$matchingJobsCount = count($seekerController->getMatchingAlertJobs());

// Include the header partial
include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Welcome, <?= htmlspecialchars(Session::get('name')) ?></h1>
        <div>
            <a href="profile.php" class="btn-primary" style="background: #35424a;">Edit Profile</a>
            <a href="bookmarks.php" class="btn-primary" style="background: #17a2b8;">Bookmarks</a>
            <a href="job_alerts.php" class="btn-primary" style="background: #ffc107; color: #333; position: relative;">
                Alerts
                <?php if ($matchingJobsCount > 0): ?>
                    <span style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; font-weight: bold;"><?= $matchingJobsCount ?></span>
                <?php endif; ?>
            </a>
            <a href="messages.php" class="btn-primary" style="background: #28a745;">Messages</a>
            <a href="complaints.php" class="btn-primary" style="background: #dc3545;">Complaints</a>
            <a href="../../public/index.php" class="btn-primary">Browse Jobs</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="job-list">
        <h2>My Applications</h2>
        
        <?php if (!empty($applications)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($app['job_title']) ?></strong></td>
                            <td><?= htmlspecialchars($app['company_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td>
                                <span class="badge" style="padding: 5px 10px; border-radius: 4px; background: 
                                    <?php 
                                        echo match($app['status']) {
                                            'submitted' => '#cfe2ff',
                                            'shortlisted' => '#d1e7dd',
                                            'interview' => '#fff3cd',
                                            'rejected' => '#f8d7da',
                                            default => '#f4f4f4'
                                        };
                                    ?>; color: #333; font-size: 12px; text-transform: uppercase;">
                                    <?= $app['status'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="application_details.php?id=<?= $app['id'] ?>" style="color: #e8491d; text-decoration: none; font-size: 14px; margin-right: 10px;">View</a>
                                <?php if ($app['status'] === 'submitted'): ?>
                                    <a href="dashboard.php?withdraw=<?= $app['id'] ?>" style="color: #dc3545; text-decoration: none; font-size: 14px;" onclick="return confirm('Are you sure you want to withdraw this application?');">Withdraw</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="job-card">
                <p>You haven't applied to any jobs yet. <a href="../../public/index.php" style="color: #e8491d;">Start searching now!</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Include the footer partial
include '../partials/footer.php'; 
?>