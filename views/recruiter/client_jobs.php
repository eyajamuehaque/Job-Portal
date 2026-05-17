<?php
// views/recruiter/client_jobs.php
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';

Session::init();

include '../partials/header.php';

if (Session::get('role') !== 'recruiter') {
    header("Location: /Job-Portal/views/auth/login.php");
    exit();
}

$database = new Database();
$db = $database->conn;
$recruiter_id = Session::get('user_id');

// Fetch jobs posted by THIS recruiter, including the client name
$sql = "SELECT jobs.*, recruiter_clients.client_name 
        FROM jobs 
        JOIN recruiter_clients ON jobs.employer_id = recruiter_clients.id 
        WHERE jobs.recruiter_id = ? 
        ORDER BY jobs.created_at DESC";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Managed Jobs - Recruiter</title>
    <link rel="stylesheet" href="/Job-Portal/public/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="job-list">
            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>

            <h2>Jobs Posted for Clients</h2>
            
            <table class="job-listings-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #35424a; color: white;">
                        <th style="padding: 12px; text-align: left;">Job Title</th>
                        <th style="padding: 12px; text-align: left;">Client Company</th>
                        <th style="padding: 12px; text-align: left;">Location</th>
                        <th style="padding: 12px; text-align: left;">Type</th>
                        <th style="padding: 12px; text-align: left;">Posted Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($job = mysqli_fetch_assoc($result)): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($job['client_name']) ?></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($job['location']) ?></td>
                                <td style="padding: 12px;"><span class="badge"><?= htmlspecialchars($job['job_type']) ?></span></td>
                                <td style="padding: 12px;"><?= date('M d, Y', strtotime($job['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center; color: #777;">No jobs posted yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px;">
                <a href="dashboard.php" style="text-decoration: none; color: #35424a; font-weight: bold;">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>