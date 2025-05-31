<?php
if (!function_exists('ef_category_viewer_row'))
{
    function ef_category_viewer_row($cat)
    {
        if (!$cat) return;
        echo '<div class="ef-row ef-viewer">';
        echo '<div class="ef-col ef-col-name">'.esc_html($cat->name).'</div>';
        echo ' <div class="ef-col ef-col-visibility">'.esc_html(ucfirst($cat->visibility ?? '')).'</div>';
        echo '<div class="ef-col ef-col-description">'.esc_html($cat->description).'</div>';
        echo '<div class="ef-col ef-col-actions">';
        echo'<a href="?page=eventfolio_categories&edit='.intval($cat->id).'" class="ef-btn ef-btn-edit">Edit</a>&nbsp;';
        echo '<a href="?page=eventfolio_categories&delete='.intval($cat->id).'" class="ef-btn ef-btn-delete" onclick="return confirm(\'Delete this category?\');">Delete</a>';
        echo '</div></div>';
    }
}
