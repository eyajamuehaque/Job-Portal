<?php
/**
 * views/admin/verify_users.php
 * Admin interface to approve or reject Employer/Recruiter accounts.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$db = (new Database())->conn;
$sql = "SELECT id, name, email, role, created_at FROM users WHERE is_verified = 0 AND (role = 'employer' OR role = 'recruiter')";
$pendingUsers = mysqli_query($db, $sql);

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <h1>User Verifications</h1>
    <p>Review and approve corporate accounts to maintain platform quality.</p>

    <div class="job-list">
        <?php if (mysqli_num_rows($pendingUsers) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Requested Role</th>
                        <th>Signup Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($pendingUsers)): ?>
                        <tr id="user-row-<?= $user['id'] ?>">
                            <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge" style="background: #eee;"><?= strtoupper($user['role']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <button onclick="verifyUser(<?= $user['id'] ?>, 1)" class="btn-primary" style="padding: 5px 12px; font-size: 13px; background: #28a745;">Approve</button>
                                <button onclick="verifyUser(<?= $user['id'] ?>, 0)" class="btn-primary" style="padding: 5px 12px; font-size: 13px; background: #dc3545; margin-left: 5px;">Reject</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="job-card">
                <p>No pending verifications at this time.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function verifyUser(userId, status) {
    if(!confirm(status === 1 ? "Approve this user?" : "Reject this user?")) return;

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../../api/admin/verify-user.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let res = JSON.parse(this.responseText);
            if(res.success) {
                document.getElementById('user-row-' + userId).remove();
            } else {
                alert("Operation failed.");
            }
        }
    };
    xhr.send("user_id=" + userId + "&is_verified=" + status);
}
</script>

<?php include '../partials/footer.php'; ?>