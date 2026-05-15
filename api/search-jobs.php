<?php
/**
 * api/search-jobs.php
 * JSON endpoint for AJAX job searching.
 * Used by Job Seekers on the main landing page or dashboard.
 */

require_once '../app/controllers/SeekerController.php';

// Instantiate the controller
// Note: SeekerController automatically checks if the user is a 'seeker' 
// but since the public landing page also uses search, ensure your 
// controller allows public search or use a separate logic.
$seekerController = new SeekerController();

// The search() method in SeekerController is already designed 
// to detect 'ajax=1' and return JSON.
$_GET['ajax'] = 1; 
$seekerController->search();