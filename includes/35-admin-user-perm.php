<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('ef_user_perm_header_row'))
{
function ef_user_perm_header_row()
    {
    echo <<<EOF
    <div class="eventfolio-row eventfolio-header eventfolio-row-viewer">
        <div class="eventfolio-col eventfolio-col-user">User</div>
        <div class="eventfolio-col eventfolio-col-username">Username</div>
        <div class="eventfolio-col eventfolio-col-permissions">Permissions</div>
        <div class="eventfolio-col eventfolio-col-updated">Updated</div>
        <div class="eventfolio-col eventfolio-actions"></div>
    </div>
    EOF;
    }
}

function ef_admin_user_permissions_page()
{
    // Ensure all users + guest exist in the permissions table
    ef_sync_user_permissions_table();
    $updated=false;
    // Handle POST (add/edit/save permissions)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perm_action']) && check_admin_referer('ef_user_perm_form'))
    {
        $user_id     = intval($_POST['user_id']);
        $permset=$_POST['user_permission_set'];
        $allperms=ef_get_role_definitions();
        $perms=$allperms[$permset];
        $csv_perms   = sanitize_text_field(implode(',', $perms));
        if ($_POST['perm_action'] === 'save' && $user_id >= 0)
        {
            $role = ef_best_matching_role($csv_perms);
            $final_perms = implode(',', $allperms[$role]);
            ef_update_user_permissions($user_id, $final_perms);
            $updated=true;
        }
    }
    $editing_id=-1;
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['perm_action']))
    {
        $user_id = intval($_GET['user_id']);
        if ($_GET['perm_action'] === 'reset' && $user_id >= 0)
        {
            ef_reset_user_permissions($user_id);
            $updated=true;
        }
        elseif ($_GET['perm_action'] === 'edit' && $user_id >= 0)
        {
            $editing_id=$user_id;
            $updated=true;
        }
    }
    // Get all users in WP
    $wp_users = get_users(['fields' => ['ID', 'display_name', 'user_login', 'user_email']]);
    // Get all permissions rows (including guest)
    $all_perms = ef_get_all_user_permissions(); // implement this in db.php

    // Map: user_id => [permissions, updated_at]
    $perms_by_id = [];
    foreach ($all_perms as $row)
    {
        $perms_by_id[intval($row->user_id)] = $row;
    }

    //ef_style();
    // --- Render Admin Nav ---
    ef_admin_nav('User Permissions');

    echo '<div class="eventfolio-list">';
    ef_user_perm_header_row();

    // Show guest row first
    if (isset($perms_by_id[0]))
    {
        ef_user_perm_viewer_row(0, [
            'display_name' => 'Guest (not logged in)',
            'user_login'   => 'guest',
            'permissions'  => $perms_by_id[0]->permissions,
            'updated_at'   => $perms_by_id[0]->updated_at
        ], $editing_id === 0 ? 'edit' : 'view');
    }

    // Show each user row
    foreach ($wp_users as $user)
    {
        $uid = intval($user->ID);
        $perms = isset($perms_by_id[$uid]) ? $perms_by_id[$uid]->permissions : '';
        ef_user_perm_viewer_row($uid, [
            'display_name' => $user->display_name ?: $user->user_login,
            'user_login'   => $user->user_login,
            'permissions'  => $perms,
            'updated_at'   => $perms_by_id[$uid]->updated_at ?? ''
        ], $editing_id === $uid ? 'edit' : 'view');
    }

    echo '</div>';
}


