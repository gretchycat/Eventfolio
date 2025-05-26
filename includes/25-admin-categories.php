<?php
if (!defined('ABSPATH')) exit;

// --- Helper: Category header row ---
if (!function_exists('ef_category_header_row')) {
    function ef_category_header_row()
    {
        ?>
        <div class="ef-category-row ef-category-header">
            <div class="ef-category-col">Slug</div>
            <div class="ef-category-col">Name</div>
            <div class="ef-category-col">Visibility</div>
            <div class="ef-category-col">Description</div>
            <div class="ef-category-col ef-category-actions">Actions</div>
        </div>
        <?php
    }
}

function ef_admin_categories_page()
{
    // --- Handle POST (add/edit) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_action']) && check_admin_referer('ef_category_form'))
    {
        $id = intval($_POST['cat_id']);
        $slug = sanitize_title($_POST['cat_slug']);
        $name = sanitize_text_field($_POST['cat_name']);
        $visibility = ($_POST['cat_visibility'] === 'private') ? 'private' : 'public';
        $description = sanitize_text_field($_POST['cat_description']);

        if ($_POST['cat_action'] === 'add')
        {
            if (!ef_category_slug_exists($slug))
            {
                ef_insert_category($slug, $name, $visibility, $description);
            }
            // Redirect to main page to clear &add=1
            //wp_redirect(admin_url('admin.php?page=eventfolio_categories'));
            //exit;
        }
        elseif ($_POST['cat_action'] === 'save' && $id)
        {
            ef_update_category($id, $name, $visibility, $description);
            //wp_redirect(admin_url('admin.php?page=eventfolio_categories'));
            //exit;
        }
    }
    // --- Handle delete ---
    elseif (isset($_GET['delete']))
    {
        $delete_id = intval($_GET['delete']);
        if ($delete_id)
        {
            ef_delete_category($delete_id);
            //wp_redirect(admin_url('admin.php?page=eventfolio_categories'));
            //exit;
        }
    }

    // --- Setup ---
    $editing_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
    $adding_new = isset($_GET['add']) && !$editing_id;
    ef_ensure_categories_exist();
    $categories = ef_get_categories();

    // --- Render ---
    ef_admin_nav();

    // Add Category button (shown if not editing or adding)
    if (!$editing_id && !$adding_new) {
        echo '<p><a class="button ef-btn-add" href="' . esc_url(admin_url('admin.php?page=eventfolio_categories&add=1')) . '">Add Category</a></p>';
    }

    echo '<div class="ef-category-list">';
    ef_category_header_row();
    foreach ($categories as $cat)
    {
        if ($editing_id == $cat->id) 
        {
            ef_category_editor_row($cat, 'edit');
        } else 
        {
            ef_category_viewer_row($cat);
        }
    }
    // Only show add row if adding_new and not currently editing
    if ($adding_new) 
    {
        ef_category_editor_row((object)[
            'id'=>0, 'slug'=>'', 'name'=>'', 'visibility'=>'public', 'description'=>''
        ], 'add');
    }
    echo '</div>';

    // JS for live slug generation
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let nameInputs = document.querySelectorAll('input[name="cat_name"]');
        nameInputs.forEach(function(nameInput) {
            nameInput.addEventListener('input', function(e) {
                let form = nameInput.closest('form');
                if (!form) return;
                let slugInput = form.querySelector('input[name="cat_slug"]');
                let slugLabel = form.querySelector('.ef-slug-label');
                if (!slugInput || !slugLabel) return;
                let nameVal = nameInput.value.toLowerCase();
                nameVal = nameVal.replace(/[^a-z0-9\s-]/g, '');
                nameVal = nameVal.replace(/\s+/g, ' ');
                nameVal = nameVal.trim();
                nameVal = nameVal.replace(/\s+/g, '-');
                nameVal = nameVal.replace(/-+/g, '-');
                slugInput.value = nameVal;
                slugLabel.textContent = nameVal;
            });
        });
    });
    </script>
    <?php
}
