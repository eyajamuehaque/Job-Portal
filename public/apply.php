<?php
// public/apply.php
require_once '../app/core/Database.php';
require_once '../app/core/Session.php';

Session::init();

// 1. Security Check: Only logged-in Seekers can apply
if (Session::get('role') !== 'seeker') {
    header("Location: ../views/auth/login.php");
    exit();
}

$job_id = $_GET['id'] ?? null;
$seeker_id = Session::get('user_id');

if (!$job_id) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->conn;

// 2. Check if the user has already applied for this job
$check_sql = "SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?";
$check_stmt = mysqli_prepare($db, $check_sql);
mysqli_stmt_bind_param($check_stmt, "ii", $job_id, $seeker_id);
mysqli_stmt_execute($check_stmt);
$already_applied = mysqli_stmt_get_result($check_stmt)->num_rows > 0;

if ($already_applied) {
    $message = "You have already applied for this position.";
    $type = "warning";
} else {
    // 3. Insert the application
    $apply_sql = "INSERT INTO applications (job_id, seeker_id, status) VALUES (?, ?, 'pending')";
    $apply_stmt = mysqli_prepare($db, $apply_sql);
    mysqli_stmt_bind_param($apply_stmt, "ii", $job_id, $seeker_id);
    
    if (mysqli_stmt_execute($apply_stmt)) {
        $message = "Application submitted successfully!";
        $type = "success";
    } else {
        $message = "Something went wrong. Please try again.";
        $type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Status</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 100px; text-align: center;">
        <div class="job-card" style="display: inline-block; padding: 40px; max-width: 500px;">
            <?php if ($type === 'success'): ?>
                <h2 style="color: #2ecc71;">✔ Success!</h2>
            <?php else: ?>
                <h2 style="color: #e8491d;">Notice</h2>
            <?php endif; ?>
            
            <p style="font-size: 18px; margin: 20px 0;"><?= $message ?></p>
            
            <a href="../public/index.php" class="btn-primary" style="text-decoration: none; padding: 10px 20px;">Return to Job Board</a>
            <br><br>
            <a href="job_details.php?id=<?= $job_id ?>" style="color: #777;">Back to Job Details</a>
        </div>
    </div>
</body>
</html>