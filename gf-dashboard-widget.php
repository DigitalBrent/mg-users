<?php
/*
Plugin Name: Gravity Forms Dashboard Widget
Description: A widget to list all the names of Gravity Forms and associated notification emails.
Version: 1.0
Author: Your Name
*/

// Hook into the 'wp_dashboard_setup' to register our widget.
add_action('wp_dashboard_setup', 'register_gf_dashboard_widget');

function register_gf_dashboard_widget() {
    wp_add_dashboard_widget(
        'gf_dashboard_widget',          // Widget slug.
        'Gravity Forms List',           // Title.
        'display_gf_dashboard_widget'   // Display function.
    );
}

function get_notification_emails($form_id) {
    $notification_emails = array(
        'to' => array(),
        'cc' => array(),
        'bcc' => array()
    );

    // Get the form object
    $form = GFAPI::get_form($form_id);

    // Check if the form has notifications
    if (isset($form['notifications']) && is_array($form['notifications'])) {
        foreach ($form['notifications'] as $notification) {
            if (isset($notification['toType']) && $notification['toType'] === 'email') {
                if (isset($notification['to'])) {
                    $notification_emails['to'][] = $notification['to'];
                }
                if (isset($notification['cc'])) {
                    $notification_emails['cc'][] = $notification['cc'];
                }
                if (isset($notification['bcc'])) {
                    $notification_emails['bcc'][] = $notification['bcc'];
                }
            }
        }
    }

    // Filter out any empty strings from the arrays
    $notification_emails['to'] = array_filter($notification_emails['to'], 'strlen');
    $notification_emails['cc'] = array_filter($notification_emails['cc'], 'strlen');
    $notification_emails['bcc'] = array_filter($notification_emails['bcc'], 'strlen');

    return $notification_emails;
}

function display_gf_dashboard_widget() {
    // Ensure Gravity Forms is active.
    if (class_exists('GFAPI')) {
        $forms = GFAPI::get_forms();

        if ($forms) {
            // Create dropdown with form names
            echo '<select id="gf_forms_dropdown">';
            foreach ($forms as $form) {
                echo '<option value="' . esc_attr($form['id']) . '">' . esc_html($form['title']) . '</option>';
            }
            echo '</select>';

            echo '<div class="gf-today-section">';
            echo '<span id="gf_entries_today_display">Entries Today:</span>';
            echo '<button class="gf-report-btn">Download Form Entry Report</button>';
            echo '</div>';

            // Container for the emails display
            echo '<h4 id="anr">Admin Notification Recipients</h4>';
            echo '<div id="gf_emails_display"></div>';

        } else {
            echo 'No forms found.';
        }
    } else {
        echo 'Gravity Forms is not active or not installed.';
    }
}

// Enqueue the JavaScript in the WordPress admin
function enqueue_admin_scripts() {
    wp_enqueue_style('gf-dashboard-widget-css', plugin_dir_url(__FILE__) . 'gf-dashboard-widget.css', array(), '1.0.0');
    wp_enqueue_script('gf-dashboard-widget-js', plugin_dir_url(__FILE__) . 'gf-dashboard-widget.js', array('jquery'), '1.0.0', true);

    // Add localized variables to the script
    wp_localize_script('gf-dashboard-widget-js', 'gfDashboardWidget', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');

// Handle the AJAX request
function get_form_emails_callback() {
    $form_id = intval($_POST['form_id']);
    $emails = get_notification_emails($form_id);

    ob_start();
    
    foreach (['to', 'cc', 'bcc'] as $type) {
        if (!empty($emails[$type])) {
            echo '<div><small>' . strtoupper($type) . ':</small></div>';
            echo '<ul class="gf-notification-emails">';
            foreach ($emails[$type] as $email) {
                echo '<li>' . esc_html($email) . '</li>';
            }
            echo '</ul>';
        }
    }

    $response = array('html' => ob_get_clean());
    echo json_encode($response);
    die();
}
add_action('wp_ajax_get_form_emails', 'get_form_emails_callback');

function get_form_entries_today_callback() {
    $form_id = intval($_POST['form_id']);
    
    $search_criteria['start_date'] = date('Y-m-d 00:00:00');
    $search_criteria['end_date'] = date('Y-m-d 23:59:59');
    
    $entries_count = GFAPI::count_entries($form_id, $search_criteria);
    
    echo json_encode(array('count' => $entries_count));
    die();
}
add_action('wp_ajax_get_form_entries_today', 'get_form_entries_today_callback');

?>
