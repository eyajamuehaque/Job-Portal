<?php
/**
 * views/auth/register.php
 * User registration form.
 */

require_once '../../app/controllers/AuthController.php';


$authController = new AuthController();


$error = $authController->register();


include '../partials/header.php';
?>

<div class="container">
    <div class="job-card" style="max-width: 550px; margin: 40px auto; padding: 40px;">
        <h2 style="text-align: center; margin-bottom: 20px;">Create an Account</h2>
        
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>//
                <input type="text" name="name" id="name" class="form-control" required placeholder="John Doe">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required placeholder="john@example.com">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="phone">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="01XXXXXXXXX">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required placeholder="Minimum 6 characters">
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label for="role">Register As</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="seeker">Job Seeker</option>
                    <option value="employer">Employer / Company</option>
                    <option value="recruiter">Recruiter / Agency</option>
                </select>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn-primary" style="width: 100%;">Create Account</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 14px;">
            Already have an account? <a href="login.php" style="color: #e8491d; font-weight: bold;">Login here</a>
        </p>
    </div>
</div>

<?php 
// Include the footer partial
include '../partials/footer.php'; 
?>