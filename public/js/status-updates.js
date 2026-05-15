/**
 * public/js/status-updates.js
 * Handles AJAX updates for Job Status (Employer) and Applicant Status (Employer/Recruiter).
 * Reference: Page 55 (AJAX) and Page 64 (JSON) of course notes.
 */

/**
 * Toggles a job between 'active' and 'closed'
 * @param {number} jobId 
 * @param {string} currentStatus 
 */
function toggleJobStatus(jobId, currentStatus) {
    const newStatus = (currentStatus === 'active') ? 'closed' : 'active';
    const statusBadge = document.getElementById('status-badge-' + jobId);
    const toggleBtn = document.getElementById('toggle-btn-' + jobId);

    const xhr = new XMLHttpRequest();
    // Sending request to EmployerController via an API endpoint
    xhr.open("GET", "../../api/update-status.php?job_id=" + jobId + "&status=" + newStatus, true);

    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const response = JSON.parse(this.responseText);
            if (response.success) {
                // Update UI dynamically (Page 55 of notes)
                statusBadge.innerHTML = newStatus.toUpperCase();
                statusBadge.className = 'badge ' + (newStatus === 'active' ? 'bg-success' : 'bg-danger');

                // Update button text for the next click
                toggleBtn.onclick = function () { toggleJobStatus(jobId, newStatus); };
                toggleBtn.innerText = (newStatus === 'active' ? 'Close Job' : 'Reopen Job');

                alert("Job status updated to " + newStatus);
            } else {
                alert("Failed to update job status.");
            }
        }
    };
    xhr.send();
}

/**
 * Updates the application status of a candidate (e.g., Shortlisted, Interview)
 * Used in the Employer/Recruiter applicant list dropdowns.
 * @param {number} applicationId 
 * @param {string} newStatus 
 */
function updateApplicantStatus(applicationId, newStatus) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../../api/update-status.php", true);

    // Setting header for POST data (Page 39 of notes)
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const response = JSON.parse(this.responseText);
            if (response.success) {
                const row = document.getElementById('app-row-' + applicationId);
                row.style.backgroundColor = "#e8f5e9"; // Visual confirmation (Green tint)
                setTimeout(() => { row.style.backgroundColor = ""; }, 2000);
            } else {
                alert("Error updating status: " + (response.message || "Unknown error"));
            }
        }
    };

    // Sending parameters (Page 40 of notes)
    xhr.send("application_id=" + applicationId + "&status=" + encodeURIComponent(newStatus));
}