<?php
/**
 * views/employer/analytics.php
 */
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';
require_once '../../app/models/Analytics.php';
require_once '../../app/models/Job.php';

Session::init();
Session::checkRole('employer');

$employerId = Session::get('user_id');
$db = (new Database())->conn;
$analyticsModel = new Analytics($db);
$jobModel = new Job($db);

$overview = $analyticsModel->getEmployerOverview($employerId);
$jobs = $jobModel->getByEmployer($employerId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiring Analytics</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; text-align: center; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #6c757d; text-transform: uppercase; }
        .stat-card .num { font-size: 32px; font-weight: bold; color: #35424a; margin: 0; }
        
        .funnel { display: flex; background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; margin-bottom: 30px; }
        .funnel-stage { flex: 1; padding: 15px; text-align: center; border-right: 1px solid #ddd; }
        .funnel-stage:last-child { border-right: none; }
        .funnel-stage h4 { margin: 0 0 5px 0; font-size: 14px; color: #555; }
        .funnel-stage p { margin: 0; font-size: 24px; font-weight: bold; color: #e8491d; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .conversion { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="analytics.php">Analytics</a>
        <a href="shortlisted.php">Shortlisted Candidates</a>
    </div>

    <h2>Overall Hiring Analytics</h2>
    
    <div class="stat-grid">
        <div class="stat-card">
            <h3>Total Jobs Posted</h3>
            <p class="num"><?= $overview['total_jobs'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Active Jobs</h3>
            <p class="num"><?= $overview['active_jobs'] ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Applications</h3>
            <p class="num"><?= $overview['total_applications'] ?></p>
        </div>
    </div>

    <h3>Overall Application Funnel</h3>
    <div class="funnel">
        <div class="funnel-stage">
            <h4>Total</h4>
            <p><?= $overview['total_applications'] ?></p>
        </div>
        <div class="funnel-stage">
            <h4>Reviewed</h4>
            <p><?= $overview['status_breakdown']['reviewed'] + $overview['status_breakdown']['shortlisted'] + $overview['status_breakdown']['interview'] + $overview['status_breakdown']['rejected'] ?></p>
        </div>
        <div class="funnel-stage">
            <h4>Shortlisted</h4>
            <p><?= $overview['status_breakdown']['shortlisted'] + $overview['status_breakdown']['interview'] ?></p>
        </div>
        <div class="funnel-stage">
            <h4>Interview</h4>
            <p><?= $overview['status_breakdown']['interview'] ?></p>
        </div>
    </div>

    <h3>Per Job Performance</h3>
    <table>
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Status</th>
                <th>Applications</th>
                <th>Shortlisted</th>
                <th>Interview</th>
                <th>Conversion Rate (App to Shortlist)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jobs as $job): ?>
                <?php 
                    $metrics = $analyticsModel->getJobMetrics($job['id']); 
                    $shortlistedCount = $metrics['shortlisted'] + $metrics['interview'];
                    $conversion = $metrics['total'] > 0 ? round(($shortlistedCount / $metrics['total']) * 100, 1) : 0;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                    <td><?= ucfirst($job['status']) ?></td>
                    <td><?= $metrics['total'] ?></td>
                    <td><?= $shortlistedCount ?></td>
                    <td><?= $metrics['interview'] ?></td>
                    <td class="conversion"><?= $conversion ?>%</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
