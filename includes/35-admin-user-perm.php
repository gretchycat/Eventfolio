<?php

if (!function_exists('ef_admin_shared_styles')) {
function ef_admin_shared_styles() {
    echo '<style>
/* Shared Admin Styles for Eventfolio Plugin */
.ef-category-list,
.ef-user-perm-list {
    display: flex;
    flex-direction: column;
    gap: 0.25em;
    width: 100%;
}

.ef-category-row,
.ef-user-perm-row {
    display: grid;
    grid-template-columns: 1.5fr 2fr 1.5fr 3fr 2fr;
    gap: 0.5em;
    align-items: center;
    padding: 0.4em 0.2em;
    border-bottom: 1px solid #222;
}

.ef-category-header,
.ef-user-perm-header {
    font-weight: bold;
    background: #111;
    border-bottom: 2px solid #333;
}

.ef-category-col,
.ef-user-perm-col {
    min-width: 0;
    overflow-wrap: anywhere;
}

.ef-category-actions a,
.ef-user-perm-actions a,
.ef-user-perm-actions button {
    margin-right: 0.5em;
}

@media (max-width: 800px) {
    .ef-category-row,
    .ef-user-perm-row {
        grid-template-columns: 1fr 1fr 1fr;
        font-size: 0.95em;
    }
}
    </style>';
}
}

<?php
if (!defined('ABSPATH')) exit;

function ef_admin_user_permissions_page()
{
    // Ensure all users + guest exist in the permissions table
    ef_sync_user_permissions_table();

    // Handle POST (add/edit/save permissions)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['perm_action']) && check_admin_referer('ef_user_perm_form'))
    {
        $user_id     = intval($_POST['user_id']);
        $permissions = sanitize_text_field($_POST['permissions']);

        if ($_POST['perm_action'] === 'save' && $user_id >= 0) {
            ef_update_user_permissions($user_id, $permissions);
        } elseif ($_POST['perm_action'] === 'delete' && $user_id > 0) {
            ef_delete_user_permissions($user_id); // implement this in db.php
        }
        // No redirect; just reload page to show updated state
    }

    // Handle Edit mode via GET
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

    ef_admin_shared_styles();
    // --- Render Admin Nav ---
    ef_admin_nav();

    echo '<h2>User Permissions</h2>';
    echo '<div class="ef-user-perm-list">';
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


