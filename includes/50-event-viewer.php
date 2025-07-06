<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_event_viewer_row'))
{
    function ef_event_viewer_row($row, $edit_url, $delete_url, $edit_series='', $new_instance='')
    {
        if(!$row) return;
        {
            $icon='<img src="'.$row->image_url.'" width="100%">';
            if ($new_instance)
            {
                $actions_html='<div>';
                $actions_html.='<a href="'.$new_instance.'">New&nbsp;Instance</a> ';
                $actions_html.='<a href="'.$edit_url.'">Edit&nbsp;Series</a>  ';
                $actions_html.='<a href="'.$delete_url.'">Delete</a> ';
                $actions_html.='</div>';
            }
            else
            {
                $actions_html='<div>';
                $actions_html.='<a href="'.$edit_url.'">Edit</a> ';
                if($edit_series)
                    $actions_html.='<a href="'.$edit_series.'">Edit&nbsp;Series</a> ';
                $actions_html.='<a href="'.$delete_url.'">Delete</a> ';
                $actions_html.='</div>';
            }
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

