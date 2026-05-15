<?php
/**
 * views/auth/login.php
 * User login form.
 */

require_once '../../app/controllers/AuthController.php';

// Initialize the controller
$authController = new AuthController();

// Handle the login request and get any error messages
$error = $authController->login();

// Include the header partial
include '../partials/header.php';
?>

<div class="container">
    <div class="job-card" style="max-width: 450px; margin: 60px auto; padding: 40px;">
        <h2 style="text-align: center; margin-bottom: 20px;">Welcome Back</h2>
        
        <!-- Display error message if login fails -->
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Display success message from registration if redirected -->
        <?php if (isset($_GET['msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required placeholder="Enter your email">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Enter your password">
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn-primary" style="width: 100%;">Login to Account</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            Don't have an account? <a href="register.php" style="color: #e8491d; font-weight: bold;">Register here</a>
        </p>
    </div>
</div>

<?php 
// Include the footer partial
include '../partials/footer.php'; 
?>