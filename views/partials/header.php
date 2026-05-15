<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>
    <link rel="stylesheet" href="http://localhost:8080/Job-Portal/public/css/style.css">
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