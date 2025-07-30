<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('ef_event_viewer_row'))
{
    function ef_event_viewer_row($row, $edit_url, $delete_url, $edit_series='', $new_instance='')
    {
        if(!$row) return;
        $icon='<img src="'.$row->image_url.'" width="100%">';
        $actions_url='';
        if(ef_user_has_permission('edit_event'))    //is organizer?
        {
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
        }
        elseif(ef_user_has_permission('view_event'))    //is organizer?
        {
            $actions_html='<div>';
            $actions_html.='<a href="'.$edit_url.'">View</a> ';
            $actions_html.='</div>';
        }
        $public=ef_is_public($row->id);
        if(ef_user_has_permission('view_event')or $public)
        {
            $location='To Be Determined';
            $loc=ef_get_location_by_slug($row->location);
            if($loc)
            {
                $location=$loc->name;
            }
        }
        else
            $location='private';
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $start_date = date_i18n($date_format, strtotime($row->start_time));
        $start_time = date_i18n($time_format, strtotime($row->start_time));
        $end_time   = date_i18n($time_format, strtotime($row->end_time));
        echo template_render('event_row.html', array(
            'ICON' => $icon,
            'TITLE' => esc_html($row->title),
            'LOCATION' => esc_html($location),
            'DATE' => ef_recurrence_human($start_date,$row->recurrence_type),
            'START' => esc_html($start_time),
            'END' => esc_html($end_time),
            'RECURRING' => esc_html($row->recurrence_type),
            'RECURRING_DETAILS' => '',
            'ACTIONS' => $actions_html,
            'HEADER' => '',
        ));
    }
}

