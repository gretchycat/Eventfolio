<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_event_viewer_row'))
{
    function ef_event_viewer_row($row)
    {
        if(!$row) return;
        {
            $icon='<img src="'.$row->icon.'" >';
            $actions='<a href="">Edit</a>&nbsp;<a href="">Delete</a>';
            echo template_render('event_row.html', array(
                'ICON' => $icon,
                'TITLE' => esc_html($row->title),
                'LOCATION' => esc_html($row->location),
                'START' => esc_html($row->start_time),
                'END' => esc_html($row->end_time),
                'RECURRING' => esc_html($row->recurrence_type),
                //'RECURRING_DETAILS' => esc_html($recurring_details),
                'ACTIONS' => $actions_html,
                'HEADER' => '',
            ));
       }
    }
}

