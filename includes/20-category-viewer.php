<?php

if (!defined('ABSPATH')) exit;

if (!function_exists('ef_category_viewer_row'))
{
    function ef_category_viewer_row($cat)
    {
        if (!$cat) return;
        echo '<div class="eventfolio-row eventfolio-row-viewer">';
        echo    '<div class="eventfolio-col eventfolio-col-name">'.esc_html($cat->name).'</div>';
        echo    '<div class="eventfolio-col eventfolio-col-visibility">'.esc_html(ucfirst($cat->visibility ?? '')).'</div>';
        echo    '<div class="eventfolio-col eventfolio-col-description">'.esc_html($cat->description).'</div>';
        echo    '<div class="eventfolio-col eventfolio-col-actions">';
        echo        '<a href="?page=eventfolio_categories&edit='.intval($cat->id).'" class="eventfolio-btn ef-btn-edit">Edit</a>&nbsp;';
        echo        '<a href="?page=eventfolio_categories&delete='.intval($cat->id).'" class="eventfolio-btn ef-btn-delete" onclick="return confirm(\'Delete this category?\');">Delete</a>';
        echo    '</div>';
        echo '</div>';
    }
}
