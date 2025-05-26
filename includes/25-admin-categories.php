<?php
if (!defined('ABSPATH')) exit;

if(false){
ef_admin_nav();

echo '<div class="ef-category-list">';
echo '<div class="ef-category-row ef-category-header">';
echo '<div class="ef-category-col">Slug</div>';
echo '<div class="ef-category-col">Name</div>';
echo '<div class="ef-category-col">Visibility</div>';
echo '<div class="ef-category-col">Description</div>';
echo '<div class="ef-category-col ef-category-actions">Actions</div>';
echo '</div>';

// Fetch categories (you'll replace this with DB logic)
$categories = isset($categories) ? $categories : []; // fallback if $categories not passed
$editing_id = isset($editing_id) ? $editing_id : 0; // set to editing category id

foreach ($categories as $cat)
{
    if ($editing_id == $cat->id)
    {
        $mode = 'edit';
        include EF_PLUGIN_PATH . 'includes/category-editor.php';
    }
    else
    {
        include EF_PLUGIN_PATH . 'includes/category-viewer.php';
    }
}

// Add new category row (blank)
$cat = (object) ['id'=>0, 'slug'=>'', 'name'=>'', 'visibility'=>'public', 'description'=>''];
$mode = 'add';
include EF_PLUGIN_PATH . 'includes/category-editor.php';

echo '</div>';

function ef_admin_categories_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'ef_categories';
}
    // Handle add/edit/delete actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_action']))
    {
        $id = intval($_POST['cat_id']);
        $slug = sanitize_title($_POST['cat_slug']);
        $name = sanitize_text_field($_POST['cat_name']);
        $visibility = $_POST['cat_visibility'] === 'private' ? 'private' : 'public';
        $description = sanitize_text_field($_POST['cat_description']);

        if ($_POST['cat_action'] === 'add')
        {
            // Slug must be unique
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
            // Else: handle error (slug exists)
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

    // Are we editing?
    $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

    // Fetch all categories
    $categories = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");

    // Bring in the UI
    include EF_PLUGIN_PATH . 'includes/admin-categories-view.php';
}
