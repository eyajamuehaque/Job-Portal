<?php
/**
 * views/admin/settings.php
 * Manage platform-wide policies.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller->updateSettings();
}

$settings = $controller->getSettings();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px; max-width: 800px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Platform Policies & Settings</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #6c757d;">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <form action="settings.php" method="POST">
            
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #35424a;">Maximum Active Jobs per Employer</label>
                <p style="font-size: 13px; color: #666; margin-top: 0; margin-bottom: 10px;">To prevent spam, limit the number of active job postings a single employer can have at one time.</p>
                <input type="number" name="max_jobs_per_employer" value="<?= htmlspecialchars($settings['max_jobs_per_employer'] ?? '10') ?>" class="form-control" style="width: 100%; max-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required min="1">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #35424a;">Maximum Pending Applications per Seeker</label>
                <p style="font-size: 13px; color: #666; margin-top: 0; margin-bottom: 10px;">Limit how many active pending applications a job seeker can hold simultaneously.</p>
                <input type="number" name="max_applications_per_seeker" value="<?= htmlspecialchars($settings['max_applications_per_seeker'] ?? '50') ?>" class="form-control" style="width: 100%; max-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" required min="1">
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #35424a;">Default Resume Visibility</label>
                <p style="font-size: 13px; color: #666; margin-top: 0; margin-bottom: 10px;">Should resumes be visible to recruiters by default, or only when explicitly applied to a job?</p>
                <select name="resume_visibility" class="form-control" style="width: 100%; max-width: 200px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="public" <?= ($settings['resume_visibility'] ?? 'public') === 'public' ? 'selected' : '' ?>>Public (Visible to Headhunters)</option>
                    <option value="private" <?= ($settings['resume_visibility'] ?? 'public') === 'private' ? 'selected' : '' ?>>Private (Only Applications)</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="padding: 12px 25px; font-size: 16px;">Save Policies</button>
        </form>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
