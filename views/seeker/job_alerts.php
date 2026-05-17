<?php
/**
 * views/seeker/job_alerts.php
 */
require_once '../../app/controllers/SeekerController.php';
require_once '../../app/models/Job.php';

$controller = new SeekerController();

// Handle creation and deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create') {
    $controller->createAlert();
}
if (isset($_GET['delete'])) {
    $controller->deleteAlert(intval($_GET['delete']));
}

$alerts = $controller->getAlerts();
$matchingJobs = $controller->getMatchingAlertJobs();


$database = new Database();
$jobModel = new Job($database->conn);
$categories = $jobModel->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Alerts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .alert-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; cursor: pointer; border: none; }
        .btn-danger { background: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .message { padding: 10px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="job_alerts.php">Job Alerts</a>
    </div>

    <h2>Job Alerts</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="message"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
        <h3>Create New Alert</h3>
        <form method="POST" action="job_alerts.php">
            <input type="hidden" name="action" value="create">
            
            <div class="form-group">
                <label>Keyword</label>
                <input type="text" name="keyword" placeholder="e.g. Developer, Remote">
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">Any Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="e.g. New York">
            </div>
            
            <div class="form-group">
                <label>Job Type</label>
                <select name="job_type">
                    <option value="">Any Type</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Save Alert</button>
        </form>
    </div>

    <h3>Active Alerts</h3>
    <div id="alerts-list">
        <?php if (empty($alerts)): ?>
            <p>You don't have any active job alerts.</p>
        <?php else: ?>
            <?php foreach ($alerts as $alert): ?>
                <div class="alert-card">
                    <p>
                        <strong>Keyword:</strong> <?= htmlspecialchars($alert['keyword'] ?: 'Any') ?> |
                        <strong>Category:</strong> <?= htmlspecialchars($alert['category_name'] ?: 'Any') ?> |
                        <strong>Location:</strong> <?= htmlspecialchars($alert['location'] ?: 'Any') ?> |
                        <strong>Type:</strong> <?= htmlspecialchars($alert['job_type'] ?: 'Any') ?>
                    </p>
                    <a href="job_alerts.php?delete=<?= $alert['id'] ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this alert?');">Delete</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h3 style="margin-top: 40px;">Jobs Matching Your Alerts</h3>
    <div id="matching-jobs-list">
        <?php if (empty($matchingJobs)): ?>
            <p>No jobs currently match your active alerts.</p>
        <?php else: ?>
            <?php foreach ($matchingJobs as $job): ?>
                <div class="alert-card" style="border-left: 4px solid #28a745;">
                    <h4 style="margin-top: 0; margin-bottom: 5px;"><?= htmlspecialchars($job['title']) ?></h4>
                    <p style="margin-top: 0;">
                        <strong>Company:</strong> <?= htmlspecialchars($job['company_name']) ?> |
                        <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?> |
                        <strong>Type:</strong> <span style="color: #e8491d;"><?= htmlspecialchars($job['job_type']) ?></span>
                    </p>
                    <a href="../../public/job_details.php?id=<?= $job['id'] ?>" class="btn">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
