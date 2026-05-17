<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <link rel="stylesheet" href="http://localhost:8081/Job-Portal/public/css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div id="branding">
            <h1><span style="color: #e8491d;">Job</span> Portal</h1>
        </div>
        <nav>
            <ul>
                <li><a href="/JOB-PORTAL/public/index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="/JOB-PORTAL/views/<?= $_SESSION['role'] ?>/dashboard.php">Dashboard</a></li>
        <li><a href="/JOB-PORTAL/views/auth/logout.php">Logout</a></li>
    <?php else: ?>
        <li><a href="/JOB-PORTAL/views/auth/login.php">Login</a></li>
        <li><a href="/JOB-PORTAL/views/auth/register.php" class="btn-primary">Register</a></li>
    <?php endif; ?>
</ul>
        </nav>
    </div>
</header>

<?php
// Fetch Announcements
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    require_once __DIR__ . '/../../app/core/Database.php';
    
    // We only create connection if it doesn't exist, but here we can just create a new one safely for this simple query.
    $db_conn = (new Database())->conn;
    
    $sql = "SELECT title, message FROM announcements 
            WHERE is_active = 1 
            AND (target_role = 'all' OR target_role = ?)";
            
    $stmt = mysqli_prepare($db_conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $role);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($ann = mysqli_fetch_assoc($result)) {
            echo '<div style="background: #17a2b8; color: white; padding: 10px 20px; text-align: center; border-bottom: 1px solid #117a8b;">';
            echo '<strong>' . htmlspecialchars($ann['title']) . ':</strong> ' . htmlspecialchars($ann['message']);
            echo '</div>';
        }
        mysqli_stmt_close($stmt);
    }
}
?>