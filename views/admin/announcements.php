<?php
/**
 * views/admin/announcements.php
 * Post and manage platform-wide announcements.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['action'])) {
    $controller->createAnnouncement();
} elseif (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $controller->toggleAnnouncementStatus(intval($_GET['id']));
}

$announcements = $controller->getAnnouncements();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Platform Announcements</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #6c757d;">Back to Dashboard</a>
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

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        
        <!-- Create Announcement Form -->
        <div style="background: white; border: 1px solid #ddd; padding: 25px; border-radius: 8px;">
            <h3 style="margin-top: 0;">New Announcement</h3>
            <form action="announcements.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Title</label>
                    <input type="text" name="title" required class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;" placeholder="e.g. Scheduled Maintenance">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Target Audience</label>
                    <select name="target_role" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="all">All Users</option>
                        <option value="seeker">Job Seekers Only</option>
                        <option value="employer">Employers Only</option>
                        <option value="recruiter">Recruiters Only</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 5px;">Message</label>
                    <textarea name="message" rows="5" required class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; font-size: 16px;">Post Announcement</button>
            </form>
        </div>

        <!-- Announcement History -->
        <div style="background: white; border: 1px solid #ddd; padding: 25px; border-radius: 8px;">
            <h3 style="margin-top: 0;">Past Announcements</h3>
            
            <?php if (empty($announcements)): ?>
                <p style="color: #666;">No announcements have been posted yet.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php foreach ($announcements as $ann): ?>
                        <div style="border: 1px solid #eee; padding: 15px; border-radius: 4px; border-left: 4px solid <?= $ann['is_active'] ? '#17a2b8' : '#ccc' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <h4 style="margin: 0 0 5px 0;"><?= htmlspecialchars($ann['title']) ?></h4>
                                    <span style="font-size: 12px; color: #888; background: #f4f4f4; padding: 2px 6px; border-radius: 4px;">Audience: <?= ucfirst($ann['target_role']) ?></span>
                                    <span style="font-size: 12px; color: #888; margin-left: 10px;"><?= date('M d, Y h:i A', strtotime($ann['created_at'])) ?></span>
                                </div>
                                
                                <a href="announcements.php?action=toggle&id=<?= $ann['id'] ?>" class="btn-primary" style="font-size: 12px; padding: 5px 10px; background: <?= $ann['is_active'] ? '#dc3545' : '#28a745' ?>;">
                                    <?= $ann['is_active'] ? 'Deactivate' : 'Reactivate' ?>
                                </a>
                            </div>
                            <p style="margin: 10px 0 0 0; font-size: 14px; color: #444; white-space: pre-line;"><?= htmlspecialchars($ann['message']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
