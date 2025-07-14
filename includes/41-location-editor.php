<?php
if (!defined('ABSPATH')) exit;

function ef_location_editor_row($row)
{
    $categories = ef_get_categories();
    $cat_options='';
    foreach ($categories as $cat)
    {
        $sel = $row->category == $cat->slug ? 'selected' : '';
        $cat_options .= '<option value="' . esc_attr($cat->slug) . '" ' . $sel . '>' . esc_html($cat->name) . '</option>';
    }
    $save='<button type="submit" name="action" value="save" class="eventfolio-link-btn">Save</button>';
    $cancel='<a href="' . esc_url(admin_url('admin.php?page=eventfolio_locations&category="'.$row->category.'"')) . '" class="eventfolio-link-btn">Cancel</a>';
 
    echo template_render('location_editor.html', array(
        'ICON'      => '',
        'ID'        => $row->id,
        'NAME'      => $row->name,
        'URL'       => $row->url,
        'CATEGORY_OPTIONS' => $cat_options,
        'ADDRESS'   => $row->address,
        'MAP_LINK'  => '',
        'ACTIONS'   => "$save&nbsp;$cancel",
        'ROW_CLASS' => '',
        'IMAGE_ID'  => $row->image_id ?? '',
        'IMAGE_URL' => $row->image_url ?? '',
    ));
 
}
