<?php
/**
 * views/admin/complaints.php
 * Interface for Admin to review and resolve user complaints/disputes.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'resolve') {
    $controller->resolveComplaint();
}

$complaints = $controller->getAllComplaints();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Dispute Resolution</h1>
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

    <p style="color: #666; margin-bottom: 30px;">Review complaints submitted by platform users regarding abusive conduct, fake jobs, or scams.</p>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php if (empty($complaints)): ?>
            <div style="background: #e9ecef; padding: 30px; text-align: center; border-radius: 8px;">
                <h3 style="color: #555; margin: 0;">No Complaints Found</h3>
                <p style="color: #888; margin-top: 5px;">The platform is peaceful right now.</p>
            </div>
        <?php else: ?>
            <?php foreach ($complaints as $c): ?>
                <div style="border: 1px solid <?= $c['status'] == 'open' ? '#dc3545' : '#ddd' ?>; border-radius: 8px; background: white; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    
                    <!-- Header -->
                    <div style="background: <?= $c['status'] == 'open' ? '#fff5f5' : '#f8f9fa' ?>; padding: 15px 20px; border-bottom: 1px solid <?= $c['status'] == 'open' ? '#f5c6cb' : '#eee' ?>; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong>Complaint #<?= $c['id'] ?></strong>
                            <span style="color: #888; font-size: 14px; margin-left: 10px;">Submitted: <?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></span>
                        </div>
                        <?php if ($c['status'] == 'open'): ?>
                            <span style="background: #dc3545; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">REQUIRES ACTION</span>
                        <?php else: ?>
                            <span style="background: #28a745; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">RESOLVED</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Body -->
                    <div style="padding: 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
                        <!-- Left Column: Parties involved -->
                        <div style="border-right: 1px solid #eee; padding-right: 20px;">
                            <h4 style="margin: 0 0 10px 0; color: #35424a; font-size: 14px; text-transform: uppercase;">Submitted By</h4>
                            <p style="margin: 0 0 20px 0;">
                                <strong><?= htmlspecialchars($c['submitter_name']) ?></strong><br>
                                <span style="font-size: 14px; color: #666; text-transform: capitalize;">Role: <?= htmlspecialchars($c['submitter_role']) ?></span><br>
                                <span style="font-size: 12px; color: #aaa;">ID: <?= $c['submitter_id'] ?></span>
                            </p>
                            
                            <h4 style="margin: 0 0 10px 0; color: #35424a; font-size: 14px; text-transform: uppercase;">Regarding Subject</h4>
                            <?php if ($c['subject_id']): ?>
                                <p style="margin: 0;">
                                    <strong><?= htmlspecialchars($c['subject_name'] ?? 'Unknown/Deleted') ?></strong><br>
                                    <span style="font-size: 14px; color: #666; text-transform: capitalize;">Role: <?= htmlspecialchars($c['subject_role'] ?? 'Unknown') ?></span><br>
                                    <span style="font-size: 12px; color: #aaa;">ID: <?= $c['subject_id'] ?></span>
                                </p>
                            <?php else: ?>
                                <p style="margin: 0; color: #888; font-style: italic;">General Platform Issue</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Right Column: Details & Resolution -->
                        <div>
                            <h4 style="margin: 0 0 10px 0; color: #35424a; font-size: 14px; text-transform: uppercase;">Complaint Description</h4>
                            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; border: 1px solid #eee; margin-bottom: 20px; white-space: pre-line; line-height: 1.5;">
                                <?= htmlspecialchars($c['description']) ?>
                            </div>
                            
                            <?php if ($c['status'] == 'open'): ?>
                                <h4 style="margin: 0 0 10px 0; color: #dc3545; font-size: 14px; text-transform: uppercase;">Resolve Dispute</h4>
                                <form action="complaints.php" method="POST" style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                                    <input type="hidden" name="action" value="resolve">
                                    <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                                    <label style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Admin Note / Actions Taken</label>
                                    <textarea name="admin_note" rows="3" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px;" placeholder="e.g. Account banned. Fake job removed."></textarea>
                                    <button type="submit" style="background: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Mark as Resolved</button>
                                    
                                    <?php if ($c['subject_id']): ?>
                                        <a href="users.php" style="margin-left: 15px; font-size: 14px; color: #dc3545; text-decoration: underline;">Go ban user?</a>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <h4 style="margin: 0 0 10px 0; color: #28a745; font-size: 14px; text-transform: uppercase;">Resolution Note</h4>
                                <div style="background: #e8f5e9; border-left: 4px solid #28a745; padding: 15px; border-radius: 0 4px 4px 0; white-space: pre-line; line-height: 1.5; color: #155724;">
                                    <?= htmlspecialchars($c['admin_note']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
