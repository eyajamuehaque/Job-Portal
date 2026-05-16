<?php
/**
 * views/recruiter/profile.php
 * Page for recruiters to manage their agency information.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';
require_once '../../app/models/Profile.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$recruiterController = new RecruiterController();
$profileModel = new Profile($db);

$message = $recruiterController->updateProfile();
$profile = $profileModel->getRecruiter(Session::get('user_id'));

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <h1>Agency Profile</h1>
        <p>Your agency details are visible to candidates when you contact them.</p>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="job-card" style="padding: 30px;">
            <form action="profile.php" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Agency Name</label>
                    <input type="text" name="agency_name" class="form-control" required value="<?= htmlspecialchars($profile['agency_name'] ?? '') ?>" style="width:100%; padding:8px; box-sizing:border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Specialization</label>
                    <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($profile['specialization'] ?? '') ?>" placeholder="e.g. IT Recruitment, Executive Search" style="width:100%; padding:8px; box-sizing:border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Agency Website</label>
                    <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($profile['website'] ?? '') ?>" placeholder="https://example.com" style="width:100%; padding:8px; box-sizing:border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Description</label>
                    <textarea name="description" class="form-control" rows="5" style="width:100%; padding:8px; box-sizing:border-box;"><?= htmlspecialchars($profile['description'] ?? '') ?></textarea>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="padding:10px 20px; border:none; cursor:pointer;">Update Profile</button>
                    <a href="dashboard.php" style="margin-left:15px; color:#666; text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>