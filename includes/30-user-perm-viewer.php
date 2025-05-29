<?php
if (!defined('ABSPATH')) exit;

function ef_role_color($role)
{
    switch ($role) {
        case 'guest': return 'background:#222;color:#fff;'; // Black
        case 'user': return 'background:#207d25;color:#fff;'; // Green
        case 'organizer': return 'background:#ffb400;color:#222;'; // Yellow
        case 'admin': return 'background:#c00;color:#fff;'; // Red
        default: return 'background:#999;color:#fff;';
    }
}

if (!function_exists('ef_user_perm_header_row'))
{
function ef_user_perm_header_row()
    {
    echo <<<EOF
    <div class="ef-user-perm-row ef-user-perm-header">
        <div class="ef-user-perm-col">User</div>
        <div class="ef-user-perm-col">Username</div>
        <div class="ef-user-perm-col">Email</div>
        <div class="ef-user-perm-col">Permissions</div>
        <div class="ef-user-perm-col">Updated</div>
        <div class="ef-user-perm-col ef-user-perm-actions">Actions</div>
    </div>
    EOF;
    }
}

// Define role permission sets (central place for easy updates)
function ef_get_role_permission_sets()
{
    return [
        'guest'     => ['view_event'],
        'user'      => ['view_event', 'rsvp_event'],
        'organizer' => ['view_event', 'rsvp_event', 'create_event', 'edit_event', 'delete_event'],
        'admin'     => ['view_event', 'rsvp_event', 'create_event', 'edit_event', 'delete_event', 'manage_permissions']
    ];
}

// Determine role by strict or closest match
function ef_detect_user_role($perm_csv)
{
    $roles = ef_get_role_permission_sets();
    $perms = array_filter(array_map('trim', explode(',', strtolower($perm_csv))));
    // Empty or single 'view_event' => guest
    if (empty($perms) || (count($perms) === 1 && $perms[0] === 'view_event')) {
        return 'guest';
    }
    // Exact match
    foreach ($roles as $role => $set) {
        if (count($set) === count($perms) && !array_diff($set, $perms) && !array_diff($perms, $set)) {
            return $role;
        }
    }
    // Best overlap (fallback)
    $best_role = 'guest';
    $best_score = 0;
    foreach ($roles as $role => $set) {
        $score = count(array_intersect($set, $perms));
        if ($score > $best_score) {
            $best_score = $score;
            $best_role = $role;
        }
    }
    return $best_role;
}

// Viewer row
if (!function_exists('ef_user_perm_viewer_row'))
{
    if (!function_exists('ef_user_perm_viewer_row'))
    {
function ef_user_perm_viewer_row($user_id, $user, $mode = 'view') {
    if ($mode === 'edit') 
    {
        ef_user_perm_editor_row($user_id, $user);
        return;
    }

    // Role/color logic
    $roles = ef_get_role_definitions();
    $role = ef_best_matching_role($user['permissions'], $roles);
    $role_style = ef_role_color($role);

    echo '<div class="ef-user-perm-row">';
    echo '<div class="ef-user-perm-col">'. esc_html($user['display_name']).'</div>';
    echo '<div class="ef-user-perm-col">'. esc_html($user['user_login']).'</div>';
    echo '<div class="ef-user-perm-col">'. esc_html($user['user_email']).'</div>';
    echo '<div class="ef-user-perm-col">';
    echo '<span style="padding:2px 10px; border-radius:12px; font-weight:bold; '.$role_style.'">';
    echo ucfirst($role);
    echo '</span></div>';
    echo '<div class="ef-user-perm-col">'. esc_html($user['permissions']).'</div>';
    echo '<div class="ef-user-perm-col">'.esc_html($user['updated_at']).'</div>';
    echo '<div class="ef-user-perm-col ef-user-perm-actions"><a href="'. esc_url(add_query_arg(['edit' => $user_id])) .'">Edit</a>';
    if ($user_id !== 0)
        echo '<a href="'.esc_url(add_query_arg(['delete' => $user_id])).'" onclick="return confirm(\'Delete user permissions for '.esc_attr($user['display_name']).'?\');">Delete</a>';
     echo '</div></div>';
}
    }
}

function ef_user_perm_viewer_row_broken($user_id, $user, $perm_csv)
{
    $role = ef_detect_user_role($perm_csv);

    // Assign color per role
    $role_colors = [
        'guest'     => 'background:#222; color:#fff;',
        'user'      => 'background:#065f46; color:#fff;',      // green
        'organizer' => 'background:#eab308; color:#111;',      // yellow
        'admin'     => 'background:#b91c1c; color:#fff;',      // red
    ];

    $role_label = ucfirst($role);
    $style = isset($role_colors[$role]) ? $role_colors[$role] : '';

    echo '<div class="ef-user-row">';
    echo '<div class="ef-user-col ef-user-perm"><span class="ef-role-label" style="padding:2px 12px;border-radius:12px;font-weight:600;'.$style.'">' . esc_html($role_label) . '</span></div>';
    echo '<div class="ef-user-col ef-user-name">' . esc_html($user->display_name ?? 'guest') . '</div>';
    echo '<div class="ef-user-col ef-user-login">' . esc_html($user->user_login ?? 'guest') . '</div>';
    echo '<div class="ef-user-col ef-user-email">' . esc_html($user->user_email ?? '') . '</div>';
    echo '<div class="ef-user-col ef-user-actions">';
    echo '<a href="?page=eventfolio_user_permissions&edit=' . intval($user_id) . '">Edit</a>';
    // Only allow delete for real users, not guest
    if ($user_id > 0) {
        echo ' <a href="?page=eventfolio_user_permissions&delete=' . intval($user_id) . '" style="color:#b91c1c;">Delete</a>';
    }
    echo '</div>';
    echo '</div>';
}

