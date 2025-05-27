<?php

// --- Admin navigation ---
function ef_admin_nav()
{
    $pages = [
        'eventfolio'                => 'Info',
        'eventfolio_events'         => 'Events',
        'eventfolio_categories'     => 'Categories',
        'eventfolio_user_permissions'    => 'User Permissions'
    ];
    echo '<div class="ef-admin-nav" style="margin-bottom:1em;">';
    foreach ($pages as $slug => $label)
    {
        $url = admin_url('admin.php?page=' . $slug);
        echo '<a href="' . esc_url($url) . '" class="button" style="margin-right:0.5em;">' . esc_html($label) . '</a>';
    }
    echo '</div>';
}


