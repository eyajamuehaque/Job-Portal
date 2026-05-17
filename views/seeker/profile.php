<?php
/**
 * views/seeker/profile.php
 * Page for job seekers to update their professional profile and resume.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/SeekerController.php';
require_once '../../app/models/Profile.php';

// Secure the page
Session::init();
Session::checkRole('seeker');

$db = (new Database())->conn;
$seekerController = new SeekerController();
$profileModel = new Profile($db);

// Handle Profile Update
$message = $seekerController->updateProfile();

// Fetch current profile data
$profile = $profileModel->getSeeker(Session::get('user_id'));

// Fetch current user data (for profile pic)
$userQuery = mysqli_query($db, "SELECT profile_pic FROM users WHERE id = " . Session::get('user_id'));
$userData = mysqli_fetch_assoc($userQuery);
$profilePic = $userData['profile_pic'] ?? null;

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="max-width: 800px; margin: auto;">
        <h1>Manage Professional Profile</h1>
        <p>Complete your profile to increase your chances of being noticed by employers.</p>

        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="job-card" style="padding: 30px;">
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <!-- Hidden field for existing resume if no new one is uploaded -->
                <input type="hidden" name="existing_resume" value="<?= $profile['resume_path'] ?? '' ?>">

                <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                    <div>
                        <?php if ($profilePic): ?>
                            <img src="../../public/uploads/profile_pics/<?= htmlspecialchars($profilePic) ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #eee;">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; color: #999; font-size: 14px;">
                                No Image
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 8px;">Profile Picture</label>
                        <input type="file" name="profile_pic" accept="image/*">
                        <p style="margin: 5px 0 0; font-size: 12px; color: #777;">Upload a square image for best results.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Professional Headline</label>
                    <input type="text" name="headline" class="form-control" placeholder="e.g. Senior Software Engineer" value="<?= htmlspecialchars($profile['headline'] ?? '') ?>">
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Professional Summary</label>
                    <textarea name="summary" class="form-control" rows="4" placeholder="Briefly describe your experience and goals..."><?= htmlspecialchars($profile['summary'] ?? '') ?></textarea>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>Key Skills (Comma separated)</label>
                    <input type="text" name="skills" class="form-control" placeholder="PHP, MySQL, JavaScript, Project Management" value="<?= htmlspecialchars($profile['skills'] ?? '') ?>">
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Years of Experience</label>
                        <input type="number" name="years_experience" class="form-control" value="<?= $profile['years_experience'] ?? 0 ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>Education Level</label>
                        <select name="education_level" class="form-control">
                            <option value="Bachelors" <?= ($profile['education_level'] ?? '') == 'Bachelors' ? 'selected' : '' ?>>Bachelors</option>
                            <option value="Masters" <?= ($profile['education_level'] ?? '') == 'Masters' ? 'selected' : '' ?>>Masters</option>
                            <option value="PhD" <?= ($profile['education_level'] ?? '') == 'PhD' ? 'selected' : '' ?>>PhD</option>
                            <option value="Diploma" <?= ($profile['education_level'] ?? '') == 'Diploma' ? 'selected' : '' ?>>Diploma</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Expected Salary ($)</label>
                        <input type="number" name="expected_salary" class="form-control" value="<?= $profile['expected_salary'] ?? '' ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>Preferred Location</label>
                        <input type="text" name="preferred_location" class="form-control" value="<?= htmlspecialchars($profile['preferred_location'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px; padding: 15px; border: 1px dashed #ccc; border-radius: 4px;">
                    <label>Upload Resume (PDF only)</label>
                    <input type="file" name="resume" accept=".pdf" style="display: block; margin-top: 10px;">
                    <?php if (!empty($profile['resume_path'])): ?>
                        <p style="margin-top: 10px; font-size: 13px; color: #666;">
                            Current Resume: <a href="../../public/uploads/resumes/<?= $profile['resume_path'] ?>" target="_blank" style="color: #e8491d;">View Uploaded File</a>
                        </p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-primary">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>