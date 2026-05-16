<?php
/**
 * views/recruiter/outreach_history.php
 * Shows all outreach messages sent by recruiter with read/response status.
 */

require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$recruiterId = Session::get('user_id');

$sql = "SELECT ro.*, 
        u.name AS seeker_name,
        u.email AS seeker_email,
        j.title AS job_title
        FROM recruiter_outreach ro
        JOIN users u ON ro.seeker_id = u.id
        JOIN jobs j ON ro.job_id = j.id
        WHERE ro.recruiter_id = ?
        ORDER BY ro.sent_at DESC";

$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $recruiterId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$outreaches = mysqli_fetch_all($result, MYSQLI_ASSOC);

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Outreach History</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">← Back to Dashboard</a>
    </div>

    <div class="job-list">
        <h2>All Outreach Messages</h2>
        <p style="color: #666; margin-bottom: 20px;">
            View all job opportunity messages sent to seekers and track their current outreach status.
        </p>

        <?php if (empty($outreaches)): ?>
            <div class="job-card" style="text-align: center; color: #777;">
                <p>No outreach messages sent yet.</p>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #000000ff; color: white; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Candidate</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Job Opportunity</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Message</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Status</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Sent Date</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($outreaches as $outreach): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;">
                                <strong><?= htmlspecialchars($outreach['seeker_name']) ?></strong><br>
                                <small style="color: #666;"><?= htmlspecialchars($outreach['seeker_email']) ?></small>
                            </td>

                            <td style="padding: 12px;">
                                <?= htmlspecialchars($outreach['job_title']) ?>
                            </td>

                            <td style="padding: 12px; max-width: 320px;">
                                <?= nl2br(htmlspecialchars($outreach['message'])) ?>
                            </td>

                            <td style="padding: 12px;">
                                <?php
                                    $statusColor = '#cfe2ff';

                                    if ($outreach['status'] == 'read') {
                                        $statusColor = '#fff3cd';
                                    } elseif ($outreach['status'] == 'responded') {
                                        $statusColor = '#d1e7dd';
                                    }
                                ?>

                                <span class="badge" style="background: <?= $statusColor ?>; padding: 5px 10px; border-radius: 4px; font-size: 12px; color: #333;">
                                    <?= strtoupper(htmlspecialchars($outreach['status'])) ?>
                                </span>
                            </td>

                            <td style="padding: 12px;">
                                <?= date('M d, Y', strtotime($outreach['sent_at'])) ?>
                            </td>

                            <td style="padding: 12px;">
                                <a href="messages.php?contact_id=<?= $outreach['seeker_id'] ?>" 
                                   style="color: #e8491d; text-decoration: none; font-weight: bold;">
                                    View Chat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>