<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// --- Admin navigation ---
function ef_admin_nav()
{
    $pages = [
        'eventfolio'                => 'Info',
        'eventfolio_events'         => 'Events',
        'eventfolio_categories'     => 'Categories',
        'eventfolio_permissions'    => 'User Permissions'
    ];
    echo '<div class="ef-admin-nav" style="margin-bottom:1em;">';
    foreach ($pages as $slug => $label)
    {
        $url = admin_url('admin.php?page=' . $slug);
        echo '<a href="' . esc_url($url) . '" class="button" style="margin-right:0.5em;">' . esc_html($label) . '</a>';
    }
    echo '</div>';
}

// --- Splash/info/settings main page ---
function ef_admin_info_page()
{
    ef_admin_nav();

    // Adjust this path as needed if file moves to a subdir
    $readme_path = dirname(dirname(__FILE__)) . '/README.md';
    $readme = file_exists($readme_path)
        ? file_get_contents($readme_path)
        : 'README.md not found.';

    // Load Parsedown if not already loaded
    if (!class_exists('Parsedown'))
    {
        require_once dirname(__FILE__) . '/lib/Parsedown/Parsedown.php';
    }

    $Parsedown = new Parsedown();
    echo '<div class="ef-readme">';
    echo $Parsedown->text($readme);
    echo '</div>';
}

