<?php
// public/job_details.php

// 1. Path Adjustments: Use ../ to reach the app folder from public
require_once '../app/core/Database.php';
require_once '../app/core/Session.php';

Session::init();

include '../views/partials/header.php'; 

$job_id = $_GET['id'] ?? null;

// Redirect if no ID is provided
if (!$job_id) {
    header("Location: ../index.php"); 
    exit();
}

$database = new Database();
$db = $database->conn;

// 2. Optimized SQL: This handles both direct Employer posts and Recruiter client posts
$sql = "SELECT j.*, 
        COALESCE(e.name, r.name) AS company_display_name,
        COALESCE(e.email, r.email) AS company_email
        FROM jobs j
        LEFT JOIN users e ON j.employer_id = e.id 
        LEFT JOIN users r ON j.recruiter_id = r.id
        WHERE j.id = ?";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $job_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$job = mysqli_fetch_assoc($result);

// Error handling if job ID doesn't exist in DB
if (!$job) {
    die("<div class='container'><h2>Job not found.</h2><a href='../index.php'>Return Home</a></div>");
}

require_once '../app/models/SavedJob.php';
$savedJobModel = new SavedJob($db);
$isBookmarked = false;
if (Session::get('role') === 'seeker') {
    $isBookmarked = $savedJobModel->isSaved(Session::get('user_id'), $job_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> | Job Portal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="background-color: #f4f7f6;">

    <div class="container" style="max-width: 900px; margin: 50px auto; padding: 20px;">
        <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            
            <div style="border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px;">
                <span style="background: #e8491d; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                    <?= htmlspecialchars($job['job_type']) ?>
                </span>
                
                <h1 style="font-size: 32px; margin: 15px 0 5px 0; color: #35424a;">
                    <?= htmlspecialchars($job['title']) ?>
                </h1>
                
                <p style="font-size: 18px; color: #777;">
                    <strong><?= htmlspecialchars($job['company_display_name']) ?></strong> 
                    <span style="margin: 0 10px;">•</span> 
                    <?= htmlspecialchars($job['location']) ?>
                </p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; background: #fafafa; padding: 20px; border-radius: 8px;">
                <div>
                    <p style="margin: 0; color: #888; font-size: 14px;">Salary Range</p>
                    <p style="margin: 5px 0; font-weight: bold; color: #2ecc71;">
                        <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?> BDT
                    </p>
                </div>
                <div>
                    <p style="margin: 0; color: #888; font-size: 14px;">Date Posted</p>
                    <p style="margin: 5px 0; font-weight: bold;">
                        <?= date('M d, Y', strtotime($job['created_at'])) ?>
                    </p>
                </div>
            </div>

            <div style="line-height: 1.8; color: #444; font-size: 16px;">
                <h3 style="color: #35424a; border-left: 4px solid #e8491d; padding-left: 15px;">Job Description</h3>
                <p style="white-space: pre-line; margin-top: 15px;">
                    <?= htmlspecialchars($job['description']) ?>
                </p>
            </div>

            <div style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
                
                <a href="../public/index.php" style="text-decoration: none; color: #777; font-weight: 500;">
                    ← Back to Listings
                </a>

                <?php if (Session::get('role') === 'seeker'): ?>
                    <div style="display: flex; gap: 15px;">
                        <button onclick="toggleBookmark(<?= $job['id'] ?>)" id="bookmarkBtn" class="btn-primary" style="padding: 15px 30px; border: none; cursor: pointer; border-radius: 6px; font-weight: bold; background-color: <?= $isBookmarked ? '#dc3545' : '#17a2b8' ?>; color: white;">
                            <?= $isBookmarked ? 'Remove Bookmark' : 'Bookmark Job' ?>
                        </button>
                        <a href="apply.php?id=<?= $job['id'] ?>" class="btn-primary" style="padding: 15px 40px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                            Apply Now
                        </a>
                    </div>
                <?php elseif (!Session::get('role')): ?>
                    <a href="/JOB-PORTAL/views/auth/login.php" style="color: #e8491d; font-weight: bold; text-decoration: none;">
                        Login as Seeker to Apply
                    </a>
                <?php else: ?>
                    <span style="color: #999; font-style: italic;">Viewing as <?= Session::get('role') ?></span>
                <?php endif; ?>
                
            </div>
        </div>
    </div>

<script>
function toggleBookmark(jobId) {
    fetch('../api/toggle-bookmark.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('bookmarkBtn');
            if (data.status === 'saved') {
                btn.innerText = 'Remove Bookmark';
                btn.style.backgroundColor = '#dc3545';
            } else {
                btn.innerText = 'Bookmark Job';
                btn.style.backgroundColor = '#17a2b8';
            }
        } else {
            alert('An error occurred.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}
</script>

</body>
</html>