<?php
/**
 * views/admin/dashboard.php
 * Main Admin Dashboard.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();
$stats = $controller->getDashboardStats();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Admin Dashboard</h1>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; align-items: center;">
            <a href="categories.php" class="btn-primary" style="background: #17a2b8; margin: 0;">Manage Categories</a>
            <a href="manage_jobs.php" class="btn-primary" style="background: #28a745; margin: 0;">Manage Jobs</a>
            <a href="settings.php" class="btn-primary" style="background: #6f42c1; margin: 0;">Policies</a>
            <a href="announcements.php" class="btn-primary" style="background: #007bff; margin: 0;">Announcements</a>
            <a href="analytics.php" class="btn-primary" style="background: #e8491d; margin: 0;">Analytics</a>
            <a href="users.php" class="btn-primary" style="background: #ffc107; color: #333; margin: 0;">
                Manage Users
                <?php if($stats['pending_verifications'] > 0) echo '<span style="background:red; color:white; padding:2px 6px; border-radius:50%; font-size:12px; margin-left:5px;">'.$stats['pending_verifications'].'</span>'; ?>
            </a>
            <a href="complaints.php" class="btn-primary" style="background: #dc3545; margin: 0;">
                Disputes & Complaints 
                <?php if($stats['open_complaints'] > 0) echo '<span style="background:white; color:#dc3545; padding:2px 6px; border-radius:50%; font-size:12px; margin-left:5px;">'.$stats['open_complaints'].'</span>'; ?>
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        
        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase;">Total Users</h3>
            <p style="margin: 0; font-size: 36px; font-weight: bold; color: #35424a;"><?= $stats['total_users'] ?></p>
            <div style="display: flex; justify-content: space-around; margin-top: 15px; font-size: 12px; color: #888;">
                <span>Seekers: <?= $stats['seekers'] ?? 0 ?></span>
                <span>Employers: <?= $stats['employers'] ?? 0 ?></span>
                <span>Recruiters: <?= $stats['recruiters'] ?? 0 ?></span>
            </div>
        </div>
        
        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase;">Active Jobs</h3>
            <p style="margin: 0; font-size: 36px; font-weight: bold; color: #28a745;"><?= $stats['active_jobs'] ?></p>
        </div>
        
        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase;">Total Applications</h3>
            <p style="margin: 0; font-size: 36px; font-weight: bold; color: #007bff;"><?= $stats['total_applications'] ?></p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #888;">
                <span style="color: #28a745; font-weight: bold;">+<?= $stats['applications_today'] ?></span> today
            </p>
        </div>
        
        <div style="background: white; border: <?= $stats['open_complaints'] > 0 ? '2px solid #dc3545' : '1px solid #ddd' ?>; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase;">Open Complaints</h3>
            <p style="margin: 0; font-size: 36px; font-weight: bold; color: <?= $stats['open_complaints'] > 0 ? '#dc3545' : '#666' ?>;"><?= $stats['open_complaints'] ?></p>
        </div>
        
    </div>

    <div style="background: white; border: 1px solid #ddd; padding: 30px; border-radius: 8px;">
        <h2>Welcome to the Admin Control Panel</h2>
        <p style="line-height: 1.6; color: #555;">
            From this dashboard, you have full control over the platform's operations:
        </p>
        <ul style="line-height: 1.8; color: #555;">
            <li><strong>Categories:</strong> Ensure job listings are organized by creating and managing industry categories.</li>
            <li><strong>Users:</strong> Monitor platform members. You have the power to verify trusted accounts or ban malicious actors.</li>
            <li><strong>Disputes:</strong> Act as the moderator. Review complaints filed by users regarding fake jobs, abusive recruiters, or scam seekers, and take appropriate action.</li>
        </ul>
    </div>
</div>

<?php include '../partials/footer.php'; ?>