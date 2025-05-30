<?php
if (!defined('ABSPATH')) exit;

// --- Helper: Category header row ---
if (!function_exists('ef_category_header_row')) {
    function ef_category_header_row()
    {
        ?>
        <div class="ef-category-row ef-category-header">
            <div class="ef-category-col ef-col-name">Name</div>
            <div class="ef-category-col ef-col-visibility">Visibility</div>
            <div class="ef-category-col ef-col-description">Description</div>
            <div class="ef-category-col ef-col-actions">Actions</div>
        </div>
        <?php
    }
}

function et_cat_style()
{
    echo <<<EOF
<style>
.ef-category-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.ef-category-row {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    border-bottom: 1px solid #444;
}

.ef-category-header {
    font-weight: bold;
    background: #191919;
    color: #fff;
}

.ef-category-col {
    padding: 0.5em 1em;
    min-width: 0;
    word-break: break-all;
    display: flex;
    align-items: center;
}

.ef-col-name        { flex: 1 1 12em; }
.ef-col-visibility  { flex: 0 1 12em;  text-align: left; }
.ef-col-description { flex: 2 1 20em; }
.ef-col-actions     { flex: 0 0 8em;  text-align: right; }
.ef-category-col input[type="text"],
.ef-category-col textarea,
.ef-category-col select {
    width: 100%;
    box-sizing: border-box;
    font-size: inherit;
    margin: 0;
    padding: 4px 6px;
    background: #1a1c1f;
    color: #fff;
    border: 1px solid #333;
    border-radius: 4px;
}
.ef-category-col select {
    width: 100% !important;
    min-width: 60px;
    box-sizing: border-box;
    font-size: inherit;
    padding: 4px 6px;
    background: #1a1c1f;
    color: #fff;
    border: 1px solid #333;
    border-radius: 4px;
}
/* Optional: tighten up space between columns if needed */
.ef-category-row {
    gap: 0.05em;
}
</style>
EOF;
}

function ef_admin_categories_page()
{
    $changed=false;
    // --- Handle POST (add/edit) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cat_action']) && check_admin_referer('ef_category_form'))
    {
        $id = intval($_POST['cat_id']);
        $slug = sanitize_title($_POST['cat_name']);
        $name = sanitize_text_field($_POST['cat_name']);
        $visibility = ($_POST['cat_visibility'] === 'private') ? 'private' : 'public';
        $description = sanitize_text_field($_POST['cat_description']);

        if ($_POST['cat_action'] === 'add')
        {
            if (!ef_category_slug_exists($slug))
            {
                ef_insert_category($slug, $name, $visibility, $description);
            }
            $changed=true;
            // Redirect to main page to clear &add=1
            //wp_redirect(admin_url('admin.php?page=eventfolio_categories'));
            //exit;
        }
        elseif ($_POST['cat_action'] === 'save' && $id)
        {
            ef_update_category($id, $name, $visibility, $description);
            $changed=true;
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
            $changed=true;
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
    et_cat_style();

    echo '<div class="ef-category-list">';
    ef_category_header_row();
    foreach ($categories as $cat)
    {
        if ($editing_id == $cat->id && !$changed)
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
    // Add Category button (shown if not editing or adding)
    if (!$editing_id && !$adding_new) {
        echo '<p><a class="button ef-btn-add" href="' . esc_url(admin_url('admin.php?page=eventfolio_categories&add=1')) . '">Add Category</a></p>';
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
