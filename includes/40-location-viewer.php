<?php

function ef_location_viewer_row($row)
{
    $edit='<a href="' . esc_url(admin_url('admin.php?page=eventfolio_locations&category="'.$row->category.'"&id="'.$row->id.'"&action="edit"')) . '" class="eventfolio-link-btn">Edit</a>';
    $delete='<a href="' . esc_url(admin_url('admin.php?page=eventfolio_locations&category="'.$row->category.'"&id="'.$row->id.'"&action="delete"')) . '" class="eventfolio-link-btn">Delete</a>';
    echo template_render('location_viewer.html', array(
        'ICON'      => '',
        'ID'        => $row->id,
        'NAME'      => $row->name,
        'URL'       => $row->url,
        'CATEGORY'  => ef_get_category_by_slug($row->category)->name,
        'ADDRESS'   => $row->address,
        'MAP_LINK'  => '<a href="'.''.'" target="_map">Map</a>',
        'ACTIONS'   => $edit.'&nbsp;'.$delete,
        'ROW_CLASS' => '',
    ));
 
}
