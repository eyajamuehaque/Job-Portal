<?php
// public/apply.php
require_once '../app/core/Database.php';
require_once '../app/core/Session.php';
require_once '../app/controllers/SeekerController.php';

Session::init();

// 1. Security Check: Only logged-in Seekers can apply
if (Session::get('role') !== 'seeker') {
    header("Location: ../views/auth/login.php");
    exit();
}

$job_id = $_GET['id'] ?? null;
if (!$job_id && !isset($_POST['job_id'])) {
    header("Location: index.php");
    exit();
}

$controller = new SeekerController();
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result = $controller->apply();
    if ($result && is_string($result)) {
        $error_message = $result;
    }
    $job_id = $_POST['job_id'];
}

// Fetch job details for display
$database = new Database();
require_once '../app/models/Job.php';
$jobModel = new Job($database->conn);
$job = $jobModel->getById($job_id);

if (!$job) {
    die("Job not found.");
}

// Check if already applied
require_once '../app/models/Application.php';
$applicationModel = new Application($database->conn);
$already_applied = $applicationModel->hasAlreadyApplied($job_id, Session::get('user_id'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?= htmlspecialchars($job['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: #f4f7f6; font-family: Arial, sans-serif; }
        .apply-container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .alert-warning { background-color: #fff3cd; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="apply-container">
        <h2>Apply for: <?= htmlspecialchars($job['title']) ?></h2>
        <p style="color: #666; margin-bottom: 30px;"><?= htmlspecialchars($job['employer_name']) ?> - <?= htmlspecialchars($job['location']) ?></p>

        <?php if ($error_message): ?>
            <div class="alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($already_applied): ?>
            <div class="alert-warning">
                <strong>Notice:</strong> You have already submitted an application for this position.
            </div>
            <div style="margin-top: 30px;">
                <a href="../views/seeker/dashboard.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px;">View My Applications</a>
                <a href="job_details.php?id=<?= $job_id ?>" style="margin-left: 15px; color: #777;">Back to Job</a>
            </div>
        <?php else: ?>
            <form action="apply.php" method="POST">
                <input type="hidden" name="job_id" value="<?= $job_id ?>">
                
                <div class="form-group">
                    <label for="cover_letter">Cover Letter</label>
                    <textarea name="cover_letter" id="cover_letter" rows="8" class="form-control" placeholder="Write why you are a great fit for this role..." required></textarea>
                </div>

                <div class="form-group" style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px dashed #ccc;">
                    <p style="margin: 0 0 10px 0; font-weight: bold; color: #333;">Resume</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Your current resume from your profile will be automatically attached to this application.</p>
                    <a href="../views/seeker/profile.php" style="font-size: 13px; color: #007bff; display: inline-block; margin-top: 10px;">Update Resume in Profile</a>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 15px;">
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">Submit Application</button>
                    <a href="job_details.php?id=<?= $job_id ?>" style="display: inline-block; padding: 12px 30px; color: #666; text-decoration: none; border: 1px solid #ccc; border-radius: 4px;">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>