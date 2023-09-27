document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('gf_forms_dropdown');
    const displayArea = document.getElementById('gf_emails_display');

    function fetchDataForForm(formId) {
        // Use AJAX to fetch the data
        // Fetch the entries count for today
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_form_entries_today&form_id=' + formId
        })
            .then(response => response.json())
            .then(data => {
                const entriesDisplay = document.getElementById('gf_entries_today_display');
                entriesDisplay.textContent = "Entries Today: " + data.count;
            });


        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_form_emails&form_id=' + formId
        })
            .then(response => response.json())
            .then(data => {
                displayArea.innerHTML = data.html;
            });
    }

    dropdown.addEventListener('change', function () {
        fetchDataForForm(dropdown.value);
    });

    // Fetch data for the initial form on page load
    fetchDataForForm(dropdown.value);
});
