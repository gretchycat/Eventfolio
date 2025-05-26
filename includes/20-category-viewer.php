<?php
if (!defined('ABSPATH')) exit;
?>
<div class="ef-category-row ef-category-viewer">
    <div class="ef-category-col"><?php echo esc_html($cat->slug); ?></div>
    <div class="ef-category-col"><?php echo esc_html($cat->name); ?></div>
    <div class="ef-category-col"><?php echo esc_html(ucfirst($cat->visibility)); ?></div>
    <div class="ef-category-col"><?php echo esc_html($cat->description); ?></div>
    <div class="ef-category-col ef-category-actions">
        <a href="?page=eventfolio_categories&edit=<?php echo intval($cat->id); ?>" class="ef-btn ef-btn-edit">Edit</a>
        <a href="?page=eventfolio_categories&delete=<?php echo intval($cat->id); ?>" class="ef-btn ef-btn-delete" onclick="return confirm('Delete this category?');">Delete</a>
    </div>
</div>
