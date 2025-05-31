<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('ef_user_perm_header_row'))
{
function ef_user_perm_header_row()
    {
    echo <<<EOF
    <div class="ef-row ef-header">
        <div class="ef-col ef-col-user">User</div>
        <div class="ef-col ef-col-username">Username</div>
        <div class="ef-col ef-col-email"></div>
        <div class="ef-col ef-col-permissions">Permissions</div>
        <div class="ef-col ef-col-updated">Updated</div>
        <div class="ef-col ef-actions">Actions</div>
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
        $csv_perms   = sanitize_text_field($_POST['permissions']);
        $roles = ef_get_role_definitions();
        if ($_POST['perm_action'] === 'save' && $user_id >= 0)
        {
            $role = ef_best_matching_role($csv_perms, $roles);
            $final_perms = implode(',', $roles[$role]);
            ef_update_user_permissions($user_id, $final_perms);
            $updated=true;
        }
        elseif ($_POST['perm_action'] === 'reset' && $user_id >= 0)
        {
           ef_reset_user_permissions($user_id);
        }
        elseif ($_POST['perm_action'] === 'delete' && $user_id >= 0)
        {
            ef_delete_user_permissions($user_id); // only if you want manual delete
            $updated=true;
        }
        // Just reload to show updated
    }
    // Handle Edit mode via GET
    $editing_id=-1;
    if(!$updated)
        $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : -1;
    $adding_new = isset($_GET['add']) && $editing_id < 0;

    // Get all users in WP
    $wp_users = get_users(['fields' => ['ID', 'display_name', 'user_login', 'user_email']]);
    // Get all permissions rows (including guest)
    $all_perms = ef_get_all_user_permissions(); // implement this in db.php

    // Map: user_id => [permissions, updated_at]
    $perms_by_id = [];
    foreach ($all_perms as $row) {
        $perms_by_id[intval($row->user_id)] = $row;
    }

    et_style();
    // --- Render Admin Nav ---
    ef_admin_nav();

    echo '<h2>User Permissions</h2>';
    echo '<div class="ef-list">';
    ef_user_perm_header_row();

    // Show guest row first
    if (isset($perms_by_id[0])) {
        ef_user_perm_viewer_row(0, [
            'display_name' => 'Guest (not logged in)',
            'user_login'   => 'guest',
            'user_email'   => '',
            'permissions'  => $perms_by_id[0]->permissions,
            'updated_at'   => $perms_by_id[0]->updated_at
        ], $editing_id === 0 ? 'edit' : 'view');
    }

    // Show each user row
    foreach ($wp_users as $user) {
        $uid = intval($user->ID);
        $perms = isset($perms_by_id[$uid]) ? $perms_by_id[$uid]->permissions : '';
        ef_user_perm_viewer_row($uid, [
            'display_name' => $user->display_name ?: $user->user_login,
            'user_login'   => $user->user_login,
            'user_email'   => $user->user_email,
            'permissions'  => $perms,
            'updated_at'   => $perms_by_id[$uid]->updated_at ?? ''
        ], $editing_id === $uid ? 'edit' : 'view');
    }

    echo '</div>';
}


