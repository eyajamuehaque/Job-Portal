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
    <div class="search-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <form id="search-form" onsubmit="event.preventDefault(); performSearch();">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                <input type="text" name="keyword" id="keyword" placeholder="Search by job title or skills..." style="flex: 1; min-width: 200px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                
                <select name="category" id="category" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="location" id="location" placeholder="Location..." style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 150px;">
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <select name="job_type" id="job_type" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">All Job Types</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="remote">Remote</option>
                    <option value="contract">Contract</option>
                </select>

                <select name="experience_level" id="experience_level" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Any Experience</option>
                    <option value="entry">Entry Level</option>
                    <option value="mid">Mid Level</option>
                    <option value="senior">Senior Level</option>
                </select>

                <input type="number" name="salary_min" id="salary_min" placeholder="Min Salary (BDT)" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 150px;">
                
                <button type="submit" class="btn-primary" style="padding: 10px 30px;">Search</button>
            </div>
        </form>
    </div>

    <div class="job-list" id="job-results-container">
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

function performSearch() {
    const keyword = document.getElementById('keyword').value;
    const category = document.getElementById('category').value;
    const location = document.getElementById('location').value;
    const job_type = document.getElementById('job_type').value;
    const experience_level = document.getElementById('experience_level').value;
    const salary_min = document.getElementById('salary_min').value;

    const queryParams = new URLSearchParams({
        keyword, category, location, job_type, experience_level, salary_min
    });

    fetch(`../api/search-jobs.php?${queryParams.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderJobs(data.jobs, data.role);
            } else {
                alert('Search failed');
            }
        })
        .catch(err => {
            console.error('Error fetching jobs:', err);
        });
}

function renderJobs(jobs, role) {
    const container = document.getElementById('job-results-container');
    let html = '<h2>Latest Opportunities</h2>';
    
    if (jobs.length === 0) {
        html += `<div class="job-card"><p>No job postings match your search.</p></div>`;
    } else {
        jobs.forEach(job => {
            let bookmarkBtnHTML = '';
            if (role === 'seeker') {
                const btnText = job.isBookmarked ? 'Remove Bookmark' : 'Bookmark';
                const btnColor = job.isBookmarked ? '#dc3545' : '#17a2b8';
                bookmarkBtnHTML = `
                    <button onclick="toggleBookmark(${job.id})" id="bookmarkBtn_${job.id}" class="btn-primary" style="padding: 8px 18px; font-size: 14px; border: none; cursor: pointer; background-color: ${btnColor}; color: white;">
                        ${btnText}
                    </button>
                `;
            }

            html += `
                <div class="job-card">
                    <h3>${job.title_safe}</h3>
                    <p class="job-meta">
                        <strong>Location:</strong> ${job.location_safe} | 
                        <strong>Type:</strong> <span style="color: #e8491d;">${job.job_type_safe}</span>
                    </p>
                    <p>${job.description_short}</p>
                    <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                        <a href="job_details.php?id=${job.id}" class="btn-primary" style="padding: 8px 18px; font-size: 14px;">View Details</a>
                        ${bookmarkBtnHTML}
                    </div>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
}

// Attach change events to auto-submit when filters change
document.querySelectorAll('#search-form select, #search-form input').forEach(input => {
    input.addEventListener('change', () => {
        // Only auto-search on select change or if they press enter on text inputs (handled by form submit)
        if (input.tagName === 'SELECT') {
            performSearch();
        }
    });
});

</script>