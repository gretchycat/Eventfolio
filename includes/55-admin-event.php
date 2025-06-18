<?php
//  55-admin-event.php

if (!function_exists('ef_admin_events_page'))
{
    function ef_admin_events_page()
    {
        // You might want your admin nav here
        if (function_exists('ef_admin_nav')) ef_admin_nav('Events');
        //get display and filter  info
        $selected_category = ef_request_var('category', '');
        $selected_date = ef_request_var('date', '');
        $mode = ef_request_var('mode', 'list');
        $sort = ef_request_var('sort', 'a');
        $action = ef_request_var('action', '');
        $past = ef_str_to_bool(ef_request_var('past', 'false'));
        $future =  ef_str_to_bool(ef_request_var('future', 'false'));
        $editing = ef_str_to_bool(ef_request_var('editing', 'false'));
        $categories = ef_get_categories();
        $cat_options='';
        foreach ($categories as $cat)
        {
            $sel = $selected_category == $cat->slug ? 'selected' : '';
            $cat_options .= '<option value="' . esc_attr($cat->slug) . '" ' . $sel . '>' . esc_html($cat->name) . '</option>';
        }

        if(!$past and !$future) //we have to show something!
            $future=true;
        //category chooser
        if($action=='save')
        {
            $event_id = ef_request_var('event_id', '');
            print_r($_POST);
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'location' => $_POST['location'],
                'start_time' => $_POST['start_date'].' '.$_POST['start_time'],
                'end_time' =>  $_POST['start_date'].' '.$_POST['end_time'],
                'recurrence_type' => $_POST['recurrence'],
                'parent_event_id' => null,  // for exceptions/overrides
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
            $event_id = ef_request_var('event_id', '');
            $cancel_link = add_query_arg([
                    'page'     => 'eventfolio_events',
                    'category' => $selected_category ?? '',
                    'date'     => $selected_date ?? '',
                    'mode'     => $mode,
                    'sort'     => $sort,
                    'past'     => $past ? 'true' : 'false',
                    'future'   => $future ? 'true' : 'false',
                ], admin_url('admin.php'));
            $pub_selected= ' selected';
            $pri_selected='';
            $rec_options  =  '<option value="" selected>Single</option>';
            $rec_options .=  '<option value="weekly">Weekly</option>';
            $rec_options .=  '<option value="monthly">Monthly</option>';
            $rec_options .=  '<option value="yearly">Yearly</option>';
            $loc_options  =  '<option value="" >To be Determined</option>';
            $locations = ef_get_locations($selected_category);
            $row=array();
            if (intval($event_id)>0)
                $row=ef_get_event(intval($event_id));
            else
                $row=(object)array(
                    'title'=>'',
                    'location'=>'',
                    'start_time'=>date("Y-m-d H:i:s", time()),
                    'end_time'=>date("Y-m-d H:i:s", time()+3600),
                    'description'=>'',
                    );
            $location=ef_request_var('location', $row->location);
            foreach ($locations as $loc)
            {
                $sel = $location == $loc->slug ? 'selected' : '';
                $loc_options .= '<option value="' . esc_attr($loc->slug) . '" ' . $sel . '>' . esc_html($loc->name) . '</option>';
            }
            $start_date = date('Y-m-d', strtotime($row->start_time));
            $start_time = date('H:i', strtotime($row->start_time));
            $end_time   = date('H:i', strtotime($row->end_time));

            echo template_render('event_edit_page.html', array(
                'MODE'               => $mode,
                'SORT'               => $sort,
                'EVENT_ID'           => $event_id,
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
                'DATE'               => ef_request_var('start_date', $start_date),
                'START_TIME'         => ef_request_var('start_time', $start_time),
                'END_TIME'           => ef_request_var('end_time', $end_time),
                ));
        }
        else
        {
            echo template_render('event_page_header.html', array(
                'MODE'             => $mode,
                'SORT'             => $sort,
                'SELECTED_CATEGORY'=> esc_attr($selected_category),
                'CATEGORY_OPTIONS' => $cat_options,
                'PAST'       => $past ? 'true' : 'false',
                'FUTURE'     => $future ? 'true' : 'false',
                ));
            /*if ($event_id)
            {
                echo '<strong>Please select a category.</strong><br>';
            }*/

            //custom based on display mode
            if ($mode=='list')
            {
                echo template_render('event_page_list_header.html', array(
                    'MODE'             => $mode,
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
                ef_admin_events_list($selected_category, $past, $future, $sort);
            }
            elseif ($mode=='calendar')
            {//show calendar interface
                ef_admin_events_calendar($selected_category);
            }
        }
    }
}

function ef_admin_events_list($category, $past, $future, $sort)
{
    echo '<div class="eventfolio-list eventfolio-events-list">';
    // Header row
    echo template_render('event_row.html', array(
        'ICON' => '',
        'TITLE' => 'Title',
        'LOCATION' => 'Location',
        'START' => 'Start',
        'END' => 'End',
        'RECURRING' => 'Recurring',
        'RECURRING_DETAILS' => 'Recurring Details',
        'ACTIONS' => '',
        'HEADER' => 'eventfolio-header',
    ));

    $events = ef_get_events();
    if (!empty($events))
    {
        foreach ($events as $row)
        {
            ef_event_viewer_row($row);
        }
    }
    else
    {
        echo '<div class="eventfolio-row"><div class="eventfolio-col" style="flex: 1;">No events found.</div></div>';
    }
    echo '</div>';
}

function ef_admin_events_calendar($category)
{
}
