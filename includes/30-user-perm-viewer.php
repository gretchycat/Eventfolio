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

if (!function_exists('ef_user_perm_header_row')) {
function ef_user_perm_header_row() {
    ?>
    <div class="ef-user-perm-row ef-user-perm-header">
        <div class="ef-user-perm-col">User</div>
        <div class="ef-user-perm-col">Username</div>
        <div class="ef-user-perm-col">Email</div>
        <div class="ef-user-perm-col">Permissions</div>
        <div class="ef-user-perm-col">Updated</div>
        <div class="ef-user-perm-col ef-user-perm-actions">Actions</div>
    </div>
    <?php
}}

if (!function_exists('ef_user_perm_viewer_row')) {
function ef_user_perm_viewer_row($user_id, $user, $mode = 'view') {
    if ($mode === 'edit') {
        ef_user_perm_editor_row($user_id, $user);
        return;
    }

    // Role/color logic
    $roles = ef_get_role_definitions();
    $role = ef_best_matching_role($user['permissions'], $roles);
    $role_style = ef_role_color($role);

    ?>
    <div class="ef-user-perm-row">
        <div class="ef-user-perm-col"><?php echo esc_html($user['display_name']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_login']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_email']); ?></div>
        <div class="ef-user-perm-col">
            <span style="padding:2px 10px; border-radius:12px; font-weight:bold; <?php echo $role_style; ?>">
                <?php echo ucfirst($role); ?>
            </span>
        </div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['updated_at']); ?></div>
        <div class="ef-user-perm-col ef-user-perm-actions">
            <a href="<?php echo esc_url(add_query_arg(['edit' => $user_id])); ?>">Edit</a>
            <?php if ($user_id !== 0): ?>
            <a href="<?php echo esc_url(add_query_arg(['delete' => $user_id])); ?>" onclick="return confirm('Delete user permissions for <?php echo esc_attr($user['display_name']); ?>?');">Delete</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}}
