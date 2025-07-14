<?php

if (!defined('ABSPATH')) exit;
//  55-admin-event.php

if (!function_exists('ef_admin_events_page'))
{
    function ef_admin_events_page()
    {
        // You might want your admin nav here
        if (function_exists('ef_admin_nav')) ef_admin_nav('Events');
        $selected_category = ef_request_var('category', '');
        $selected_date = ef_request_var('date', date_i18n("Y-m-d", current_time('timestamp')));
        $mode = ef_request_var('mode', 'calendar');
        $sort = ef_request_var('sort', 'a');
        $action = ef_request_var('action', '');
        $past = ef_str_to_bool(ef_request_var('past', 'false'));
        $future =  ef_str_to_bool(ef_request_var('future', 'false'));
        $editing = ef_str_to_bool(ef_request_var('editing', 'false'));
        $cat_options=options_list(ef_get_categories(),$selected_category);
        if(!$past and !$future) //we have to show something!
            $future=true;
        //category chooser
        if($action=='reset_date')
        {
            $selected_date = date_i18n("Y-m-d", current_time('timestamp'));
        }
        elseif($action=='save')
        {
            $event_id = ef_request_var('event_id', '');
            $image_id = ef_request_var('featured_image', '');
            $image_url = wp_get_attachment_url($image_id);
            $length=intval(ef_request_var('length', '0'));
            $start= $_POST['start_date'].' '.$_POST['start_time'];
            $end= $_POST['start_date'].' '.$_POST['end_time'];
            if(strtotime($end)-strtotime($start)<0)
            {
                //$end=date_i18n(strtotime($start)+$length);
            }
            $data = [
                'title'           => $_POST['title'],
                'description'     => $_POST['description'],
                'category'        => $_POST['category'],
                'location'        => $_POST['location'],
                'visibility'      => $_POST['visibility'],
                'start_time'      => $start,
                'end_time'        => $end,
                'recurrence_type' => $_POST['recurrence'],
                'parent_event_id' => $_POST['parent_id'],
                'image_id'        => $_POST['featured_image'],
                'image_url'       => $image_url,
            ];
            if($event_id=='new')
            {
                ef_insert_event($data);
            }
            else
            {
                ef_update_event($event_id, $data);
            }
            $editing=false;
        }
        if($action=='delete')
        {
            $event_id = ef_request_var('event_id', '');
            ef_delete_event($event_id);
            $editing=false;
        }
        if ($action=='edit' or $editing)// and $selected_category)
        {
            ef_event_editor();
        }
        else
        {
            $la=''; $ca='';
            if ($mode=='list')
                $la='active';
            else
                $ca='active';
            echo template_render('event_page_header.html', array(
                'MODE'             => $mode,
                'DATE'             => $selected_date,
                'SORT'             => $sort,
                'SELECTED_CATEGORY'=> esc_attr($selected_category),
                'CATEGORY_OPTIONS' => $cat_options,
                'PAST'             => $past ? 'true' : 'false',
                'FUTURE'           => $future ? 'true' : 'false',
                'LIST_ACTIVE'      => $la,
                'CAL_ACTIVE'       => $ca,
                ));
            //custom based on display mode
            if ($mode=='list')
            {
                echo template_render('event_page_list_header.html', array(
                    'MODE'             => $mode,
                    'DATE'             => $selected_date,
                    'SORT'             => $sort,
                    'SELECTED_CATEGORY'=> esc_attr($selected_category),
                    'PAST_CHECKED'     => $past ? 'checked' : '',
                    'FUTURE_CHECKED'   => $future ? 'checked' : '',
                    'PAST_VALUE'       => $past ? 'true' : 'false',
                    'FUTURE_VALUE'     => $future ? 'true' : 'false',
                    'NEXT_SORT'        => ($sort === 'a') ? 'd' : 'a',
                    'SORT_ICON'        => ($sort === 'a')
                        ? '<span class="dashicons dashicons-arrow-down-alt"></span>'
                        : '<span class="dashicons dashicons-arrow-up-alt"></span>',
                    'SORT_LABEL'       => ($sort === 'a') ? 'Ascending':'Descending',
                    ));
            }
            elseif ($mode=='calendar')
            {
                echo template_render('event_page_calendar_header.html', array(
                    'MODE'             => $mode,
                    'DATE'             => $selected_date,
                    'SORT'             => $sort,
                    'SELECTED_CATEGORY'=> esc_attr($selected_category),
                    'PAST_VALUE'       => $past ? 'true' : 'false',
                    'FUTURE_VALUE'     => $future ? 'true' : 'false',
                    ));
            }

            $add_event_url = add_query_arg([
                'page'     => 'eventfolio_events',
                'event_id' => 'new',
                'action'   => 'edit',
                'category' => $selected_category ?? '',
                'date'     => $selected_date ?? '',
                'mode'     => $mode,
                'sort'     => $sort,
                'past'     => $past ? 'true' : 'false',
                'future'   => $future ? 'true' : 'false',
                ], admin_url('admin.php'));
            echo template_render('add_new_button.html', [
                'ADD_URL'  => esc_url($add_event_url),
                'TYPE'     => 'Event',
            ]);
            if ($mode =='list')
            {   //show list interface
                ef_events_list($selected_category, $selected_date);
            }
            elseif ($mode=='calendar')
            {   //show calendar interface
                ef_events_calendar($selected_category, $selected_date);
            }
        }
    }
}

