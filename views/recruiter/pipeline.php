<?php
/**
 * views/recruiter/pipeline.php
 * Unified candidate pipeline for recruiters across all client jobs.
 */

require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$controller = new RecruiterController();

$recruiterId = Session::get('user_id');
$clients = $controller->getClients();

$statusFilter = $_GET['status'] ?? '';
$clientFilter = $_GET['client_id'] ?? '';

$sql = "SELECT 
            a.id AS application_id,
            a.job_id,
            a.seeker_id,
            a.status,
            a.applied_at,
            u.name AS candidate_name,
            u.email AS candidate_email,
            sp.headline,
            sp.years_experience,
            sp.expected_salary,
            sp.preferred_location,
            sp.resume_path,
            j.title AS job_title,
            j.status AS job_status,
            c.name AS category_name,
            COALESCE(emp.name, 'Standalone Client') AS client_name
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON a.seeker_id = u.id
        LEFT JOIN seeker_profiles sp ON a.seeker_id = sp.user_id
        LEFT JOIN users emp ON j.employer_id = emp.id
        LEFT JOIN categories c ON j.category_id = c.id
        WHERE j.recruiter_id = ?
        AND a.status IN ('submitted', 'reviewed', 'shortlisted', 'interview')";

$params = [$recruiterId];
$types = "i";

if (!empty($statusFilter)) {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($clientFilter)) {
    if (strpos($clientFilter, 'emp_') === 0) {
        $employerId = intval(substr($clientFilter, 4));
        $sql .= " AND j.employer_id = ?";
        $params[] = $employerId;
        $types .= "i";
    } elseif ($clientFilter === 'standalone') {
        $sql .= " AND j.employer_id IS NULL";
    }
}

$sql .= " ORDER BY a.applied_at DESC";

$stmt = mysqli_prepare($db, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $candidates = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} else {
    $candidates = [];
}

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Candidate Pipeline</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">← Back to Dashboard</a>
    </div>

    <div class="job-card" style="padding: 25px; margin-bottom: 30px;">
        <h2 style="margin-top: 0;">Filter Pipeline</h2>
        <p style="color: #666; margin-bottom: 20px;">
            View all active candidates across all client jobs and track their current recruitment stage.
        </p>

        <form method="GET" action="pipeline.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold;">Client</label>
                <select name="client_id" class="form-control" style="width: 100%; padding: 10px;">
                    <option value="">All Clients</option>

                    <?php foreach ($clients as $client): ?>
                        <?php if ($client['employer_id']): ?>
                            <option value="emp_<?= $client['employer_id'] ?>" 
                                <?= $clientFilter == 'emp_'.$client['employer_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['employer_name']) ?> (Registered Employer)
                            </option>
                        <?php else: ?>
                            <option value="standalone" <?= $clientFilter == 'standalone' ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['company_name_override']) ?> (Standalone Client)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold;">Current Stage</label>
                <select name="status" class="form-control" style="width: 100%; padding: 10px;">
                    <option value="">All Active Stages</option>
                    <option value="submitted" <?= $statusFilter == 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="reviewed" <?= $statusFilter == 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                    <option value="shortlisted" <?= $statusFilter == 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                    <option value="interview" <?= $statusFilter == 'interview' ? 'selected' : '' ?>>Interview</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="padding: 11px 22px; border: none; cursor: pointer; background: #20c997;">
                Apply Filter
            </button>
        </form>
    </div>

    <div class="job-list">
        <h2>Active Candidates</h2>

        <?php if (empty($candidates)): ?>
            <div class="job-card" style="text-align: center; color: #777;">
                <p>No active candidates found in the pipeline.</p>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #000000ff; color: white; text-align: left;">
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Candidate</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Client</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Job</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Category</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Current Stage</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Applied Date</th>
                        <th style="padding: 12px; border-bottom: 1px solid #ddd;">Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;">
                                <strong><?= htmlspecialchars($candidate['candidate_name']) ?></strong><br>
                                <small style="color: #666;"><?= htmlspecialchars($candidate['candidate_email']) ?></small>

                                <?php if (!empty($candidate['headline'])): ?>
                                    <br><small style="color: #777;"><?= htmlspecialchars($candidate['headline']) ?></small>
                                <?php endif; ?>

                                <?php if ($candidate['years_experience'] !== null): ?>
                                    <br><small style="color: #777;">Experience: <?= htmlspecialchars($candidate['years_experience']) ?> years</small>
                                <?php endif; ?>
                            </td>

                            <td style="padding: 12px;">
                                <?= htmlspecialchars($candidate['client_name']) ?>
                            </td>

                            <td style="padding: 12px;">
                                <strong><?= htmlspecialchars($candidate['job_title']) ?></strong>
                            </td>

                            <td style="padding: 12px;">
                                <?= htmlspecialchars($candidate['category_name'] ?? 'N/A') ?>
                            </td>

                            <td style="padding: 12px;">
                                <?php
                                    $stageColor = '#cfe2ff';

                                    if ($candidate['status'] == 'reviewed') {
                                        $stageColor = '#e2e3e5';
                                    } elseif ($candidate['status'] == 'shortlisted') {
                                        $stageColor = '#d1e7dd';
                                    } elseif ($candidate['status'] == 'interview') {
                                        $stageColor = '#fff3cd';
                                    }
                                ?>

                                <span class="badge" style="background: <?= $stageColor ?>; padding: 5px 10px; border-radius: 4px; font-size: 12px; color: #333;">
                                    <?= strtoupper(htmlspecialchars($candidate['status'])) ?>
                                </span>
                            </td>

                            <td style="padding: 12px;">
                                <?= date('M d, Y', strtotime($candidate['applied_at'])) ?>
                            </td>

                            <td style="padding: 12px;">
                                <a href="view_applicants.php?job_id=<?= $candidate['job_id'] ?>" 
                                   style="color: #007bff; text-decoration: none; font-weight: bold;">
                                    Manage
                                </a>
                                <br>
                                <a href="messages.php?contact_id=<?= $candidate['seeker_id'] ?>" 
                                   style="color: #e8491d; text-decoration: none; font-weight: bold;">
                                    Message
                                </a>

                                <?php if (!empty($candidate['resume_path'])): ?>
                                    <br>
                                    <a href="/JOB-PORTAL/public/uploads/resumes/<?= htmlspecialchars($candidate['resume_path']) ?>" 
                                       target="_blank"
                                       style="color: #28a745; text-decoration: none; font-weight: bold;">
                                        Resume
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>