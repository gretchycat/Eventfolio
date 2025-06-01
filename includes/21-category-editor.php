<?php
if (!function_exists('ef_category_editor_row'))
{
    function ef_category_editor_row($cat, $mode = 'add')
    {
        if (!$cat)
        {
            $cat = (object) ['id'=>0, 'slug'=>'', 'name'=>'', 'visibility'=>'public', 'description'=>''];
        }
        echo '<form method="post" class="eventfolio-row eventfolio-editor">';
        // Add nonce field
        ob_start();
        wp_nonce_field('ef_category_form');
        echo ob_get_clean();

        echo '<input type="hidden" name="cat_id" value="' . intval($cat->id) . '">';

        // Name input
        echo '<div class="eventfolio-col eventfolio-col-name">';
        echo '<input class="eventfolio-input" type="text" name="cat_name" value="' . esc_attr($cat->name) . '" required>';
        echo '</div>';

        // Visibility dropdown
        echo '<div class="eventfolio-col eventfolio-col-visibility">';
        echo '<select class="eventfolio-select" name="cat_visibility" required>';
        echo '<option value="public"' . selected($cat->visibility, 'public', false) . '>Public</option>';
        echo '<option value="private"' . selected($cat->visibility, 'private', false) . '>Private</option>';
        echo '</select>';
        echo '</div>';

        // Description input
        echo '<div class="eventfolio-col eventfolio-col-description">';
        echo '<input class="eventfolio-input" type="text" name="cat_description" value="' . esc_attr($cat->description) . '">';
        echo '</div>';

        // Actions
        echo '<div class="eventfolio-col eventfolio-col-actions">';
        echo '<button class="eventfolio-btn ef-btn-save" type="submit" name="cat_action" value="' . ($mode === 'add' ? 'add' : 'save') . '">';
        echo ($mode === 'add' ? 'Add' : 'Save');
        echo '</button>';
        if ($mode === 'edit')
        {
            echo '&nbsp;<a href="?page=eventfolio_categories" class="eventfolio-btn ef-btn-cancel">Cancel</a>';
        }
        echo '</div>';

        echo '</form>';
    }
}
