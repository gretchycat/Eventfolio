<?php

function ef_location_viewer_row($row)
{
    $edit='<a href="' . esc_url(admin_url('admin.php?page=eventfolio_locations&category="'.$row->category.'"&id="'.$row->id.'"&action="edit"')) . '" class="eventfolio-link-btn">Edit</a>';
    $delete='<a href="' . esc_url(admin_url('admin.php?page=eventfolio_locations&category="'.$row->category.'"&id="'.$row->id.'"&action="delete"')) . '" class="eventfolio-link-btn">Delete</a>';
    $map_link='';
    $address='No Address';
    if($row->address)
        $address= '<pre>'.$row->address.'</pre>';
    if($row->address)
    {
        $map_url=eventfolio_generate_map_url($row);
        $map_link= '<a href="'.$map_url.'"target="_map">Map</a>';
    }
    $img_link='';
    if ($row->image_url)
        $img_link='<img src="'.$row->image_url.'" alt="icon" width="100%">';
    $url='No URL';
    if($row->url)
        $url='<a href="'.$row->url.'" target="_new">'.$row->url.'</a>';
    echo template_render('location_viewer.html', array(
        'ICON'      => '',
        'ID'        => $row->id,
        'NAME'      => $row->name,
        'URL'       => $url,
        'CATEGORY'  => ef_get_category_by_slug($row->category)->name,
        'ADDRESS'   => $address,
        'MAP_LINK'  => $map_link,
        'ACTIONS'   => $edit.'&nbsp;'.$delete,
        'ROW_CLASS' => '',
        'IMAGE_PREVIEW'=>$img_link,
    ));
 
}
