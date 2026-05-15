<?php
/**
 * views/employer/edit_job.php
 * Form to edit an existing job posting.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/EmployerController.php';
require_once '../../app/models/Job.php';

Session::init();
Session::checkRole('employer');

$db = (new Database())->conn;
$employerController = new EmployerController();
$jobModel = new Job($db);

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $error = $employerController->editJob();
}

$jobId = $_GET['id'] ?? null;
if (!$jobId) {
    header("Location: dashboard.php");
    exit();
}

$job = $jobModel->getById($jobId);
if (!$job || $job['employer_id'] != Session::get('user_id')) {
    header("Location: dashboard.php?error=Job not found or access denied.");
    exit();
}

$categories = $jobModel->getCategories();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Edit Job Posting</h1>
            <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="job-card" style="padding: 30px;">
            <form action="edit_job.php?id=<?= htmlspecialchars($jobId) ?>" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="job_id" value="<?= htmlspecialchars($jobId) ?>">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Title *</label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($job['title']) ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Category *</label>
                        <select name="category_id" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $job['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Type *</label>
                        <select name="job_type" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                            <option value="full-time" <?= $job['job_type'] == 'full-time' ? 'selected' : '' ?>>Full Time</option>
                            <option value="part-time" <?= $job['job_type'] == 'part-time' ? 'selected' : '' ?>>Part Time</option>
                            <option value="contract" <?= $job['job_type'] == 'contract' ? 'selected' : '' ?>>Contract</option>
                            <option value="remote" <?= $job['job_type'] == 'remote' ? 'selected' : '' ?>>Remote</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Experience Level *</label>
                        <select name="experience_level" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                            <option value="entry" <?= $job['experience_level'] == 'entry' ? 'selected' : '' ?>>Entry Level</option>
                            <option value="mid" <?= $job['experience_level'] == 'mid' ? 'selected' : '' ?>>Mid Level</option>
                            <option value="senior" <?= $job['experience_level'] == 'senior' ? 'selected' : '' ?>>Senior Level</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Location *</label>
                        <input type="text" name="location" class="form-control" required value="<?= htmlspecialchars($job['location']) ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Min Salary ($)</label>
                        <input type="number" name="salary_min" class="form-control" value="<?= htmlspecialchars($job['salary_min'] ?? '') ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Max Salary ($)</label>
                        <input type="number" name="salary_max" class="form-control" value="<?= htmlspecialchars($job['salary_max'] ?? '') ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Deadline *</label>
                        <input type="date" name="deadline" class="form-control" required value="<?= htmlspecialchars($job['deadline']) ?>" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Description *</label>
                    <textarea name="description" class="form-control" rows="5" required style="width: 100%; padding: 8px; box-sizing: border-box;"><?= htmlspecialchars($job['description']) ?></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Requirements *</label>
                    <textarea name="requirements" class="form-control" rows="4" required style="width: 100%; padding: 8px; box-sizing: border-box;"><?= htmlspecialchars($job['requirements']) ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: bold;">Benefits</label>
                    <textarea name="benefits" class="form-control" rows="3" style="width: 100%; padding: 8px; box-sizing: border-box;"><?= htmlspecialchars($job['benefits'] ?? '') ?></textarea>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="padding: 10px 20px; border: none; cursor: pointer;">Save Changes</button>
                    <a href="dashboard.php" style="margin-left: 15px; color: #666; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
