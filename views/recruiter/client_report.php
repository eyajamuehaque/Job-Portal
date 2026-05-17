<?php
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

$db = (new Database())->conn;
$controller = new RecruiterController();
$clients = $controller->getClients();

$selected = $_GET['client_id'] ?? '';
$reportRows = [];

if ($selected) {
    $recruiter_id = Session::get('user_id');

    if (strpos($selected, 'emp_') === 0) {
        $employer_id = intval(substr($selected, 4));

        $sql = "SELECT j.id, j.title, j.status,
                COUNT(a.id) AS total_applications,
                SUM(a.status='submitted') AS submitted_count,
                SUM(a.status='reviewed') AS reviewed_count,
                SUM(a.status='shortlisted') AS shortlisted_count,
                SUM(a.status='interview') AS interview_count,
                SUM(a.status='rejected') AS rejected_count,
                SUM(a.status='withdrawn') AS withdrawn_count
                FROM jobs j
                LEFT JOIN applications a ON j.id = a.job_id
                WHERE j.recruiter_id = ? AND j.employer_id = ?
                GROUP BY j.id
                ORDER BY j.created_at DESC";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $recruiter_id, $employer_id);

    } elseif (strpos($selected, 'rc_') === 0) {
        $client_id = intval(substr($selected, 3));

        $sql = "SELECT j.id, j.title, j.status,
                COUNT(a.id) AS total_applications,
                SUM(a.status='submitted') AS submitted_count,
                SUM(a.status='reviewed') AS reviewed_count,
                SUM(a.status='shortlisted') AS shortlisted_count,
                SUM(a.status='interview') AS interview_count,
                SUM(a.status='rejected') AS rejected_count,
                SUM(a.status='withdrawn') AS withdrawn_count
                FROM jobs j
                LEFT JOIN applications a ON j.id = a.job_id
                WHERE j.recruiter_id = ? AND j.employer_id IS NULL
                GROUP BY j.id
                ORDER BY j.created_at DESC";

        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
    }

    if (isset($stmt)) {
        mysqli_stmt_execute($stmt);
        $reportRows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    }
}

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Client Report</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">← Back to Dashboard</a>
    </div>

    <div class="job-card" style="padding: 25px; margin-bottom: 30px;">
        <h2 style="margin-top: 0;">Generate Client Report</h2>
        <p style="color: #666; margin-bottom: 20px;">
            Select a client to view total jobs posted, applications received per job, and current pipeline stage counts.
        </p>

        <form method="GET" action="client_report.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold;">Select Client</label>
                <select name="client_id" class="form-control" required style="width: 100%; padding: 10px;">
                    <option value="">-- Choose Client --</option>

                    <?php foreach ($clients as $client): ?>
                        <?php if ($client['employer_id']): ?>
                            <option value="emp_<?= $client['employer_id'] ?>" 
                                <?= $selected == 'emp_'.$client['employer_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['employer_name']) ?> (Registered Employer)
                            </option>
                        <?php else: ?>
                            <option value="rc_<?= $client['id'] ?>" 
                                <?= $selected == 'rc_'.$client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['company_name_override']) ?> (Standalone Client)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="padding: 11px 22px; border: none; cursor: pointer; background: #6610f2;">
                Generate Report
            </button>
        </form>
    </div>

    <?php if ($selected): ?>
        <div class="job-list">
            <h2>Report Result</h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center;">
                    <h3 style="margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase;">Total Jobs Posted</h3>
                    <p style="margin: 0; font-size: 34px; font-weight: bold; color: #35424a;">
                        <?= count($reportRows) ?>
                    </p>
                </div>
            </div>

            <?php if (empty($reportRows)): ?>
                <div class="job-card" style="text-align: center; color: #777;">
                    <p>No jobs found for this client.</p>
                </div>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="background-color: #000000ff; color: white; text-align: left;">
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Job</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Status</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Total Applications</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Submitted</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Reviewed</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Shortlisted</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Interview</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Rejected</th>
                            <th style="padding: 12px; border-bottom: 1px solid #ddd;">Withdrawn</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($reportRows as $r): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                    <strong><?= htmlspecialchars($r['title']) ?></strong>
                                </td>

                                <td style="padding: 12px; border-bottom: 1px solid #ddd;">
                                    <span class="badge" style="background: <?= $r['status'] == 'active' ? '#d1e7dd' : '#f8d7da' ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: #333;">
                                        <?= strtoupper(htmlspecialchars($r['status'])) ?>
                                    </span>
                                </td>

                                <td style="padding: 12px; border-bottom: 1px solid #ddd; font-weight: bold;">
                                    <?= $r['total_applications'] ?>
                                </td>

                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['submitted_count'] ?? 0 ?></td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['reviewed_count'] ?? 0 ?></td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['shortlisted_count'] ?? 0 ?></td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['interview_count'] ?? 0 ?></td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['rejected_count'] ?? 0 ?></td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= $r['withdrawn_count'] ?? 0 ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../partials/footer.php'; ?>