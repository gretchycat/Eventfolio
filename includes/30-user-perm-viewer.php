<?php
if (!defined('ABSPATH')) exit;

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
    ?>
    <div class="ef-user-perm-row">
        <div class="ef-user-perm-col"><?php echo esc_html($user['display_name']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_login']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_email']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['permissions']); ?></div>
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
