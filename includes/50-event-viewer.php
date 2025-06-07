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
            echo template_render('event-row.html', array(
                'ICON' => $icon_html,
                'TITLE' => esc_html($title),
                'LOCATION' => esc_html($location),
                'START' => esc_html($start),
                'END' => esc_html($end),
                'RECURRING' => esc_html($recurring),
                'RECURRING_DETAILS' => esc_html($recurring_details),
                'ACTIONS' => $actions_html,
                'HEADER' => '',
            ));
       }
    }
}

