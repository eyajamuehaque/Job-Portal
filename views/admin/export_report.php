<?php
/**
 * views/admin/export_report.php
 * Generates and downloads a CSV report of platform metrics.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/AdminController.php';

Session::init();
Session::checkRole('admin');

$controller = new AdminController();
$analytics = $controller->getAnalytics();

// Set Headers for CSV Download
$filename = "platform_report_" . date('Y_m_d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Title row
fputcsv($output, ['Job Portal - Platform Analytics Report']);
fputcsv($output, ['Generated on:', date('Y-m-d H:i:s')]);
fputcsv($output, []); // blank line

// Jobs By Category
fputcsv($output, ['=== Top Job Categories ===']);
fputcsv($output, ['Category Name', 'Active Jobs']);
if (!empty($analytics['jobs_by_category'])) {
    foreach ($analytics['jobs_by_category'] as $cat) {
        fputcsv($output, [$cat['name'], $cat['count']]);
    }
} else {
    fputcsv($output, ['No data available']);
}
fputcsv($output, []);

// Top Employers
fputcsv($output, ['=== Top Employers (By Active Jobs) ===']);
fputcsv($output, ['Employer Name', 'Active Jobs']);
if (!empty($analytics['top_employers'])) {
    foreach ($analytics['top_employers'] as $emp) {
        fputcsv($output, [$emp['name'], $emp['count']]);
    }
} else {
    fputcsv($output, ['No data available']);
}
fputcsv($output, []);

// User Growth
fputcsv($output, ['=== User Growth (Last 6 Months) ===']);
fputcsv($output, ['Month', 'Role', 'New Users']);
if (!empty($analytics['new_users_by_month'])) {
    foreach ($analytics['new_users_by_month'] as $growth) {
        fputcsv($output, [$growth['month'], $growth['role'], $growth['count']]);
    }
} else {
    fputcsv($output, ['No data available']);
}

fclose($output);
exit();
?>
