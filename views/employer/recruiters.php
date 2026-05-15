<?php
/**
 * views/employer/recruiters.php
 * Displays recruiters who are authorized to post jobs on behalf of this company.
 */
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';

Session::init();
Session::checkRole('employer');

$employerId = Session::get('user_id');
$db = (new Database())->conn;

$sql = "SELECT rc.*, u.name as recruiter_name, u.email as recruiter_email, rp.agency_name 
        FROM recruiter_clients rc
        JOIN users u ON rc.recruiter_id = u.id
        LEFT JOIN recruiter_profiles rp ON u.id = rp.user_id
        WHERE rc.employer_id = ?
        ORDER BY rc.added_at DESC";

$stmt = mysqli_prepare($db, $sql);
$recruiters = [];
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $employerId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $recruiters = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associated Recruiters</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        
        .recruiter-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .recruiter-info h3 { margin: 0 0 5px 0; color: #35424a; }
        .recruiter-info p { margin: 0; color: #666; font-size: 14px; }
        .btn-msg { background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="recruiters.php">Associated Recruiters</a>
    </div>

    <h2>Associated Recruiters</h2>
    <p>These recruitment agencies are authorized to post jobs and source candidates on your behalf.</p>

    <?php if (empty($recruiters)): ?>
        <div style="background: #e9ecef; padding: 20px; text-align: center; border-radius: 5px;">
            <p style="margin:0;">You are currently not partnered with any recruiters on this platform.</p>
        </div>
    <?php else: ?>
        <?php foreach ($recruiters as $r): ?>
            <div class="recruiter-card">
                <div class="recruiter-info">
                    <h3><?= htmlspecialchars($r['agency_name'] ?? $r['recruiter_name']) ?></h3>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($r['recruiter_name']) ?> (<?= htmlspecialchars($r['recruiter_email']) ?>)</p>
                    <p><strong>Partnership started:</strong> <?= date('M d, Y', strtotime($r['added_at'])) ?></p>
                </div>
                <div>
                    <a href="messages.php?contact_id=<?= $r['recruiter_id'] ?>" class="btn-msg">Send Message</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
