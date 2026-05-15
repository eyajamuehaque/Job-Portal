document.querySelectorAll('.filter-input').forEach(input => {
    input.addEventListener('change', function() {
        const category = document.querySelector('#category-filter').value;
        const type = document.querySelector('#type-filter').value;
        
        fetch(`../api/get_jobs.php?category=${category}&type=${type}`)
            .then(res => res.text())
            .then(data => {
                document.querySelector('#job-listings').innerHTML = data;
            });
    });
});