<?php
require_once '../../app/core/Session.php';
require_once '../../app/core/Database.php';

Session::init();

include '../partials/header.php';

// Security: Only Recruiters allowed
if (Session::get('role') !== 'recruiter') {
    header("Location: /Job-Portal/views/auth/login.php");
    exit();
}

$database = new Database();
$db = $database->conn;
$recruiter_id = Session::get('user_id');

// Handle adding a new client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client'])) {
    $name = htmlspecialchars($_POST['client_name']);
    $contact = htmlspecialchars($_POST['contact_person']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);

    $sql = "INSERT INTO recruiter_clients (recruiter_id, client_name, contact_person, email, phone) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $recruiter_id, $name, $contact, $email, $phone);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Client added successfully!";
    } else {
        $error = "Error adding client.";
    }
}

// Fetch existing clients for this recruiter
$query = "SELECT * FROM recruiter_clients WHERE recruiter_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $recruiter_id);
mysqli_stmt_execute($stmt);
$clients = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clients - Recruiter</title>
    <link rel="stylesheet" href="/Job-Portal/public/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <div class="job-list">
            <h2>Partnered Client Companies</h2>
            <p>Add the companies you are currently representing to enable job postings for them.</p>

            <?php if (isset($success)): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3>Add New Client</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <input type="text" name="client_name" placeholder="Company Name (e.g. Apex)" required style="padding: 10px;">
                    <input type="text" name="contact_person" placeholder="Contact Person Name" style="padding: 10px;">
                    <input type="email" name="email" placeholder="Client Email" style="padding: 10px;">
                    <input type="text" name="phone" placeholder="Client Phone" style="padding: 10px;">
                </div>
                <button type="submit" name="add_client" class="btn-primary" style="margin-top: 15px; padding: 10px 25px;">
                    Register Client
                </button>
            </form>

            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 30px;">

            <table class="job-listings-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #35424a; color: white;">
                        <th style="padding: 12px; text-align: left;">Company Name</th>
                        <th style="padding: 12px; text-align: left;">Contact Person</th>
                        <th style="padding: 12px; text-align: left;">Email</th>
                        <th style="padding: 12px; text-align: left;">Date Added</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($clients) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($clients)): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?= htmlspecialchars($row['client_name']) ?></strong></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($row['contact_person']) ?></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($row['email']) ?></td>
                                <td style="padding: 12px;"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; color: #777;">
                                No clients found. Add your first client above.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 30px;">
                <a href="dashboard.php" style="text-decoration: none; color: #35424a; font-weight: bold;">← Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>