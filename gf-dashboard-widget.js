document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('gf_forms_dropdown');
    const displayArea = document.getElementById('gf_emails_display');
    const downloadSection = document.querySelector('.gf-report-section');
    const startDateInput = document.getElementById('gf_start_date');
    const endDateInput = document.getElementById('gf_end_date');
    const downloadBtn = document.querySelector('.gf-download-btn');

    function fetchDataForForm(formId) {
        // Use AJAX to fetch the data
        // Fetch the entries count for today
        fetch(gfDashboardWidget.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_form_entries_today&form_id=' + encodeURIComponent(formId)
        })
            .then(response => response.json())
			.then(data => {
                const entriesDisplay = document.getElementById('gf_entries_today_display');
                entries.textContent = "Entries Today: " + data.count;
            });


        fetch(gfDashboardWidget.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=get_form_emails&form_id=' + encodeURIComponent(formId)
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

    // Handle download button click
    downloadBtn.addEventListener('click', function () {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const formId = dropdown.value;

        // Redirect to download URL
        window.location.href = gfDashboardWidget.ajaxurl +
            '?action=download_form_entries' +
            '&form_id=' + encodeURIComponent(formId) +
            '&start_date=' + encodeURIComponent(startDate) +
            '&end_date=' + encodeURIComponent(endDate);
    });
});
