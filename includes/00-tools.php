<?php

// --- Admin navigation ---
function ef_admin_nav($selected='')
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
        $class='ef-admin-nav';
        if($label == $selected)
            $class='ef-admin-nav-selected';
        echo '<a href="' . esc_url($url) . '" class="button '.$class.'" style="margin-right:0.5em;">' . esc_html($label) . '</a>';
    }
    echo '</div>';
}

function ef_style()
{
    add_action('admin_enqueue_scripts', 'ef_enqueue_admin_css');
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

function ef_best_matching_role($csv_perms)
{
    $role_definitions=ef_get_role_definitions();
    $user_perms = explode(',', $csv_perms);
    $best_role='';
    if(strlen($csv_perms)==0)
    {
        $count=999;
        foreach ($role_definitions as $role => $perms)
        {
            $rc=count($perms);
            if ($rc<$count)
            {
                $count=$rc;
                $best_role=$role;
            }
        }
    }
    else
    {
        $best_score=999.0; //0 is best
        $common=0;
        foreach ($role_definitions as $role => $perms)
        {
            $overlap=count(array_intersect($user_perms, $perms));
            $count=count($perms);
            $ucount=count($user_perms);
            $score=abs(((float)$overlap/(float)$count)-1.0);
            if ($best_role=='')
                $best_role=$role;
            error_log('---------');
            error_log('Testing '.$role);
            error_log('Counts: ov.'.$overlap.' rl.'.$count);
            error_log('User: '.implode(',', $user_perms));
            error_log('Role: '.implode(',', $perms));
            error_log('Score: '.$score);
 
            if ($score <= $best_score)
            {
                if ($overlap>$common)
                {
                    $common=$overlap;
                    $best_score=$score;
                    $best_role=$role;
                }
            }
        }
    }
    return $best_role;
}

