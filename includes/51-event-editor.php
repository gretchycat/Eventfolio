<?php
if (!defined('ABSPATH')) exit;

function ef_event_editor()
{
    $selected_category = ef_request_var('category', '');
    $selected_date = ef_request_var('date', '');
    $event_id = ef_request_var('event_id', 'new');
    $parent_id = ef_request_var('parent_id', '');
    $past = ef_request_var('past', '');
    $future = ef_request_var('future', '');
    $sort = ef_request_var('sort', '');
    $mode = ef_request_var('mode', '');
    $date = ef_request_var('date', '');
    $length = ef_request_var('length', '');
    $cancel_link = add_query_arg([
        'page'     => 'eventfolio_events',
        'category' => $selected_category ?? '',
        'date'     => $selected_date ?? '',
        'mode'     => $mode,
        'sort'     => $sort,
        'past'     => $past ? 'true' : 'false',
        'future'   => $future ? 'true' : 'false',
        ], admin_url('admin.php'));
    $row=array();
    if (intval($event_id)>0)
    {
        $row=ef_get_event(intval($event_id));
        if( ef_str_to_bool(ef_request_var('click', 'false')))
            $selected_category=$row->category;
        $parent_id=$row->parent_event_id;
    }
    else
    {
        if($date=='')
            $tm= current_time('timestamp');
        else
            $tm=strtotime($date);
        $row=(object)array(
            'id'=>'new',
            'title'=>'',
            'location'=>'',
            'start_time'=>date_i18n("Y-m-d H:i:s",$tm),
            'end_time'=>date_i18n("Y-m-d H:i:s", $tm+(60*60)),
            'description'=>'',
            'recurrence_type' => '',
            'category'=>$selected_category,
            'parent_event_id'=>$parent_id,
            'image_id'=>'',
            );
    }
    $cat_options=options_list(ef_get_categories(),$selected_category);
    $loc_options=options_list(ef_get_locations($selected_category),ef_request_var('location', $row->location));
    $recurrence=ef_request_var('recurrence', $row->recurrence_type);
    $recur=array(
        'Single' => '',
        'Weekly' => 'weekly',
        'Monthly'=> 'monthly',
        'Yearly' => 'yearly',
            );
    $image_id=ef_request_var('featured_image', $row->image_id);
    $end= strtotime($row->end_time);
    if($parent_id)
    {
        $recur=array('Instance' => '');
        $image_id=ef_request_var('featured_image', ef_get_event($parent_id)->image_id);
    }
    $l=strtotime($row->end_time)-strtotime($row->start_time);
    if($length='')
    {
        if($l<0)
        {
            $length=0;
            $end=strtotime($row->start_time);
        }
        else
            $length=$l;
    }
    else
    {
        if($l<0)
        {
            $length=intval($length);
            $end=strtotime($row->start_time)+$length;
        }
    }
    $image_url = wp_get_attachment_url($image_id);
    $rec_options=options_list($recur,$recurrence);
    $start_date=date_i18n('Y-m-d', strtotime($row->start_time));
    $start_time = date_i18n('H:i', strtotime($row->start_time));
    $end_time   = date_i18n('H:i', $end);
    $humanized='';
    if ($recurrence)
        $humanized= ef_recurrence_human(ef_request_var('start_date', $start_date), $recurrence);
    $image_id=ef_request_var('featured_image', $row->image_id);
    $image_url = wp_get_attachment_url($image_id);
    $delete_link='';
    $series_link='';
    if(intval($event_id))
        $delete_link='Delete';
    if($recurrence and intval($row->id))
        $series_link='New Instance';
    if(intval($parent_id))
        $series_link='Edit Series';
    $links=ef_admin_events_links($row, $selected_category, $date);
    $dl='';
    $sl='';
    $nl='';
    if($links['delete'])
        $dl = '<a href="'.$links['delete'].'">Delete</a>';
    if($links['edit_series'])
        $sl = '<a href="'.$links['edit_series'].'">Edit&nbsp;Series</a>';
     if($links['new_instance'])
        $nl = '<a href="'.$links['new_instance'].'">New&nbspInstance</a>';
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');

    echo template_render('event_edit_page.html', array(
        'MODE'               => $mode,
        'DATE'               => $selected_date,
        'SORT'               => $sort,
        'EVENT_ID'           => $event_id,
        'PARENT_ID'          => $parent_id,
        'SELECTED_CATEGORY'  => esc_attr($selected_category),
        'CATEGORY_OPTIONS'   => $cat_options,
        'RECURRENCE_OPTIONS' => $rec_options,
        'LOCATION_OPTIONS'   => $loc_options,
        'RECURRENCE_DETAILS' => '',
        'PAST'               => $past ? 'true' : 'false',
        'FUTURE'             => $future ? 'true' : 'false',
        'CANCEL_URL'         => esc_url($cancel_link),
        'TITLE'              => ef_request_var('title', $row->title),
        'DESCRIPTION'        => ef_request_var('description', $row->description),
        'EVENT_DATE'         => ef_request_var('start_date', $start_date),
        'START_TIME'         => ef_request_var('start_time', $start_time),
        'END_TIME'           => ef_request_var('end_time', $end_time),
        'LENGTH'             => $length,
        'HUMANIZED'          => $humanized,
        'IMAGE_ID'           => $image_id,
        'IMAGE_URL'          => $image_url,
        'DELETE_LINK'        => $dl,
        'SERIES_LINK'        => $sl,
        'NEW_INSTANCE'       => $nl,
        ));
}
