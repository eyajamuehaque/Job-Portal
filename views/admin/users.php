<?php
/**
 * views/admin/users.php
 * Interface for Admin to manage platform users.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'toggle_status') {
        $controller->toggleUserStatus($id);
    } elseif ($_GET['action'] == 'toggle_verify') {
        $controller->toggleUserVerification($id);
    } elseif ($_GET['action'] == 'reject_verify' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $reason = $_POST['reason'] ?? '';
        $controller->rejectUserVerification($id, $reason);
    }
}

$roleFilter = $_GET['role'] ?? null;
$users = $controller->getUsers($roleFilter);

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Manage Users</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee;">
        <form action="users.php" method="GET" style="display: flex; gap: 15px; align-items: center;">
            <label style="font-weight: bold;">Filter by Role:</label>
            <select name="role" class="form-control" style="padding: 8px; border: 1px solid #ccc; border-radius: 4px; min-width: 200px;">
                <option value="">All Roles</option>
                <option value="seeker" <?= $roleFilter == 'seeker' ? 'selected' : '' ?>>Job Seekers</option>
                <option value="employer" <?= $roleFilter == 'employer' ? 'selected' : '' ?>>Employers</option>
                <option value="recruiter" <?= $roleFilter == 'recruiter' ? 'selected' : '' ?>>Recruiters</option>
            </select>
            <button type="submit" class="btn-primary" style="padding: 8px 15px; cursor: pointer;">Filter</button>
            <?php if ($roleFilter): ?>
                <a href="users.php" style="margin-left: 10px; color: #666; text-decoration: none;">Clear Filter</a>
            <?php endif; ?>
        </form>
    </div>

    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #ddd; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="background-color: #f8f9fa; text-align: left;">
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">ID</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">User Info</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">Role</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">Registered Date</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">Status</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">Verified</th>
                    <th style="padding: 12px; border-bottom: 1px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 20px;">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $user['id'] ?></td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <strong><?= htmlspecialchars($user['name']) ?></strong><br>
                                <small style="color: #666;"><?= htmlspecialchars($user['email']) ?></small>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd; text-transform: capitalize;">
                                <?= htmlspecialchars($user['role']) ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <?php if ($user['is_active']): ?>
                                    <span style="background: #d4edda; color: #155724; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Active</span>
                                <?php else: ?>
                                    <span style="background: #f8d7da; color: #721c24; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Banned</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <?php if ($user['is_verified']): ?>
                                    <span style="color: #007bff; font-weight: bold;">✓ Yes</span>
                                <?php else: ?>
                                    <span style="color: #6c757d;">No</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                <a href="users.php?action=toggle_status&id=<?= $user['id'] ?>" 
                                   style="text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; display: inline-block; margin-bottom: 5px; 
                                   <?= $user['is_active'] ? 'background: #dc3545; color: white;' : 'background: #28a745; color: white;' ?>"
                                   onclick="return confirm('Are you sure you want to change this user\'s access?');">
                                    <?= $user['is_active'] ? 'Ban User' : 'Unban User' ?>
                                </a>
                                
                                <br>
                                
                                <a href="users.php?action=toggle_verify&id=<?= $user['id'] ?>" 
                                   style="text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; display: inline-block; background: #17a2b8; color: white;"
                                   onclick="return confirm('Change verification status?');">
                                    <?= $user['is_verified'] ? 'Unverify' : 'Verify Account' ?>
                                </a>
                                
                                <?php if (!$user['is_verified']): ?>
                                    <button onclick="rejectUser(<?= $user['id'] ?>)" 
                                            style="border: none; cursor: pointer; padding: 5px 10px; border-radius: 4px; font-size: 12px; display: inline-block; background: #ffc107; color: #333; margin-top: 5px;">
                                        Reject
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function rejectUser(id) {
    const reason = prompt("Enter the reason for rejection:");
    if (reason !== null && reason.trim() !== "") {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'users.php?action=reject_verify&id=' + id;
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        
        form.appendChild(reasonInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../partials/footer.php'; ?>
