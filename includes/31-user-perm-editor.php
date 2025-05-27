<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_user_perm_editor_row')) {
function ef_user_perm_editor_row($user_id, $user) {
    ?>
    <form method="post" class="ef-user-perm-row">
        <?php wp_nonce_field('ef_user_perm_form'); ?>
        <input type="hidden" name="user_id" value="<?php echo intval($user_id); ?>">
        <input type="hidden" name="perm_action" value="save">
        <div class="ef-user-perm-col"><?php echo esc_html($user['display_name']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_login']); ?></div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['user_email']); ?></div>
        <div class="ef-user-perm-col">
            <input type="text" name="permissions" value="<?php echo esc_attr($user['permissions']); ?>" style="width: 100%">
            <small>CSV: view_event,edit_event,...</small>
        </div>
        <div class="ef-user-perm-col"><?php echo esc_html($user['updated_at']); ?></div>
        <div class="ef-user-perm-col ef-user-perm-actions">
            <button class="button button-primary" type="submit">Save</button>
            <a href="<?php echo esc_url(remove_query_arg('edit')); ?>">Cancel</a>
        </div>
    </form>
    <?php
}}