function ef_admin_events_links($row, $category, $date, $selected_date='')
{
    $sort = ef_request_var('sort', 'a');
    $past = ef_str_to_bool(ef_request_var('past', 'false'));
    $future =  ef_str_to_bool(ef_request_var('future', 'false'));

    $edit_series='';
    $delete_url='';
    $new_instance='';
    $edit_url = '';
    $mode = ef_request_var('mode', 'calendar');
    if(!intval($row->id))
        $row->id='new';
    $view_url = add_query_arg([
        'event_id' => $row->id,
        'eventfolio_category_slug' => $category ?? '',
        'eventfolio_view' => 'event',
        'date'     => $date ?? '',
        ], home_url('/'));
 
    $edit_url = add_query_arg([
        'page'     => 'eventfolio_events',
        'event_id' => $row->id,
        'action'   => 'edit',
        'click'    => 'true',
        'category' => $category ?? '',
        'date'     => $date ?? '',
        'mode'     => $mode,
        'sort'     => $sort,
        'past'     => $past ? 'true' : 'false',
        'future'   => $future ? 'true' : 'false',
        ], admin_url('admin.php'));
    if(intval($row->id))
    {
        $delete_url = add_query_arg([
            'page'     => 'eventfolio_events',
            'event_id' => $row->id,
            'action'   => 'delete',
            'category' => $category ?? '',
            'date'     => $date ?? '',
            'mode'     => $mode,
            'sort'     => $sort,
            'past'     => $past ? 'true' : 'false',
            'future'   => $future ? 'true' : 'false',
            ], admin_url('admin.php'));
        if(intval($row->parent_event_id))
            $edit_series = add_query_arg([
                'page'     => 'eventfolio_events',
                'event_id' => $row->parent_event_id,
                'action'   => 'edit',
                'click'    => 'true',
                'category' => $category ?? '',
                'date'     => $date ?? '',
                'mode'     => $mode,
                'sort'     => $sort,
                'past'     => $past ? 'true' : 'false',
                'future'   => $future ? 'true' : 'false',
                ], admin_url('admin.php'));
        if($row->recurrence_type)
        {
            $when=$selected_date ?? date_i18n("Y-m-d", current_time('timestamp'));
            $next = ef_get_next_event_times(
                $when,
                $row->start_time,
                $row->end_time,
                $row->recurrence_type);
            $start_date = date_i18n('Y-m-d', strtotime($next['start']));
            $start_time = date_i18n('H:i', strtotime($next['start']));
            $end_time   = date_i18n('H:i', strtotime($next['end']));
            $new_instance = add_query_arg([
                'page'       => 'eventfolio_events',
                'event_id'   => 'new',
                'parent_id'  => $row->id,
                'action'     => 'edit',
                'click'      => 'true',
                'featured_image'=> $row->image_id,
                'image_url'  => $row->image_url,
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_time'   => $end_time,
                'category'   => $row->category,
                'date'       => $date ?? '',
                'mode'       => $mode,
                'sort'       => $sort,
                'past'       => $past ? 'true' : 'false',
                'future'     => $future ? 'true' : 'false',
                'title'      => $row->title,
                'recurrence' => '',
                'description'=> $row->description,
                ], admin_url('admin.php'));
        }
    }
    return array(
        'view'         => $view_url,
        'edit'         => $edit_url,
        'delete'       => $delete_url,
        'edit_series'  => $edit_series,
        'new_instance' => $new_instance,
        );
}

