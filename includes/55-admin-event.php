<?php
// In 55-admin-event.php (or wherever fits your structure)

if (!function_exists('ef_admin_events_page'))
{
function ef_admin_events_page()
{
    // You might want your admin nav here
    if (function_exists('ef_admin_nav')) ef_admin_nav('Events');
    //get display and filter  info
    $selected_category = ef_request_var('category', '');
    $mode = ef_request_var('mode', 'list');
    $sort = ef_request_var('sort', 'a');
    $past = ef_str_to_bool(ef_request_var('past', 'false'));
    $future =  ef_str_to_bool(ef_request_var('future', 'false'));
    if(!$past and !$future) //we have to show something!
        $future=true;
    //category chooser
    $categories = ef_get_categories();
    $cat_options='';
    foreach ($categories as $cat)
    {
        $sel = $selected_category == $cat->slug ? 'selected' : '';
        $cat_options .= '<option value="' . esc_attr($cat->slug) . '" ' . $sel . '>' . esc_html($cat->name) . '</option>';
    }
    if (isset($_GET['add']))
    {
        echo template_render('event_add_page.html', array(
            'MODE'             => $mode,
            'SORT'             => $sort,
            'SELECTED_CATEGORY'=> esc_attr($selected_category),
            'CATEGORY_OPTIONS' => $cat_options,
            'PAST'       => $past ? 'true' : 'false',
            'FUTURE'     => $future ? 'true' : 'false',
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
            'add'      => 1,
            'category' => $selected_category ?? '',
            'date'     => $selected_date ?? '',
            'MODE'             => $mode,
            'SORT'             => $sort,
            'SELECTED_CATEGORY'=> esc_attr($selected_category),
            'CATEGORY_OPTIONS' => $cat_options,
            'PAST'     => $past ? 'true' : 'false',
            'FUTURE'   => $future ? 'true' : 'false',
        ], admin_url('admin.php'));
        echo template_render('event_page_add_new_button.html', [
            'ADD_EVENT_URL' => esc_url($add_event_url),
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
        foreach ($events as $ev)
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
