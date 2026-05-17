<?php
/**
 * views/admin/analytics.php
 * View platform-wide analytics and charts.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();
$analytics = $controller->getAnalytics();

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Platform Analytics</h1>
        <div>
            <a href="export_report.php" class="btn-primary" style="background: #28a745; margin-right: 10px;">Export CSV Report</a>
            <a href="dashboard.php" class="btn-primary" style="background: #6c757d;">Back to Dashboard</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Top Employers -->
        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h3>Top Employers (By Active Jobs)</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Employer Name</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Active Jobs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($analytics['top_employers'] as $emp): ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;"><?= htmlspecialchars($emp['name']) ?></td>
                            <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                <span style="background: #007bff; color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;"><?= $emp['count'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Jobs by Category -->
        <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
            <h3>Top Job Categories</h3>
            <canvas id="categoryChart"></canvas>
        </div>

    </div>

    <div style="background: white; border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-top: 30px;">
        <h3>User Growth (Last 6 Months)</h3>
        <canvas id="growthChart" height="100"></canvas>
    </div>

</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Category Data
const catLabels = <?= json_encode(array_column($analytics['jobs_by_category'], 'name')) ?>;
const catData = <?= json_encode(array_column($analytics['jobs_by_category'], 'count')) ?>;

new Chart(document.getElementById('categoryChart'), {
    type: 'pie',
    data: {
        labels: catLabels,
        datasets: [{
            data: catData,
            backgroundColor: ['#e8491d', '#35424a', '#17a2b8', '#ffc107', '#28a745']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Growth Data (Requires some formatting)
const rawGrowthData = <?= json_encode($analytics['new_users_by_month']) ?>;
const months = [...new Set(rawGrowthData.map(item => item.month))].sort();

const seekerData = months.map(m => {
    const record = rawGrowthData.find(d => d.month === m && d.role === 'seeker');
    return record ? record.count : 0;
});

const employerData = months.map(m => {
    const record = rawGrowthData.find(d => d.month === m && d.role === 'employer');
    return record ? record.count : 0;
});

const recruiterData = months.map(m => {
    const record = rawGrowthData.find(d => d.month === m && d.role === 'recruiter');
    return record ? record.count : 0;
});

new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [
            {
                label: 'Seekers',
                data: seekerData,
                borderColor: '#17a2b8',
                tension: 0.1
            },
            {
                label: 'Employers',
                data: employerData,
                borderColor: '#e8491d',
                tension: 0.1
            },
            {
                label: 'Recruiters',
                data: recruiterData,
                borderColor: '#ffc107',
                tension: 0.1
            }
        ]
    }
});
</script>

<?php include '../partials/footer.php'; ?>
