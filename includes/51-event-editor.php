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
    }
    else
        $row=(object)array(
            'title'=>'',
            'location'=>'',
            'start_time'=>date("Y-m-d H:i:s", time()),
            'end_time'=>date("Y-m-d H:i:s", time()+(60*60)),
            'description'=>'',
            'recurrence_type' => '',
            'category'=>$selected_category,
            );
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
    if($parent_id)
    {
        $recur=array('Instance' => '');
        $image_id=ef_request_var('featured_image', ef_get_event($parent_id)->image_id);
    }
    $image_url = wp_get_attachment_url($image_id);

    $rec_options=options_list($recur,$recurrence);
    $start_date = date('Y-m-d', strtotime($row->start_time));
    $start_time = date('H:i', strtotime($row->start_time));
    $end_time   = date('H:i', strtotime($row->end_time));
    $humanized='';
    if ($recurrence)
        $humanized= ef_recurrence_human(ef_request_var('start_date', $start_date), $recurrence);
    $image_id=ef_request_var('featured_image', $row->image_id);
    $image_url = wp_get_attachment_url($image_id);
    echo template_render('event_edit_page.html', array(
        'MODE'               => $mode,
        'DATE'               => $date,
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
        'HUMANIZED'          => $humanized,
        'IMAGE_ID'           => $image_id,
        'IMAGE_URL'          => $image_url,
        ));
}
