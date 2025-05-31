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

function et_style()
{
    echo <<<EOF
<style>
.ef-list{
    display: flex;
    flex-direction: column;
    gap: 0;
}

.ef-row{
    display: flex;
    flex-direction: row;
    align-items: stretch;
    height: 32px;
    border-bottom: 1px solid #444;
}

.ef-header{
    font-weight: bold;
    background: rgba(0,0,0,0.1);
}

.ef-col{
    padding: 0;
    margin: 0;
    min-width: 0;
    word-break: break-all;
    display: flex;
    align-items: center;
}
.ef-col-user         { flex: 1 0 14%; min-width: 90px; }
.ef-col-username     { flex: 1 0 14%; min-width: 90px; }
.ef-col-email        { flex: 2 1 22%; min-width: 120px; }
.ef-col-permissions  { flex: 1 0 14%; min-width: 80px; text-align: center; }
.ef-col-updated      { flex: 1 0 18%; min-width: 90px; }
.ef-col-actions      { flex: 0 0 18%; min-width: 90px; text-align: right; }

/* Responsive: stack or scroll if too narrow */
@media (max-width: 600px){
    .ef-row, .ef-list { flex-direction: column;
    }
    .ef-col{
        width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box;
    }
}
.ef-col-name        { flex: 1 1 17%; }
.ef-col-visibility  { flex: 0 1 17%;  text-align: left; }
.ef-col-description { flex: 2 1 49%; }
.ef-col-actions     { flex: 0 0 17%;  text-align: right; }
.ef-col input[type="text"],
.ef-col textarea,
.ef-col select{
    width: 100% !important;
    padding: 0;
    margin: 0;
    min-width: 60px;
    box-sizing: border-box;
    font-size: inherit;
    background: rgba(0,0,0,0.1);
    border: 1px solid #333;
    border-radius: 4px;
}
/* Optional: tighten up space between columns if needed */
.ef-row{
    gap: 0.05em;
}
.ef-btn.ef-btn-save{
    background: none;
    border: none;
    color: #2196F3;         /* Same as your links */
    padding: 0;
    margin: 0;
    font: inherit;
    text-decoration: underline;
    cursor: pointer;
    transition: color 0.2s;
}
.ef-btn.ef-btn-save:hover,
.ef-btn.ef-btn-save:focus{
    color: #1976D2;         /* Slightly darker on hover, optional */
    text-decoration: underline;
}
</style>
EOF;
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
