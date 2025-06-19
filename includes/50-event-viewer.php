<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_event_viewer_row'))
{
    function ef_event_viewer_row($row, $edit_url, $delete_url)
    {
        if(!$row) return;
        {
            $icon='<img src="'.$row->icon.'" >';

            $actions_html='<a href="'.$edit_url.'">Edit</a>&nbsp;';
            $actions_html.='<a href="'.$delete_url.'">Delete</a>';
            $start_date = date('Y-m-d', strtotime($row->start_time));
            $start_time = date('H:i', strtotime($row->start_time));
            $end_time   = date('H:i', strtotime($row->end_time));
            echo template_render('event_row.html', array(
                'ICON' => $icon,
                'TITLE' => esc_html($row->title),
                'LOCATION' => esc_html($row->location),
                'DATE' => ef_recurrence_human($start_date, $row->recurrence_type),
                'START' => esc_html($start_time),
                'END' => esc_html($end_time),
                'RECURRING' => esc_html($row->recurrence_type),
                'RECURRING_DETAILS' => '',
                'ACTIONS' => $actions_html,
                'HEADER' => '',
            ));
       }
    }
}

