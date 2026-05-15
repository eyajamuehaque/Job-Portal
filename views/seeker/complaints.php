<?php
/**
 * views/seeker/complaints.php
 */
require_once '../../app/controllers/SeekerController.php';

$controller = new SeekerController();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit') {
    $controller->submitComplaint();
}

$complaints = $controller->getMyComplaints();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .complaint-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 8px 12px; background: #dc3545; color: white; text-decoration: none; border-radius: 4px; display: inline-block; cursor: pointer; border: none; font-weight: bold; }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        .message { padding: 10px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px; }
        .error { padding: 10px; background-color: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 15px; }
        .badge-open { background: #ffc107; color: #333; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
        .badge-resolved { background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="complaints.php">Complaints</a>
    </div>

    <h2>File a Complaint</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="message"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #ffeeba;">
        <p style="margin: 0; color: #856404;">If you've encountered a misleading job posting, scam, or abusive conduct from an employer or recruiter, please report it here. The platform administrators will investigate.</p>
        
        <form method="POST" action="complaints.php" style="margin-top: 15px;">
            <input type="hidden" name="action" value="submit">
            
            <div class="form-group">
                <label>Subject ID (Optional - Job ID or Employer/Recruiter ID)</label>
                <input type="number" name="subject_id" placeholder="Leave blank if general">
            </div>
            
            <div class="form-group">
                <label>Detailed Description</label>
                <textarea name="description" rows="5" required placeholder="Describe the issue in detail. If reporting a job, please include the job title and company name."></textarea>
            </div>
            
            <button type="submit" class="btn">Submit Complaint to Admin</button>
        </form>
    </div>

    <h3>My Complaint History</h3>
    <div id="complaints-list">
        <?php if (empty($complaints)): ?>
            <p>You haven't submitted any complaints.</p>
        <?php else: ?>
            <?php foreach ($complaints as $c): ?>
                <div class="complaint-card">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>Date: <?= date('M d, Y', strtotime($c['created_at'])) ?></strong>
                        <span class="<?= $c['status'] == 'open' ? 'badge-open' : 'badge-resolved' ?>">
                            <?= strtoupper($c['status']) ?>
                        </span>
                    </div>
                    
                    <?php if ($c['subject_name'] || $c['job_title']): ?>
                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                            <strong>Regarding:</strong> <?= htmlspecialchars($c['subject_name'] ?? $c['job_title']) ?> (ID: <?= $c['subject_id'] ?>)
                        </p>
                    <?php endif; ?>
                    
                    <p style="margin: 0; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                        <?= nl2br(htmlspecialchars($c['description'])) ?>
                    </p>
                    
                    <?php if ($c['admin_note']): ?>
                        <div style="margin-top: 10px; padding: 10px; border-left: 4px solid #28a745; background: #e8f5e9;">
                            <strong>Admin Resolution:</strong><br>
                            <?= nl2br(htmlspecialchars($c['admin_note'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
