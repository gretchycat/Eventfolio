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

// Determine role by strict or closest match
function ef_detect_user_role($perm_csv)
{
    $roles = ef_get_permission_sets();
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
    function ef_user_perm_viewer_row($user_id, $user, $mode = 'view') 
    {
        if ($mode === 'edit')
        {
            ef_user_perm_editor_row($user_id, $user);
            return;
        }

        // Role/color logic
        $roles = ef_get_role_definitions();
        $role = ef_best_matching_role($user['permissions'], $roles);
        $role_style = ef_role_color($role);

        echo '<div class="eventfolio-row eventfolio-row-viewer">';
        echo '<div class="eventfolio-col eventfolio-col-user">'. esc_html($user['display_name']).'</div>';
        echo '<div class="eventfolio-col eventfolio-col-username">'. esc_html($user['user_login']).'</div>';
        echo '<div class="eventfolio-col eventfolio-col-permissions">';
        echo '<span style="padding:2px 10px; border-radius:12px; font-weight:bold; '.$role_style.'">';
        echo ucfirst($role);
        echo '</span></div>';
        //echo '<div class="eventfolio-col">'. esc_html($user['permissions']).'</div>';
        echo '<div class="eventfolio-col eventfolio-col-updated">'.esc_html($user['updated_at']).'</div>';
        echo '<div class="eventfolio-col eventfolio-actions">';
        echo '<a href="'.esc_url(add_query_arg(['user_id' => $user_id, 'perm_action' => 'edit'])) .'">Edit</a>&nbsp;';
        echo '<a href="'.esc_url(add_query_arg(['user_id' => $user_id, 'perm_action' => 'reset'])) .'">Reset</a>';
        echo '</div></div>';
    }
}