function ef_events_list($category, $date)
{
    $sort = ef_request_var('sort', 'a');
    $action = ef_request_var('action', '');
    $past = ef_str_to_bool(ef_request_var('past', 'false'));
    $future =  ef_str_to_bool(ef_request_var('future', 'false'));

    echo '<div class="eventfolio-list eventfolio-events-list">';
    // Header row
    echo template_render('event_row.html', array(
        'ICON' => '',
        'TITLE' => 'Title',
        'LOCATION' => 'Location',
        'DATE' => 'Date',
        'START' => 'Start',
        'END' => 'End',
        'RECURRING' => 'Recurring',
        'ACTIONS' => '',
        'HEADER' => 'eventfolio-header',
    ));
    $mode='list';
    $events = ef_get_events($category, $past, $future, $sort);
    if (!empty($events))
    {
        foreach ($events as $row)
        { 
            if(ef_user_has_permission('view_event'))    //is validated?
            {
                $links=ef_admin_events_links($row, $category, $date);
                if(ef_user_has_permission('edit_event'))    //is organizer?
                {
                    ef_event_viewer_row($row, $links['edit'], $links['delete'], $links['edit_series'], $links['new_instance']);
                }
                else
                {
                    ef_event_viewer_row($row, $links['view'], '', '', '');
                }
            }
            else
                ef_event_viewer_row($row, '', '', '', '');

        }
    }
    else
    {
        echo '<div class="eventfolio-row"><div class="eventfolio-col" style="flex: 1;">No events found.</div></div>';
    }
    echo '</div>';
}

function ef_events_calendar($category, $date)
{
    $now=date_i18n("Y-m-d H:i:s", current_time('timestamp'));
    $now_date=date_i18n("Y-m-d", current_time('timestamp'));
    $mode='calendar';
    $date_only = date_i18n('Y-m-d', strtotime($date));

    $dow = [];
    for ($i = 0; $i < 7; $i++) {
        $dow[] = date_i18n('l', strtotime("sunday +$i days"));
    }
    $calendar='';
    $dt=ef_get_week_start_sunday($date);
    $cells='';
    foreach($dow as $day)
    {
        $events='';
        $cells.=template_render('calendar_month_cell.html', array(
            'DAY'       => $day,
            'EVENTS'    => $events,
            'DAY_THEME' => 'calendar-month-header'
        ));
    }

    $calendar.=template_render('calendar_month_row.html', array(
            'HEADER'    => 'eventfolio-header',
            'CELLS'     => $cells,
            ));
    $start_month = date_i18n('F', strtotime($dt));
    for ($w=0;$w<5;$w++)
    {
        $cells='';
        foreach ($dow as $day)
        {
            $p=explode('-', $dt);
            $y=$p[0];
            $m=$p[1];
            $d=$p[2];
            $month = date_i18n('F', strtotime($dt));
            $theme='';
            if ($dt<$now_date)
                $theme.='calendar-past ';
            if ($dt==$date_only)
                $theme.='calendar-selected ';
            if ($dt==$now_date)
                $theme.='calendar-today ';
            if ($dt==$date_only and $dt==$now_date)
                $theme.='calendar-selected-today ';
            $events='';
            $evs=ef_get_events_on($category, $dt);
            foreach($evs as $ev)
            {
                $event = template_render('calendar_month_cell_event.html', array(
                    'ICON'    =>$ev->image_url,
                    'TITLE'   =>$ev->title,
                    'START'   => date_i18n("H:i", strtotime($ev->start_time)),
                    'END'     => date_i18n("H:i", strtotime($ev->end_time)),
                    'ACTIONS' =>'',
                    ));
                if(ef_user_has_permission('view_event'))    //is validated?
                {
                    $links=ef_admin_events_links($ev, $category, $date_only, $dt);
                    if(ef_user_has_permission('edit_event'))    //is organizer?
                    {
                        $url = empty($links['new_instance']) ? $links['edit'] : $links['new_instance'];
                    }
                    else
                    {
                        $url=$links['view'];
                    }
                    $ln='<a href="'.$url.'" class="eventfolio-event">';
                    $lc='</a>';
                    $events.= $ln.$event.$lc;
                }
                else
                {
                    $events.=$event;
                }
            }
            $cells.=template_render('calendar_month_cell.html', array(
                'DAY'       => $d,
                'EVENTS'    => $events,
                'DAY_THEME' => $theme,
            ));
            $dt=ef_get_next_day($dt);
        }
        $calendar.=template_render('calendar_month_row.html', array(
            'HEADER'    => '',
            'CELLS'     => $cells,
            ));
    }
    $end_month = date_i18n('F', strtotime($dt));
    echo template_render('calendar_month.html', array(
        'MONTH'     => $start_month.'—'.$end_month,
        'CALENDAR'  => $calendar,
        ));
}


