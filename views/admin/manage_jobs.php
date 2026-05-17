<?php
/**
 * views/admin/manage_jobs.php
 * View and moderate all job postings across the platform.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $jobId = intval($_GET['id']);
    if ($_GET['action'] === 'toggle_featured') {
        $controller->toggleFeaturedJob($jobId);
    } elseif ($_GET['action'] === 'delete') {
        $controller->deleteJob($jobId);
    }
}

// Fetch all jobs
$jobs = $controller->getAllJobs();
$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? '';

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Manage Jobs</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #6c757d;">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <form action="manage_jobs.php" method="GET" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="keyword" placeholder="Search title or company..." value="<?= htmlspecialchars($keyword) ?>" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; flex: 1;">
            
            <select name="status" style="padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="">All Statuses</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
            
            <button type="submit" class="btn-primary" style="padding: 10px 20px;">Filter</button>
            <a href="manage_jobs.php" style="color: #007bff; text-decoration: none; margin-left: 10px;">Clear</a>
        </form>
    </div>

    <div style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: #f4f4f4; border-bottom: 2px solid #ddd;">
                <tr>
                    <th style="padding: 15px;">Job Title</th>
                    <th style="padding: 15px;">Company/Recruiter</th>
                    <th style="padding: 15px;">Status</th>
                    <th style="padding: 15px;">Featured</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($jobs)): ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">
                                <strong><?= htmlspecialchars($job['title']) ?></strong>
                                <br>
                                <span style="font-size: 12px; color: #888;"><?= htmlspecialchars($job['category_name']) ?></span>
                            </td>
                            <td style="padding: 15px;">
                                <?= htmlspecialchars($job['posted_by_name']) ?>
                                <span style="display: block; font-size: 12px; color: #888;"><?= htmlspecialchars($job['posted_by_type']) ?></span>
                            </td>
                            <td style="padding: 15px;">
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: <?= $job['status'] === 'active' ? '#d4edda' : ($job['status'] === 'closed' ? '#f8d7da' : '#fff3cd') ?>; color: <?= $job['status'] === 'active' ? '#155724' : ($job['status'] === 'closed' ? '#721c24' : '#856404') ?>;">
                                    <?= ucfirst($job['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 15px;">
                                <?php if ($job['is_featured']): ?>
                                    <span style="color: #ffc107; font-size: 18px;" title="Featured">★</span>
                                <?php else: ?>
                                    <span style="color: #ccc; font-size: 18px;" title="Not Featured">☆</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px;">
                                <div style="display: flex; gap: 5px;">
                                    <a href="../../public/job_details.php?id=<?= $job['id'] ?>" target="_blank" class="btn-primary" style="background: #17a2b8; font-size: 12px; padding: 5px 10px;">View</a>
                                    
                                    <a href="manage_jobs.php?action=toggle_featured&id=<?= $job['id'] ?>" class="btn-primary" style="background: #ffc107; color: #333; font-size: 12px; padding: 5px 10px;">
                                        <?= $job['is_featured'] ? 'Unfeature' : 'Feature' ?>
                                    </a>
                                    
                                    <a href="manage_jobs.php?action=delete&id=<?= $job['id'] ?>" class="btn-primary" style="background: #dc3545; font-size: 12px; padding: 5px 10px;" onclick="return confirm('Are you sure you want to permanently delete this job? This cannot be undone.');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: #777;">No jobs found matching your criteria.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
