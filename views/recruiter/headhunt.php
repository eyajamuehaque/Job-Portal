<?php
/**
 * views/recruiter/headhunt.php
 * AJAX-powered search page for recruiters to find top talent.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <h1>Headhunt Candidates</h1>
    <p>Search our entire database of job seekers by name, headline, or specific skills.</p>

    <div class="search-container">
        <div style="display: flex; gap: 10px;">
            <input type="text" id="seeker-keyword" placeholder="Enter skills (e.g. PHP, Java) or candidate name..." class="form-control" style="flex: 1;">
            <button onclick="searchSeekers()" class="btn-primary">Search Talent</button>
        </div>
    </div>

    <div class="job-list" id="seeker-results">
        <div class="job-card" style="text-align: center; color: #777;">
            <p>Start typing above to find matching candidates.</p>
        </div>
    </div>
</div>

<!-- AJAX Implementation for Seeker Search -->
<script>
function searchSeekers() {
    let keyword = document.getElementById('seeker-keyword').value;
    let resultsDiv = document.getElementById('seeker-results');

    if (keyword.length < 1) {
        resultsDiv.innerHTML = '<div class="job-card"><p>Please enter a search term.</p></div>';
        return;
    }

    resultsDiv.innerHTML = '<div class="job-card"><p>Searching talent pool...</p></div>';

    let xhr = new XMLHttpRequest();
    // Using the RecruiterController API endpoint
    xhr.open("GET", "../../api/search-seekers.php?keyword=" + encodeURIComponent(keyword), true);

    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            let seekers = JSON.parse(this.responseText);
            let html = "";

            if (seekers.length > 0) {
                seekers.forEach(s => {
                    html += `
                        <div class="job-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <h3>${s.name}</h3>
                                    <p><strong>Headline:</strong> ${s.headline || 'No headline set'}</p>
                                    <p><strong>Skills:</strong> ${s.skills}</p>
                                    <p class="job-meta">Experience: ${s.years_experience} Years</p>
                                </div>
                                <div style="text-align: right;">
                                    <button class="btn-primary" style="padding: 5px 15px; font-size: 13px;">View Profile</button>
                                    <p style="margin-top: 10px;"><a href="mailto:${s.email}" style="color: #e8491d; text-decoration: none; font-size: 13px;">Contact Candidate</a></p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="job-card"><p>No candidates found matching your criteria.</p></div>';
            }
            resultsDiv.innerHTML = html;
        }
    };
    xhr.send();
}

// Allow pressing "Enter" to search
document.getElementById('seeker-keyword').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        searchSeekers();
    }
});
</script>

<?php include '../partials/footer.php'; ?>