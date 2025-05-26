<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

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

