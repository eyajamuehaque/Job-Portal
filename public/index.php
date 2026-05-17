<?php
/**
 * public/index.php
 * Main landing page for the Job Portal.
 * Clean version using Partials and MVC logic.
 */

// 1. Include core files and models
require_once '../app/core/Database.php';
require_once '../app/core/Session.php';
require_once '../app/models/Job.php';

// 2. Initialize session
Session::init();

// 3. Setup Database and Job Model
$database = new Database();
$jobModel = new Job($database->conn);

// 4. Handle Search Logic
$keyword = isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : "";
$category_id = isset($_GET['category']) ? intval($_GET['category']) : null;

// Fetch jobs and categories
$jobs = $jobModel->search($keyword, $category_id);
$categories = $jobModel->getCategories();

require_once '../app/models/SavedJob.php';
$savedJobModel = new SavedJob($database->conn);
$savedJobIds = [];
if (Session::get('role') === 'seeker') {
    $savedJobs = $savedJobModel->getByUser(Session::get('user_id'));
    foreach ($savedJobs as $sj) {
        $savedJobIds[] = $sj['job_id'];
    }
}

// 5. INCLUDE THE HEADER PARTIAL
// Note: Adjusted path assuming partials is in ../views/partials/
include '../views/partials/header.php'; 
?>

<section id="showcase">
    <div class="container">
        <h1>Find Your Future Career</h1>
        <p>Connecting the best talent with top employers and recruiters worldwide.</p>
    </div>
</section>

<div class="container">
    <div class="search-container">
        <form action="index.php" method="GET">
            <input type="text" name="keyword" placeholder="Search by job title or skills..." value="<?= $keyword ?>">
            
            <select name="category" class="form-control" style="width: auto; margin-right: 10px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>

    <div class="job-list">
        <h2>Latest Opportunities</h2>
        
        <?php if (!empty($jobs)): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p class="job-meta">
                        <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?> | 
                        <strong>Type:</strong> <span style="color: #e8491d;"><?= htmlspecialchars($job['job_type']) ?></span>
                    </p>
                    <p>
                        <?= (strlen($job['description']) > 180) 
                            ? substr(htmlspecialchars($job['description']), 0, 180) . '...' 
                            : htmlspecialchars($job['description']) ?>
                    </p>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                        <a href="job_details.php?id=<?= $job['id'] ?>" class="btn-primary" style="padding: 8px 18px; font-size: 14px;">View Details</a>
                        <?php if (Session::get('role') === 'seeker'): ?>
                            <?php $isBookmarked = in_array($job['id'], $savedJobIds); ?>
                            <button onclick="toggleBookmark(<?= $job['id'] ?>)" id="bookmarkBtn_<?= $job['id'] ?>" class="btn-primary" style="padding: 8px 18px; font-size: 14px; border: none; cursor: pointer; background-color: <?= $isBookmarked ? '#dc3545' : '#17a2b8' ?>; color: white;">
                                <?= $isBookmarked ? 'Remove Bookmark' : 'Bookmark' ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="job-card">
                <p>No job postings match your search. Try different keywords or browse all categories.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// 6. INCLUDE THE FOOTER PARTIAL
include '../views/partials/footer.php'; 
?>

<script>
function toggleBookmark(jobId) {
    fetch('../api/toggle-bookmark.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const btn = document.getElementById('bookmarkBtn_' + jobId);
            if (data.status === 'saved') {
                btn.innerText = 'Remove Bookmark';
                btn.style.backgroundColor = '#dc3545';
            } else {
                btn.innerText = 'Bookmark';
                btn.style.backgroundColor = '#17a2b8';
            }
        } else {
            alert('An error occurred.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred.');
    });
}
</script>