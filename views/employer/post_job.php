<?php
/**
 * views/employer/post_job.php
 * Form for employers to create new job listings.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/EmployerController.php';
require_once '../../app/models/Job.php';

Session::init();
Session::checkRole('employer');

$db = (new Database())->conn;
$employerController = new EmployerController();
$jobModel = new Job($db);

$message = $employerController->postJob();
$categories = $jobModel->getCategories();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <h1>Post a New Job</h1>
        <p>Fill in the details below to reach thousands of qualified candidates.</p>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="job-card" style="padding: 30px;">
            <form action="post_job.php" method="POST">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" class="form-control" required placeholder="e.g. Full Stack Web Developer">
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Category</label>
                    <select name="category_id" class="form-control" required>
                        <option value="" disabled selected>Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Job Description & Requirements</label>
                    <textarea name="description" class="form-control" rows="8" required placeholder="Describe the role, responsibilities, and necessary skills..."></textarea>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="e.g. Dhaka, Bangladesh">
                    </div>
                    <div style="flex: 1;">
                        <label>Job Type</label>
                        <select name="job_type" class="form-control" required>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="remote">Remote</option>
                            <option value="contract">Contract</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Minimum Salary ($)</label>
                        <input type="number" name="salary_min" class="form-control" step="0.01">
                    </div>
                    <div style="flex: 1;">
                        <label>Maximum Salary ($)</label>
                        <input type="number" name="salary_max" class="form-control" step="0.01">
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <button type="submit" class="btn-primary">Publish Job Posting</button>
                    <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>