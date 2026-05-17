<?php
/**
 * views/recruiter/outreach.php
 * Form to send a headhunting message to a specific candidate.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';
require_once '../../app/models/Profile.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$controller = new RecruiterController();
$profileModel = new Profile($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller->sendOutreach();
}

$seekerId = $_GET['seeker_id'] ?? null;
if (!$seekerId) {
    header("Location: search_seekers.php");
    exit();
}

// Fetch the seeker's basic info
$seeker = $profileModel->getSeeker($seekerId);
$userSql = "SELECT name FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $userSql);
mysqli_stmt_bind_param($stmt, "i", $seekerId);
mysqli_stmt_execute($stmt);
$userRes = mysqli_stmt_get_result($stmt);
$userInfo = mysqli_fetch_assoc($userRes);
mysqli_stmt_close($stmt);

// Fetch active jobs posted by this recruiter to link the outreach to a specific job
$jobs = $controller->getActiveJobs();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Headhunt Candidate</h1>
            <a href="search_seekers.php" class="btn-primary" style="background: #35424a;">Back to Search</a>
        </div>

        <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3 style="margin-top: 0;">Candidate: <?= htmlspecialchars($userInfo['name']) ?></h3>
            <p style="margin: 5px 0;"><strong>Headline:</strong> <?= htmlspecialchars($seeker['headline'] ?? 'N/A') ?></p>
            <p style="margin: 5px 0;"><strong>Experience:</strong> <?= $seeker['years_experience'] ?? 0 ?> years</p>
        </div>

        <div class="job-card" style="padding: 30px;">
            <?php if (empty($jobs)): ?>
                <div style="padding: 20px; background: #fff3cd; color: #856404; border-radius: 4px;">
                    <strong>Notice:</strong> You don't have any active job postings. You must <a href="post_job.php" style="color: #856404; text-decoration: underline;">post a job</a> before you can headhunt candidates for it.
                </div>
            <?php else: ?>
                <form action="outreach.php?seeker_id=<?= htmlspecialchars($seekerId) ?>" method="POST">
                    <input type="hidden" name="seeker_id" value="<?= htmlspecialchars($seekerId) ?>">
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #e8491d;">Which job are you headhunting for? *</label>
                        <select name="job_id" class="form-control" required style="width: 100%; padding: 10px; box-sizing: border-box;">
                            <option value="">-- Select an active job --</option>
                            <?php foreach ($jobs as $job): ?>
                                <?php if ($job['status'] == 'active'): ?>
                                    <option value="<?= $job['id'] ?>"><?= htmlspecialchars($job['title']) ?> (For: <?= htmlspecialchars($job['client_name']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Outreach Message *</label>
                        <p style="font-size: 13px; color: #666; margin-top: 0;">This will be sent directly to the candidate's inbox.</p>
                        <textarea name="message" class="form-control" rows="6" required style="width: 100%; padding: 10px; box-sizing: border-box;" placeholder="Hi <?= htmlspecialchars($userInfo['name']) ?>,\n\nI came across your profile and was very impressed with your background in..."></textarea>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary" style="padding: 12px 25px; border: none; cursor: pointer; background: #28a745; font-size: 16px;">Send Outreach Message</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
