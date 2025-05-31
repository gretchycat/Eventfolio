<?php
if (!function_exists('ef_category_viewer_row')) {
    function ef_category_viewer_row($cat)
    {
        if (!$cat) return;
        ?>
        <div class="ef-row ef-viewer">
            <div class="ef-col ef-col-name"><?php echo esc_html($cat->name); ?></div>
            <div class="ef-col ef-col-visibility"><?php echo esc_html(ucfirst($cat->visibility ?? '')); ?></div>
            <div class="ef-col ef-col-description"><?php echo esc_html($cat->description); ?></div>
            <div class="ef-col ef-col-actions">
                <a href="?page=eventfolio_categories&edit=<?php echo intval($cat->id); ?>" class="ef-btn ef-btn-edit">Edit</a>&nbsp;
                <a href="?page=eventfolio_categories&delete=<?php echo intval($cat->id); ?>" class="ef-btn ef-btn-delete" onclick="return confirm('Delete this category?');">Delete</a>
            </div>
        </div>
        <?php
    }
}
