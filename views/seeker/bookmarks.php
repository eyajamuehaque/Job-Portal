<?php
/**
 * views/seeker/bookmarks.php
 */
require_once '../../app/controllers/SeekerController.php';
$controller = new SeekerController();
$bookmarks = $controller->getBookmarks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookmarked Jobs</title>
    <!-- Basic styling -->
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .job-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .job-card h3 { margin-top: 0; }
        .job-card p { margin: 5px 0; color: #555; }
        .btn { padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-danger { background: #dc3545; border: none; cursor: pointer; color: white; padding: 8px 12px; border-radius: 4px; }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="bookmarks.php">My Bookmarks</a>
    </div>

    <h2>My Bookmarked Jobs</h2>

    <div id="bookmarks-list">
        <?php if (empty($bookmarks)): ?>
            <p>You haven't bookmarked any jobs yet.</p>
        <?php else: ?>
            <?php foreach ($bookmarks as $job): ?>
                <div class="job-card" id="job-card-<?= $job['job_id'] ?>">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?> (<?= htmlspecialchars($job['job_type']) ?>)</p>
                    <p><strong>Salary:</strong> $<?= htmlspecialchars($job['salary_min']) ?> - $<?= htmlspecialchars($job['salary_max']) ?></p>
                    
                    <div style="margin-top: 10px;">
                        <a href="../../public/job_details.php?id=<?= $job['job_id'] ?>" class="btn">View Details</a>
                        <button onclick="toggleBookmark(<?= $job['job_id'] ?>)" class="btn-danger">Remove Bookmark</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleBookmark(jobId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/toggle-bookmark.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success && data.status === 'removed') {
                    // Remove the card from the UI
                    document.getElementById('job-card-' + jobId).remove();
                    
                    // Show empty message if no more cards
                    if (document.querySelectorAll('.job-card').length === 0) {
                        document.getElementById('bookmarks-list').innerHTML = '<p>You haven\'t bookmarked any jobs yet.</p>';
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while removing the bookmark.');
            }
        } else {
            console.error('Error:', xhr.statusText);
            alert('An error occurred while removing the bookmark.');
        }
    };
    
    xhr.onerror = function() {
        console.error('Error: Network request failed');
        alert('An error occurred while removing the bookmark.');
    };
    
    xhr.send(JSON.stringify({ job_id: jobId }));
}
</script>

</body>
</html>
