<?php
if (!function_exists('ef_category_editor_row')) {
    function ef_category_editor_row($cat, $mode = 'add')
    {
        if (!$cat) $cat = (object) ['id'=>0, 'slug'=>'', 'name'=>'', 'visibility'=>'public', 'description'=>''];
        ?>
        <form method="post" class="ef-category-row ef-category-editor">
        <?php wp_nonce_field('ef_category_form'); ?>
            <input type="hidden" name="cat_id" value="<?php echo intval($cat->id); ?>">
            <div class="ef-category-col-name">
                <input class="ef-input" type="text" name="cat_name" value="<?php echo esc_attr($cat->name); ?>" required>
            </div>
            <div class="ef-category-colivisibility">
                <select class="ef-select" name="cat_visibility" required>
                    <option value="public" <?php selected($cat->visibility, 'public'); ?>>Public</option>
                    <option value="private" <?php selected($cat->visibility, 'private'); ?>>Private</option>
                </select>
            </div>
            <div class="ef-category-col-description">
                <input class="ef-input" type="text" name="cat_description" value="<?php echo esc_attr($cat->description); ?>">
            </div>
            <div class="ef-category-col-actions">
                <button class="ef-btn ef-btn-save" type="submit" name="cat_action" value="<?php echo $mode === 'add' ? 'add' : 'save'; ?>">
                    <?php echo $mode === 'add' ? 'Add' : 'Save'; ?>
                </button>
                <?php if ($mode === 'edit'): ?>
                    <a href="?page=eventfolio_categories" class="ef-btn ef-btn-cancel">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
        <?php
    }
}
