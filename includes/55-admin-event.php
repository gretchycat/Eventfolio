<?php
//  55-admin-event.php

if (!function_exists('ef_admin_events_page'))
{
    function ef_admin_events_page()
    {
        // You might want your admin nav here
        if (function_exists('ef_admin_nav')) ef_admin_nav('Events');
        $selected_category = ef_request_var('category', '');
        $selected_date = ef_request_var('date', date("Y-m-d H:i:s", time()));
        $mode = ef_request_var('mode', 'list');
        $sort = ef_request_var('sort', 'a');
        $action = ef_request_var('action', '');
        $past = ef_str_to_bool(ef_request_var('past', 'false'));
        $future =  ef_str_to_bool(ef_request_var('future', 'false'));
        $editing = ef_str_to_bool(ef_request_var('editing', 'false'));
        $cat_options=options_list(ef_get_categories(),$selected_category);
        if(!$past and !$future) //we have to show something!
            $future=true;
                echo '<pre></code>';
                print_r($_GET);
                print_r($_POST);
                echo '</code></pre>';
 
        //category chooser
        if($action=='save')
        {
            $event_id = ef_request_var('event_id', '');
            $image_id = ef_request_var('featured_image', '');
            $image_url = wp_get_attachment_url($image_id);
            $data = [
                'title'           => $_POST['title'],
                'description'     => $_POST['description'],
                'category'        => $_POST['category'],
                'location'        => $_POST['location'],
                'start_time'      => $_POST['start_date'].' '.$_POST['start_time'],
                'end_time'        => $_POST['start_date'].' '.$_POST['end_time'],
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
            echo template_render('event_page_header.html', array(
                'MODE'             => $mode,
                'DATE'             => $selected_date,
                'SORT'             => $sort,
                'SELECTED_CATEGORY'=> esc_attr($selected_category),
                'CATEGORY_OPTIONS' => $cat_options,
                'PAST'       => $past ? 'true' : 'false',
                'FUTURE'     => $future ? 'true' : 'false',
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
            {
                //show list interface
                ef_admin_events_list($selected_category, $past, $future, $sort, $selected_date);
            }
            elseif ($mode=='calendar')
            {//show calendar interface
                ef_admin_events_calendar($selected_category, $selected_date);
            }
        }
    }
}

function ef_admin_events_links($row, $category, $past, $future, $sort, $date)
{
    $edit_series='';
    $delete_url='';
    $new_instance='';
    $edit_url = '';
    $mode = ef_request_var('mode', 'list');
    if(!intval($row->id))
        $row->id='new';
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
            $next = ef_get_next_event_times(
                date("Y-m-d", time()),
                $row->start_time,
                $row->end_time,
                $row->recurrence_type);
            $start_date = date('Y-m-d', strtotime($next['start']));
            $start_time = date('H:i', strtotime($next['start']));
            $end_time   = date('H:i', strtotime($next['end']));
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
        'edit'         => $edit_url,
        'delete'       => $delete_url,
        'edit_series'  => $edit_series,
        'new_instance' => $new_instance,
        );
}

function ef_admin_events_list($category, $past, $future, $sort, $date)
{
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
            $links=ef_admin_events_links($row, $category, $past, $future, $sort, $date);
            ef_event_viewer_row($row, $links['edit'], $links['delete'], $links['edit_series'], $links['new_instance']);
        }
    }
    else
    {
        echo '<div class="eventfolio-row"><div class="eventfolio-col" style="flex: 1;">No events found.</div></div>';
    }
    echo '</div>';
}

function ef_admin_events_calendar($category, $date)
{
    $now=date("Y-m-d H:i:s", time());
    $now_date=date("Y-m-d", time());

    $date_only = date('Y-m-d', strtotime($date));

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
                    'START'   => date("H:i", strtotime($ev->start_time)),
                    'END'     => date("H:i", strtotime($ev->end_time)),
                    'ACTIONS' =>'',
                    ));
                $args = array_merge($_GET, $_POST);
                $args['action'] = 'edit';
                $args['event_id'] = $ev->id;
                $url = add_query_arg($args, admin_url('admin.php'));
                $ln='<a href="'.$url.'" class="eventfolio-event">';
                $lc='</a>';
                $events.= $ln.$event.$lc;
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


