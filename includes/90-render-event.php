<?php

if (!defined('ABSPATH')) exit;

function ef_public_event_view($slug, $id)
{
    $event_id = $id;
    $back_link = '<a href="javascript:history.back()">&lt; Back</a>';
    if (intval($event_id)>0)
    {
        $row=ef_get_event(intval($event_id));
    }
    else
        return;
    $image_id = $row->image_id;
    $category=ef_get_category_by_slug($row->category)->name;
    $public=ef_is_public($row->id);
    $end= strtotime($row->end_time);
    $l=strtotime($row->end_time)-strtotime($row->start_time);
    $image_url = wp_get_attachment_url($image_id);
    $rec_options=options_list($recur,$recurrence);
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');

    $start_date=date_i18n($date_format, strtotime($row->start_time));
    if(!empty($row->recurrence_type))
    {
        $start_date='Every '.ef_recurrence_human( $start_date, $row->recurrence_type);
    }
    $start_time = date_i18n($time_format, strtotime($row->start_time));
    $end_time   = date_i18n($time_format, $end);
    $image_id= $row->image_id;
    $image_url = wp_get_attachment_url($image_id);
    $address='';
    if(ef_user_has_permission('view_event') or $public)
    {
        $loc=ef_get_location_by_slug($row->location);
        if($loc)
        {
            $loc_image_url=$loc->image_url;
            $location=$loc->name;
            $location_url=$loc->url;
            $map_url=eventfolio_generate_map_url($loc);
            $address=$loc->address;
        }
        else
        {
            $loc_image_url='';
            $location='';
            $location_url='';
            $map_url='';
            $address='Location is to be determined';

        }
    }
    else
    {
            $loc_image_url='';
            $location='';
            $location_url='';
            $map_url='/wp-login.php';
            $address='You must log in and be validated to see private events.';
    }
    echo template_render('event_view_page.html', array(
        'BACK_URL'      => $back_link,
        'CATEGORY'      => $category,
        'TITLE'         => $row->title,
        'DESCRIPTION'   => nl2br(htmlentities($row->description)),
        'EVENT_DATE'    => $start_date,
        'START_TIME'    => $start_time,
        'END_TIME'      => $end_time,
        'IMAGE_ID'      => $image_id,
        'IMAGE_URL'     => $image_url,
        'LOCATION_IMG_URL' => $loc_image_url,
        'LOCATION'      => $location,
        'LOCATION_URL'  => $location_url,
        'ADDRESS'       => nl2br(htmlentities($address)),
        'MAP_URL'       => $map_url,
        ));
}
