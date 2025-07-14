<?php
if (!defined('ABSPATH')) exit;
//  45-admin-location.php

if (!function_exists('ef_admin_locations_page'))
{
    function ef_admin_locations_page()
    {
        $saved=false;
        // You might want your admin nav here
        if (function_exists('ef_admin_nav')) ef_admin_nav('Locations');
        $selected_category = ef_request_var('category', '');
        $id = ef_request_var('id', '');
        $action = ef_request_var('action', '');
        $categories = ef_get_categories();
        $cat_options='';
        foreach ($categories as $cat)
        {
            $sel = $selected_category == $cat->slug ? 'selected' : '';
            $cat_options .= '<option value="' . esc_attr($cat->slug) . '" ' . $sel . '>' . esc_html($cat->name) . '</option>';
        }
        echo template_render('location_header.html', array(
             'CATEGORY_OPTIONS'   => $cat_options,
            ));
        $add_url = add_query_arg([
            'page'     => 'eventfolio_locations',
            'id'      => 'new',
            'category' => $selected_category ?? '',
        ], admin_url('admin.php'));
        echo template_render('add_new_button.html', [
            'TYPE'      => 'Location',
            'ADD_URL'   => esc_url($add_url),
        ]);
        if ($action=='edit')
        {
        }
        if ($action=='delete')
        {
            $id = ef_request_var('id', '');
            ef_delete_location($id);
        } 
        if ($action=='save')
        {
            $image_id = ef_request_var('featured_image', '');
            $image_url = wp_get_attachment_url($image_id);
            if(intval($id)<=0)
            {
                $slug=sanitize_title($_POST['category'].' '.$_POST['name']);
                ef_insert_location($slug, $_POST['name'], $_POST['category'],
                    '', $_POST['address'], $_POST['url'],
                    $image_url, $image_id, '');
                $saved=true;
            }
            else
            {
                $fields=array(
                    'name'=>$_POST['name'],
                    'category'=>$_POST['category'],
                    'url'=>$_POST['url'],
                    'address'=>$_POST['address'],
                    'image_url'=>$image_url,
                    'image_id'=>$image_id,
                    );
                ef_update_location(intval($id), $fields);
                $saved=true;
            }
        }
        ef_admin_locations_list($selected_category, $id, $saved);
    }
}

function ef_admin_locations_list($category, $id, $saved)
{
    echo '<div class="eventfolio-list eventfolio-locations-list">';
    // Header row
    echo template_render('location_viewer.html', array(
        'ICON'      => '',
        'NAME'      => 'Name',
        'URL'       => 'URL',
        'CATEGORY'  => 'Category',
        'ADDRESS'   => 'Address',
        'MAP_LINK'  => '',
        'ACTIONS'   => '',
        'ROW_CLASS' => 'eventfolio-header',
        'IMAGE_PREVIEW'=>'',
    ));
    if ($id=='new' and !$saved)
    {
        $row=(object)[
            'id'         =>$id,
            'slug'       =>'',
            'name'       =>'',
            'category'   =>$category,
            'description'=>'',
            'url'        =>'',
            'address'    =>'',
            'imageurl'   =>'',
            'imageid'    =>'',
        ];
        ef_location_editor_row($row);
    }
    $locations = ef_get_locations($category);
    if (!empty($locations))
    {
        foreach ($locations as $row)
        {
            if ($row->id==intval($id) and !$saved)
                ef_location_editor_row($row);
            else
                ef_location_viewer_row($row);
        }
    }
    else
    {
        echo '<div class="eventfolio-row"><div class="eventfolio-col" style="flex: 1;">No events found.</div></div>';
    }
    echo '</div>';
}
