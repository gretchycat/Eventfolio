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

// Define roles and their canonical permissions
function ef_get_role_definitions()
{
    return [
        'guest' => ['view_teaser'],
        'user' => ['view_event', 'rsvp'],
        'organizer' => ['view_event', 'rsvp', 'create_event', 'edit_event', 'delete_event', 'assign_category', 'manage_own_rsvps'],
        'admin' => ['view_event', 'rsvp', 'create_event', 'edit_event', 'delete_event', 'assign_category', 'manage_own_rsvps', 'manage_all_rsvps', 'manage_permissions', 'manage_categories', 'manage_users']
    ];
}

function ef_role_priority($role) {
    $priority = ['guest' => 0, 'user' => 1, 'organizer' => 2, 'admin' => 3];
    return $priority[$role] ?? -1;
}

function ef_best_matching_role($csv_perms, $role_definitions)
{
    $user_perms = array_filter(array_map('trim', explode(',', $csv_perms)));
    $best_role = 'guest';
    $max_overlap = 0;
    foreach ($role_definitions as $role => $perms)
    {
        $overlap = count(array_intersect($user_perms, $perms));
        if ($overlap > $max_overlap || ($overlap == $max_overlap && ef_role_priority($role) > ef_role_priority($best_role)))
        {
            $best_role = $role;
            $max_overlap = $overlap;
        }
    }
    return $best_role;
}

function ef_get_permission_sets()
{
    static $sets = null;

    if (is_null($sets))
    {
        // This array is your canonical source.
        // Extend as needed (labels, descriptions, etc.)
        $sets = [
            'guest' => [
                'label' => 'Guest',
                'permissions' => ['view_teaser'],
            ],
            'user' => [
                'label' => 'Validated User',
                'permissions' => ['view_event', 'rsvp'],
            ],
            'organizer' => [
                'label' => 'Organizer',
                'permissions' => [
                    'view_event',
                    'rsvp',
                    'create_event',
                    'edit_event',
                    'delete_event',
                    'manage_categories',
                ],
            ],
            'admin' => [
                'label' => 'Admin',
                'permissions' => ['*'],
            ],
        ];
    }

    return $sets;
}
