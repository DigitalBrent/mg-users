<?php
// ... Full previous PHP Code remains same except for additional AJAX callback (below)...

// Handle the AJAX request to download entries
function download_form_entries_callback() {
    $form_id = intval($_POST['form_id']);

    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);

    if(empty($start_date) || empty($end_date)){
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
    }

    $search_criteria = array(
        'start_date' => $start_date,
        'end_date' => $end_date
    );

    // Get the form entries
    $entries = GFAPI::get_entries($form_id, $search_criteria);

    // Create a CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=gf_entries.csv');

    $out = fopen('php://output', 'w');

    $first = true;
    foreach($entries as $entry) {
        if ($first) {
            // Output header row
            fputcsv($out, array_keys($entry));
            $first = false;
        }

        // Output data row
        fputcsv($out, array_values($entry));
    }

    fclose($out);
    exit;
}
add_action('wp_ajax_download_form_entries', 'download_form_entries_callback');
// Rest of the PHP code ...
?>
