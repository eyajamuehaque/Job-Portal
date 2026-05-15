<?php
// views/seeker/application_details.php
require_once '../../app/core/Database.php';
require_once '../../app/core/Session.php';

Session::init();

// Security: Only Seekers allowed
if (Session::get('role') !== 'seeker') {
    header("Location: ../auth/login.php");
    exit();
}

$app_id = $_GET['id'] ?? null;
$seeker_id = Session::get('user_id');

if (!$app_id) {
    header("Location: dashboard.php");
    exit();
}

$db = (new Database())->conn;

// Fetch application info along with job and company details
// We use COALESCE to handle both direct Employer and Recruiter-client company names
$sql = "SELECT a.*, j.title, j.location, j.description as job_desc, j.job_type,
        COALESCE(u.name, 'Confidential Client (Agency)') as company_name
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        LEFT JOIN users u ON j.employer_id = u.id
        WHERE a.id = ? AND a.seeker_id = ?";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "ii", $app_id, $seeker_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$app = mysqli_fetch_assoc($result);

if (!$app) {
    die("Application not found or access denied.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Application Details | Job Portal</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body style="background: #f4f7f6;">

    <div class="container" style="margin-top: 50px; max-width: 800px;">
        <div class="job-card" style="padding: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
                <div>
                    <h1 style="margin: 0; color: #35424a;"><?= htmlspecialchars($app['title']) ?></h1>
                    <p style="color: #666; font-size: 18px; margin-top: 5px;">
                        at <strong><?= htmlspecialchars($app['company_name']) ?></strong>
                    </p>
                </div>
                <span class="status-badge <?= strtolower($app['status']) ?>" style="padding: 10px 20px; font-size: 14px;">
                    STATUS: <?= strtoupper($app['status']) ?>
                </span>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 30px;">

            <div style="margin-bottom: 30px;">
                <h4 style="color: #e8491d;">Application Info</h4>
                <p><strong>Applied On:</strong> <?= date('F d, Y', strtotime($app['applied_at'])) ?></p>
                <p><strong>Job Type:</strong> <?= ucwords(htmlspecialchars($app['job_type'])) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($app['location']) ?></p>
            </div>

            <div style="margin-bottom: 30px;">
                <h4 style="color: #e8491d;">Job Description</h4>
                <p style="line-height: 1.6; color: #444; white-space: pre-line;">
                    <?= htmlspecialchars($app['job_desc']) ?>
                </p>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <a href="dashboard.php" class="btn-secondary" style="text-decoration: none;">← Back to My Applications</a>
            </div>
        </div>
    </div>

</body>
</html>