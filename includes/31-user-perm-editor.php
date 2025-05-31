<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_user_perm_editor_row'))
{
function ef_user_perm_editor_row($user_id, $user)
{
// Get current permissions CSV from database for this user
    $current_perm_csv = ef_get_user_permissions($user_id);

    // (rest of the function as before...)
    $perm_sets = ef_get_permission_sets();

    // Detect current role by exact permission match
    $current_role = 'custom';
    $perms_array = array_map('trim', explode(',', $current_perm_csv));
    foreach ($perm_sets as $role => $perms)
    {
        // '*' means admin, otherwise check for exact match
        if ($perms === ['*'] && $perms_array === ['*'])
        {
            $current_role = $role;
            break;
        }
        elseif (count($perms) && !array_diff($perms, $perms_array) && !array_diff($perms_array, $perms))
        {
            $current_role = $role;
            break;
        }
    }

    echo '<div class="ef-row">';
    echo '<form method="post">';
    echo '<div class="ef-col ef-col-username">' . esc_html($user->user_login ?? 'guest') . '</div>';
    echo '<div class="ef-col ef-col-email">'   . esc_html($user->user_email ?? '') . '</div>';

    echo '<div class="ef-col ef-col-permissions">';
    echo '<select name="user_permission_set">';
    foreach ($perm_sets as $role => $perms)
    {
        $label = ucfirst($role);
        echo '<option value="' . esc_attr($role) . '"'
            . ($current_role === $role ? ' selected' : '') . '>'
            . esc_html($label) . '</option>';
    }
    if ($current_role === 'custom')
    {
        echo '<option value="custom" selected>Custom</option>';
    }
    echo '</select>';
    echo '</div>';

    echo '<div class="ef-col ef-col-actions">';
    echo '<button type="submit" name="perm_action" value="save" class="ef-link-btn">Save</button>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=eventfolio_user_permissions')) . '" class="ef-link-btn">Cancel</a>';
    echo '</div>';

    echo '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">';
    wp_nonce_field('ef_userperm_editor', 'ef_userperm_nonce');
    echo '</form>';
    echo '</div>';
}
}
