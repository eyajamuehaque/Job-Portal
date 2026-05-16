<?php
/**
 * views/recruiter/view_applicants.php
 * Displays the candidates who applied to a specific job posted by this recruiter.
 */

require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';
require_once '../../app/models/Job.php';
require_once '../../app/models/Application.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$jobModel = new Job($db);
$applicationModel = new Application($db);

$jobId = $_GET['job_id'] ?? null;
if (!$jobId) {
    header("Location: dashboard.php");
    exit();
}

$job = $jobModel->getById($jobId);
if (!$job || $job['recruiter_id'] != Session::get('user_id')) {
    header("Location: dashboard.php?error=Job not found or access denied.");
    exit();
}

// Fetch applicants with filtering
$filterStatus = $_GET['status'] ?? null;
$filterExp = $_GET['experience'] ?? null;
$sortBy = $_GET['sort'] ?? 'applied_desc';

$applicants = $applicationModel->getByJob($jobId, $filterStatus, $filterExp, $sortBy);

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="margin-bottom: 5px;">Applicants: <?= htmlspecialchars($job['title']) ?></h1>
            <p style="color: #666; margin-top: 0;">Total Applicants: <?= count($applicants) ?></p>
        </div>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
    </div>

    <!-- Filters -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
        <form action="view_applicants.php" method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($jobId) ?>">
            
            <div style="flex: 1;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;">Status</label>
                <select name="status" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">All Statuses</option>
                    <option value="submitted" <?= $filterStatus == 'submitted' ? 'selected' : '' ?>>New (Submitted)</option>
                    <option value="reviewed" <?= $filterStatus == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                    <option value="shortlisted" <?= $filterStatus == 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                    <option value="interview" <?= $filterStatus == 'interview' ? 'selected' : '' ?>>Interview</option>
                    <option value="rejected" <?= $filterStatus == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;">Experience</label>
                <select name="experience" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">Any Experience</option>
                    <option value="0-2" <?= $filterExp == '0-2' ? 'selected' : '' ?>>Entry (0-2 years)</option>
                    <option value="3-5" <?= $filterExp == '3-5' ? 'selected' : '' ?>>Mid (3-5 years)</option>
                    <option value="6+" <?= $filterExp == '6+' ? 'selected' : '' ?>>Senior (6+ years)</option>
                </select>
            </div>
            
            <div style="flex: 1;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px;">Sort By</label>
                <select name="sort" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="applied_desc" <?= $sortBy == 'applied_desc' ? 'selected' : '' ?>>Newest First</option>
                    <option value="applied_asc" <?= $sortBy == 'applied_asc' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="exp_desc" <?= $sortBy == 'exp_desc' ? 'selected' : '' ?>>Highest Experience</option>
                </select>
            </div>
            
            <div>
                <button type="submit" class="btn-primary" style="padding: 8px 15px; cursor: pointer;">Apply Filters</button>
                <a href="view_applicants.php?job_id=<?= $jobId ?>" style="margin-left: 10px; color: #666; text-decoration: none; font-size: 14px;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Applicant List -->
    <div>
        <?php if (empty($applicants)): ?>
            <p style="text-align: center; color: #888; padding: 20px;">No applicants found matching your criteria.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Candidate</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Experience</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Applied On</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Status</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr id="app-row-<?= $app['id'] ?>" style="background: <?= $app['status'] == 'submitted' ? '#fdfdfd' : 'white' ?>;">
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <strong><?= htmlspecialchars($app['applicant_name']) ?></strong><br>
                                <small style="color: #666;"><?= htmlspecialchars($app['applicant_email']) ?></small><br>
                                <?php if ($app['headline']): ?>
                                    <small style="color: #e8491d;"><?= htmlspecialchars($app['headline']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <?= $app['years_experience'] !== null ? $app['years_experience'] . ' years' : 'N/A' ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <select onchange="updateApplicantStatus(<?= $app['id'] ?>, this.value)" 
                                        style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; 
                                        background-color: <?= $app['status'] == 'shortlisted' || $app['status'] == 'interview' ? '#d1e7dd' : ($app['status'] == 'rejected' ? '#f8d7da' : 'white') ?>;">
                                    <option value="submitted" <?= $app['status'] == 'submitted' ? 'selected' : '' ?>>New</option>
                                    <option value="reviewed" <?= $app['status'] == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                    <option value="shortlisted" <?= $app['status'] == 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                    <option value="interview" <?= $app['status'] == 'interview' ? 'selected' : '' ?>>Interview</option>
                                    <option value="rejected" <?= $app['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <button onclick="viewCoverLetter(`<?= htmlspecialchars(addslashes($app['cover_letter'])) ?>`)" style="background: none; border: none; color: #007bff; cursor: pointer; text-decoration: underline; padding: 0;">Cover Letter</button>
                                <?php if ($app['resume_path']): ?>
                                    <a href="../../public/<?= htmlspecialchars($app['resume_path']) ?>" target="_blank" style="margin-left: 10px; color: #28a745; text-decoration: underline;">Resume</a>
                                <?php endif; ?>
                                <a href="messages.php?contact_id=<?= $app['seeker_id'] ?>" style="margin-left: 10px; color: #e8491d; text-decoration: underline;">Message</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Cover Letter Modal -->
<div id="coverLetterModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 600px; max-width: 90%;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px;">Cover Letter</h3>
        <div id="coverLetterContent" style="white-space: pre-line; line-height: 1.6; max-height: 400px; overflow-y: auto; margin-bottom: 20px;"></div>
        <button onclick="document.getElementById('coverLetterModal').style.display = 'none'" class="btn-primary" style="background: #35424a; border: none; padding: 8px 20px; cursor: pointer;">Close</button>
    </div>
</div>

<script src="../../public/js/status-updates.js"></script>
<script>
function viewCoverLetter(content) {
    if (!content) {
        content = "No cover letter provided.";
    }
    document.getElementById('coverLetterContent').innerText = content;
    document.getElementById('coverLetterModal').style.display = 'flex';
}
</script>

<?php include '../partials/footer.php'; ?>
