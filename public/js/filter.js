document.querySelectorAll('.filter-input').forEach(input => {
    input.addEventListener('change', function() {
        const category = document.querySelector('#category-filter').value;
        const type = document.querySelector('#type-filter').value;
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', `../api/get_jobs.php?category=${category}&type=${type}`, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                document.querySelector('#job-listings').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    });
});