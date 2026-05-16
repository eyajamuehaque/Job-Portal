<?php
/**
 * views/recruiter/post_job.php
 * Form to post a new job on behalf of a client.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';
require_once '../../app/models/Job.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$recruiterController = new RecruiterController();
$jobModel = new Job($db);

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $error = $recruiterController->postJob();
}

$categories = $jobModel->getCategories();
$clients = $recruiterController->getClients();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Post a Job for a Client</h1>
            <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
        </div>

        <?php if ($error): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($clients)): ?>
            <div style="padding: 20px; background: #fff3cd; color: #856404; border-radius: 4px; margin-bottom: 20px;">
                <strong>Notice:</strong> You haven't added any clients yet. You must <a href="clients.php" style="color: #856404; text-decoration: underline;">add a client</a> before posting a job.
            </div>
        <?php else: ?>
            <div class="job-card" style="padding: 30px;">
                <form action="post_job.php" method="POST">
                    
                    <div class="form-group" style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #e8491d;">Select Client *</label>
                        <select name="client_id" class="form-control" required style="width: 100%; padding: 10px; box-sizing: border-box; font-weight: bold;">
                            <option value="">-- Choose the client this job is for --</option>
                            <?php foreach ($clients as $client): ?>
                                <?php if ($client['employer_id']): ?>
                                    <option value="emp_<?= $client['employer_id'] ?>">
                                        <?= htmlspecialchars($client['employer_name']) ?> (Registered Platform Employer)
                                    </option>
                                <?php else: ?>
                                    <option value="rc_<?= $client['id'] ?>">
                                        <?= htmlspecialchars($client['company_name_override']) ?> (Standalone Client)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Title *</label>
                        <input type="text" name="title" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>

                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Category *</label>
                            <select name="category_id" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Type *</label>
                            <select name="job_type" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="remote">Remote</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Experience Level *</label>
                            <select name="experience_level" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                                <option value="entry">Entry Level (< 2 years)</option>
                                <option value="mid">Mid Level (2-5 years)</option>
                                <option value="senior">Senior Level (5+ years)</option>
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Location *</label>
                            <input type="text" name="location" class="form-control" required placeholder="e.g. Dhaka, Bangladesh" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Min Salary (BDT)</label>
                            <input type="number" name="salary_min" class="form-control" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Max Salary (BDT)</label>
                            <input type="number" name="salary_max" class="form-control" style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Application Deadline *</label>
                            <input type="date" name="deadline" class="form-control" required style="width: 100%; padding: 8px; box-sizing: border-box;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Job Description *</label>
                        <textarea name="description" class="form-control" rows="5" required style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Requirements *</label>
                        <textarea name="requirements" class="form-control" rows="4" required style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Benefits</label>
                        <textarea name="benefits" class="form-control" rows="3" style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary" style="padding: 10px 20px; border: none; cursor: pointer; width: 100%; font-size: 16px;">Post Job on Behalf of Client</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>