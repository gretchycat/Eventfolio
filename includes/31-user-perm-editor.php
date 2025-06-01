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

    echo '<form method="post">';
    ob_start();
    wp_nonce_field('ef_user_perm_form');
    echo ob_get_clean();
    echo '<div class="eventfolio-row eventfolio-row-viewer">';
    echo '<div class="eventfolio-col eventfolio-col-user">'. esc_html($user['display_name']).'</div>';
    echo '<div class="eventfolio-col eventfolio-col-username">'. esc_html($user['user_login']).'</div>';
    echo '<div class="eventfolio-col eventfolio-col-permissions">';
    echo '<select name="user_permission_set">';
    foreach ($perm_sets as $role => $perms)
    {
        $label = ucfirst($role);
        echo '<option value="' . esc_attr($role) . '"'
            . ($current_role === $role ? ' selected' : '') . '>'
            . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '</div>';
    echo '<div class="eventfolio-col eventfolio-col-updated">'.esc_html($user['updated_at']).'</div>';
    echo '<div class="eventfolio-col eventfolio-col-actions">';
    echo '<button type="submit" name="perm_action" value="save" class="eventfolio-link-btn">Save</button>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=eventfolio_user_permissions')) . '" class="eventfolio-link-btn">Cancel</a>';
    echo '</div>';
    echo '</div>';
    echo '<input type="hidden" name="user_id" value="' . esc_attr($user_id) . '">';
    wp_nonce_field('ef_userperm_editor', 'ef_userperm_nonce');
    echo '</form>';
}
}
