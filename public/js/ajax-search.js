/**
 * public/js/ajax-search.js
 * Implements AJAX-based job filtering for the Job Seeker.
 * Reference: Page 55 (AJAX) and Page 64 (JSON) of course notes.
 */

function searchJobs() {
    // 1. Get the search values from the input fields
    let keyword = document.getElementById('keyword').value;
    let category = document.getElementById('category').value;
    let resultsContainer = document.getElementById('job-results');

    // 2. Create the XMLHttpRequest object (Page 55 of notes)
    let xhr = new XMLHttpRequest();

    // 3. Prepare the URL with query parameters
    // We send 'ajax=1' so the Controller knows to return JSON
    let url = "index.php?ajax=1&keyword=" + encodeURIComponent(keyword) + "&category=" + encodeURIComponent(category);

    xhr.open("GET", url, true);

    // 4. Define what happens when the response is ready
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // 5. Parse the JSON response (Page 64 of notes)
            let jobs = JSON.parse(this.responseText);
            let html = "";

            if (jobs.length > 0) {
                // 6. Build the HTML dynamically
                jobs.forEach(function(job) {
                    html += `
                        <div class="job-card">
                            <h3>${job.title}</h3>
                            <p class="job-meta">
                                <strong>Location:</strong> ${job.location} | 
                                <strong>Type:</strong> <span style="color: #e8491d;">${job.job_type}</span>
                            </p>
                            <p>${job.description.substring(0, 180)}...</p>
                            <div style="margin-top: 15px;">
                                <a href="job_details.php?id=${job.id}" class="btn-primary" style="padding: 8px 18px; font-size: 14px;">View Details</a>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = "<div class='job-card'><p>No jobs found matching your criteria.</p></div>";
            }

            // 7. Update the page content without a refresh
            resultsContainer.innerHTML = html;
        }
    };

    // 8. Send the request
    xhr.send();
}

/**
 * Optional: Debounce function to prevent too many requests 
 * while the user is typing.
 */
let timeout = null;
document.getElementById('keyword').addEventListener('keyup', function() {
    clearTimeout(timeout);
    timeout = setTimeout(searchJobs, 500); // Wait 500ms after typing stops
});

document.getElementById('category').addEventListener('change', searchJobs);