<?php
if (!defined('ABSPATH')) exit;

require_once EF_PLUGIN_PATH . 'includes/20-category-viewer.php';
require_once EF_PLUGIN_PATH . 'includes/21-category-editor.php';

function ef_admin_categories_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'ef_categories';

    // --- Handle add/edit/delete actions ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_action']))
    {
        $id = intval($_POST['cat_id']);
        $slug = sanitize_title($_POST['cat_slug']);
        $name = sanitize_text_field($_POST['cat_name']);
        $visibility = ($_POST['cat_visibility'] === 'private') ? 'private' : 'public';
        $description = sanitize_text_field($_POST['cat_description']);

        if ($_POST['cat_action'] === 'add')
        {
            // Unique slug check
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $slug));
            if (!$exists)
            {
                $wpdb->insert($table, [
                    'slug' => $slug,
                    'name' => $name,
                    'visibility' => $visibility,
                    'description' => $description
                ]);
            }
            // (You might want a UI error if exists)
        }
        elseif ($_POST['cat_action'] === 'save' && $id)
        {
            $wpdb->update($table, [
                'name' => $name,
                'visibility' => $visibility,
                'description' => $description
            ], ['id' => $id]);
        }
    }
    elseif (isset($_GET['delete']))
    {
        $delete_id = intval($_GET['delete']);
        if ($delete_id)
        {
            $wpdb->delete($table, ['id' => $delete_id]);
        }
    }

    // Editing ID
    $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

    // Fetch categories
    $categories = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");

    // --- Render UI ---
    ef_admin_nav();

    echo '<div class="ef-category-list">';
    echo '<div class="ef-category-row ef-category-header">';
    echo '<div class="ef-category-col">Slug</div>';
    echo '<div class="ef-category-col">Name</div>';
    echo '<div class="ef-category-col">Visibility</div>';
    echo '<div class="ef-category-col">Description</div>';
    echo '<div class="ef-category-col ef-category-actions">Actions</div>';
    echo '</div>';

    foreach ($categories as $cat)
    {
        if ($editing_id == $cat->id) {
            ef_category_editor_row($cat, 'edit');
        } else {
            ef_category_viewer_row($cat);
        }
    }
    // Add row for new category
    ef_category_editor_row((object)['id'=>0, 'slug'=>'', 'name'=>'', 'visibility'=>'public', 'description'=>''], 'add');

    echo '</div>';
}
