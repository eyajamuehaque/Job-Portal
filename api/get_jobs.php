<?php
require_once '../app/core/Database.php';
$db = (new Database())->db;

$category = $_GET['category'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM jobs WHERE status = 'active'";
if($category) $sql .= " AND category_id = '$category'";
if($type) $sql .= " AND job_type = '$type'";
if($search) $sql .= " AND (title LIKE '%$search%' OR company_name LIKE '%$search%')";

$result = mysqli_query($db, $sql);

if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        echo "<div class='job-card'>
                <h3>{$row['title']}</h3>
                <p>{$row['company_name']} - {$row['job_type']}</p>
                <a href='job_details.php?id={$row['id']}'>View Details</a>
              </div>";
    }
} else {
    echo "<p>No jobs found.</p>";
}
?>