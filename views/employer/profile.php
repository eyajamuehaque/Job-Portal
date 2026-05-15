<?php
/**
 * views/employer/profile.php
 * Page for employers to manage their company information and branding.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/EmployerController.php';
require_once '../../app/models/Profile.php';

Session::init();
Session::checkRole('employer');

$db = (new Database())->conn;
$employerController = new EmployerController();
$profileModel = new Profile($db);

$message = $employerController->updateProfile();
$profile = $profileModel->getEmployer(Session::get('user_id'));

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <h1>Company Profile</h1>
        <p>Your company details are visible to job seekers when they view your job postings.</p>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="job-card" style="padding: 30px;">
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="existing_logo" value="<?= $profile['logo_path'] ?? 'default_logo.png' ?>">

                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" required value="<?= htmlspecialchars($profile['company_name'] ?? '') ?>">
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Industry</label>
                        <input type="text" name="industry" class="form-control" value="<?= htmlspecialchars($profile['industry'] ?? '') ?>" placeholder="e.g. Technology, Finance">
                    </div>
                    <div style="flex: 1;">
                        <label>Company Size</label>
                        <select name="company_size" class="form-control">
                            <option value="1-10" <?= ($profile['company_size'] ?? '') == '1-10' ? 'selected' : '' ?>>1-10 Employees</option>
                            <option value="11-50" <?= ($profile['company_size'] ?? '') == '11-50' ? 'selected' : '' ?>>11-50 Employees</option>
                            <option value="51-200" <?= ($profile['company_size'] ?? '') == '51-200' ? 'selected' : '' ?>>51-200 Employees</option>
                            <option value="201-500" <?= ($profile['company_size'] ?? '') == '201-500' ? 'selected' : '' ?>>201-500 Employees</option>
                            <option value="501-1000" <?= ($profile['company_size'] ?? '') == '501-1000' ? 'selected' : '' ?>>501-1000 Employees</option>
                            <option value="1000+" <?= ($profile['company_size'] ?? '') == '1000+' ? 'selected' : '' ?>>1000+ Employees</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Company Website</label>
                    <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($profile['website'] ?? '') ?>" placeholder="https://example.com">
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Company Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($profile['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Office Address</label>
                    <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($profile['address'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="margin-top: 20px; padding: 15px; border: 1px dashed #ccc; border-radius: 4px;">
                    <label>Company Logo</label>
                    <input type="file" name="logo" accept="image/*" style="display: block; margin-top: 10px;">
                    <?php if (!empty($profile['logo_path'])): ?>
                        <img src="../../public/uploads/logos/<?= $profile['logo_path'] ?>" alt="Logo" style="height: 60px; margin-top: 10px; border-radius: 4px;">
                    <?php endif; ?>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary">Update Company Details</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>