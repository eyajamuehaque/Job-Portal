<?php
/**
 * views/employer/shortlisted.php
 * Unified view of all shortlisted/interview candidates across all jobs.
 */
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';

Session::init();
Session::checkRole('employer');

$employerId = Session::get('user_id');
$db = (new Database())->conn;

$sql = "SELECT a.*, u.name as applicant_name, u.email as applicant_email, j.title as job_title, sp.headline, sp.years_experience
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON a.seeker_id = u.id
        LEFT JOIN seeker_profiles sp ON a.seeker_id = sp.user_id
        WHERE j.employer_id = ? AND a.status IN ('shortlisted', 'interview')
        ORDER BY a.applied_at DESC";

$stmt = mysqli_prepare($db, $sql);
$shortlisted = [];
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $employerId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $shortlisted = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shortlisted Candidates</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-shortlisted { background: #d1e7dd; color: #0f5132; }
        .badge-interview { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="analytics.php">Analytics</a>
        <a href="shortlisted.php">Shortlisted Candidates</a>
    </div>

    <h2>Shortlisted Candidates Pipeline</h2>
    <p>A unified view of your most promising candidates across all active jobs.</p>

    <?php if (empty($shortlisted)): ?>
        <p>No shortlisted or interviewing candidates found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job Title</th>
                    <th>Status</th>
                    <th>Applied On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shortlisted as $row): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($row['applicant_name']) ?></strong><br>
                            <small><?= htmlspecialchars($row['applicant_email']) ?></small>
                            <?php if ($row['headline']): ?>
                                <br><small style="color:#666;"><?= htmlspecialchars($row['headline']) ?> (<?= $row['years_experience'] ?> yrs exp)</small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['job_title']) ?></td>
                        <td>
                            <span class="badge <?= $row['status'] == 'shortlisted' ? 'badge-shortlisted' : 'badge-interview' ?>">
                                <?= strtoupper($row['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['applied_at'])) ?></td>
                        <td>
                            <a href="view_applicants.php?job_id=<?= $row['job_id'] ?>" style="color: #007bff; text-decoration: none; margin-right: 10px;">Go to Job</a>
                            <a href="messages.php?contact_id=<?= $row['seeker_id'] ?>" style="color: #e8491d; text-decoration: none;">Message</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
