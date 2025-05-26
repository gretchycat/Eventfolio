<?php
if (!defined('ABSPATH')) exit;

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
