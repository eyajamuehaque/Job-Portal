<?php
/**
 * views/recruiter/clients.php
 * Interface for recruiters to manage their clients (companies they recruit for).
 */
require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

$controller = new RecruiterController();

if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    $controller->removeClient(intval($_GET['id']));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $controller->addClient();
}

$clients = $controller->getClients();

// Fetch list of registered employers for the dropdown
$db = (new Database())->conn;
$empSql = "SELECT id, name, email FROM users WHERE role = 'employer' AND is_active = 1";
$empRes = mysqli_query($db, $empSql);
$registeredEmployers = mysqli_fetch_all($empRes, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .navbar { margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .navbar a { margin-right: 15px; text-decoration: none; color: #333; font-weight: bold; }
        
        .client-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .client-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; background: #fff; }
        .client-card h3 { margin: 0 0 10px 0; color: #35424a; }
        
        .add-client-form { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 30px; border: 1px solid #eee; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 8px 15px; background: #35424a; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn-danger { background: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; font-size: 12px; }
        
        .message { padding: 10px; background-color: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px; }
        .error { padding: 10px; background-color: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="navbar">
        <a href="dashboard.php">← Back to Dashboard</a>
        <a href="clients.php">Manage Clients</a>
    </div>

    <h2>My Clients</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="message"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="add-client-form">
        <h3 style="margin-top:0;">Add New Client</h3>
        <p style="font-size: 14px; color: #666;">You can either link an existing Employer account on the platform OR create a standalone client name.</p>
        
        <form method="POST" action="clients.php">
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>Link Registered Employer</label>
                    <select name="employer_id">
                        <option value="">-- Select an Employer --</option>
                        <?php foreach ($registeredEmployers as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="padding-top: 30px; font-weight: bold; color: #888;">OR</div>
                
                <div class="form-group" style="flex: 1;">
                    <label>Standalone Company Name</label>
                    <input type="text" name="company_name_override" placeholder="e.g. Acme Corp (Not on platform)">
                </div>
            </div>
            <button type="submit" class="btn">Add Client</button>
        </form>
    </div>

    <div class="client-grid">
        <?php if (empty($clients)): ?>
            <p style="grid-column: 1 / -1;">You haven't added any clients yet.</p>
        <?php else: ?>
            <?php foreach ($clients as $client): ?>
                <div class="client-card">
                    <?php if ($client['employer_id']): ?>
                        <h3><?= htmlspecialchars($client['employer_name']) ?> <span style="font-size:10px; background:#28a745; color:white; padding:2px 5px; border-radius:3px; vertical-align:top;">Linked</span></h3>
                        <p style="margin: 5px 0; font-size: 14px; color: #555;">Email: <?= htmlspecialchars($client['employer_email']) ?></p>
                    <?php else: ?>
                        <h3><?= htmlspecialchars($client['company_name_override']) ?> <span style="font-size:10px; background:#6c757d; color:white; padding:2px 5px; border-radius:3px; vertical-align:top;">Standalone</span></h3>
                    <?php endif; ?>
                    
                    <p style="font-size: 12px; color: #888;">Added: <?= date('M d, Y', strtotime($client['added_at'])) ?></p>
                    
                    <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                        <a href="clients.php?action=remove&id=<?= $client['id'] ?>" class="btn-danger" onclick="return confirm('Are you sure you want to remove this client?');">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
