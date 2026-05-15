<?php
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';
require_once '../../app/models/Application.php';

Session::init();

// Ensure only employers can see this
if (Session::get('role') !== 'employer') {
    header("Location: /Job-Portal/views/auth/login.php");
    exit();
}

$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    header("Location: dashboard.php");
    exit();
}

$status_filter = $_GET['status'] ?? null;
$experience_filter = $_GET['experience_level'] ?? null;
$sort_order = $_GET['date_sort'] ?? 'DESC';

$database = new Database();
$appModel = new Application($database->conn);
$applicants = $appModel->getByJob($job_id, $status_filter, $experience_filter, $sort_order);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Applicants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
        .filters { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: flex; gap: 15px; align-items: flex-end; }
        .filters select { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
        .btn { padding: 8px 15px; background: #35424a; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .status-select { padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
        .cover-letter-btn { background: #17a2b8; color: white; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 20px; border-radius: 5px; max-width: 600px; width: 100%; max-height: 80vh; overflow-y: auto; }
        .close-modal { float: right; cursor: pointer; font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" style="text-decoration: none; color: #35424a; font-weight: bold;">← Back to Dashboard</a>
        <h2>Applicants for Job #<?php echo htmlspecialchars($job_id); ?></h2>

        <!-- Filters -->
        <form method="GET" action="view_applicants.php" class="filters">
            <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_id) ?>">
            
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="submitted" <?= $status_filter == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="reviewed" <?= $status_filter == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                    <option value="shortlisted" <?= $status_filter == 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                    <option value="interview" <?= $status_filter == 'interview' ? 'selected' : '' ?>>Interview</option>
                    <option value="rejected" <?= $status_filter == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Experience Level</label>
                <select name="experience_level">
                    <option value="">All Levels</option>
                    <option value="entry" <?= $experience_filter == 'entry' ? 'selected' : '' ?>>Entry Level (<2 yrs)</option>
                    <option value="mid" <?= $experience_filter == 'mid' ? 'selected' : '' ?>>Mid Level (2-5 yrs)</option>
                    <option value="senior" <?= $experience_filter == 'senior' ? 'selected' : '' ?>>Senior Level (>5 yrs)</option>
                </select>
            </div>

            <div>
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Sort By Date</label>
                <select name="date_sort">
                    <option value="DESC" <?= $sort_order == 'DESC' ? 'selected' : '' ?>>Newest First</option>
                    <option value="ASC" <?= $sort_order == 'ASC' ? 'selected' : '' ?>>Oldest First</option>
                </select>
            </div>

            <button type="submit" class="btn">Apply Filters</button>
        </form>

        <?php if (empty($applicants)): ?>
            <p>No applicants found matching your criteria.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Applicant Details</th>
                        <th>Applied On</th>
                        <th>Documents</th>
                        <th>Update Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($applicants as $row): ?>
                    <tr id="app-row-<?= $row['id'] ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($row['applicant_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['applicant_email']); ?></small><br>
                            <?php if(isset($row['headline'])): ?>
                                <small style="color: #666;"><?php echo htmlspecialchars($row['headline']); ?> (<?= $row['years_experience'] ?> yrs exp)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['applied_at'])); ?></td>
                        <td>
                            <?php if(!empty($row['resume_path'])): ?>
                                <a href="/Job-Portal/public/uploads/resumes/<?php echo htmlspecialchars($row['resume_path']); ?>" target="_blank" style="color: #e8491d;">View Resume</a><br>
                            <?php endif; ?>
                            <?php if(!empty($row['cover_letter'])): ?>
                                <button class="cover-letter-btn" style="margin-top:5px;" onclick="showCoverLetter(`<?= htmlspecialchars($row['applicant_name']) ?>`, `<?= htmlspecialchars(nl2br($row['cover_letter'])) ?>`)">Cover Letter</button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <select class="status-select" onchange="updateApplicantStatus(<?= $row['id'] ?>, this.value)">
                                <option value="submitted" <?= $row['status'] == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                                <option value="reviewed" <?= $row['status'] == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                <option value="shortlisted" <?= $row['status'] == 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                <option value="interview" <?= $row['status'] == 'interview' ? 'selected' : '' ?>>Interview</option>
                                <option value="rejected" <?= $row['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <span id="status-msg-<?= $row['id'] ?>" style="font-size: 12px; margin-left: 5px; color: green; display: none;">Saved</span>
                        </td>
                        <td>
                            <a href="messages.php?contact_id=<?= $row['seeker_id'] ?>" style="color: #007bff; text-decoration: none;">Message</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Cover Letter Modal -->
    <div id="clModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('clModal').style.display='none'">&times;</span>
            <h3 id="modalTitle">Cover Letter</h3>
            <div id="modalBody" style="margin-top: 15px; line-height: 1.6; color: #333;"></div>
        </div>
    </div>

    <script src="../../public/js/status-updates.js"></script>
    <script>
        function showCoverLetter(name, content) {
            document.getElementById('modalTitle').innerText = name + "'s Cover Letter";
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('clModal').style.display = 'flex';
        }
    </script>
</body>
</html>