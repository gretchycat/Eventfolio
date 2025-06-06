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
    echo '<div class="eventfolio-event-controls">';
    echo '<div class="eventfolio-category-form">';
    echo '<form method="get" action="">';
    echo '<input type="hidden" name="page" value="eventfolio_events">';
    echo '<input type="hidden" name="mode" value="'.$mode.'">';
    echo '<input type="hidden" name="sort" value="'.$sort.'">';
    echo '<input type="hidden" name="past" value="'.$past.'">';
    echo '<input type="hidden" name="future" value="'.$future.'">';
    echo '<select name="category" onchange="this.form.submit()">';
    echo '<option value="">All Categories</option>';
    foreach ($categories as $cat)
    {
        $sel = $selected_category == $cat->slug ? 'selected' : '';
        echo '<option value="' . esc_attr($cat->slug) . '" ' . $sel . '>' . esc_html($cat->name) . '</option>';
    }
    echo '</select>';
    echo '</form>';
    echo '</div>';
    //display mode
    echo '<div class="eventfolio-mode-form">';
    echo '<form method="get" action="" style="margin-bottom:1em; display: flex; align-items: center; gap: 8px;">';
    echo '<input type="hidden" name="page" value="eventfolio_events">';
    echo '<input type="hidden" name="sort" value="' . esc_attr($sort) . '">';
    echo '<input type="hidden" name="past" value="' . esc_attr($past) . '">';
    echo '<input type="hidden" name="future" value="' . esc_attr($future) . '">';
    echo '<input type="hidden" name="category" value="' . esc_attr($selected_category) . '">';
    echo '<button type="submit" name="mode" value="list" class="eventfolio-toggle-btn'.($mode === 'list' ? ' active' : '').'" title="List view">';
    echo '<span class="dashicons dashicons-list-view"></span>';
    echo '</button>';
    echo '<button type="submit" name="mode" value="calendar" class="eventfolio-toggle-btn'.($mode === 'calendar' ? ' active' : '').'" title="Calendar view">';
    echo '<span class="dashicons dashicons-calendar-alt"></span>';
    echo '</button>';
    echo '</form>';
    echo '</div>';
    echo '</div>';
    //custom based on display mode
    if ($mode=='list')
    {
        echo '<div class="eventfolio-event-controls">';
        //past, future
        echo '<div class="eventfolio-category-form">';
        echo '<form method="get" action="" style="display: flex; align-items: center; gap: 12px; margin: 0;">';
        echo '<input type="hidden" name="page" value="eventfolio_events">';
        echo '<input type="hidden" name="mode" value="' . esc_attr($mode) . '">';
        echo '<input type="hidden" name="sort" value="' . esc_attr($sort) . '">';
        echo '<input type="hidden" name="category" value="' . esc_attr($selected_category) . '">';
        echo '<label style="display: flex; align-items: center; gap: 4px;">';
        echo    '<input type="checkbox" name="past" value="true" ' . ($past ? 'checked' : '') . ' onchange="this.form.submit()">';
        echo    'Past';
        echo '</label>';
        echo '<label style="display: flex; align-items: center; gap: 4px;">';
        echo    '<input type="checkbox" name="future" value="true" ' . ($future ? 'checked' : '') . ' onchange="this.form.submit()">';
        echo    'Future';
        echo '</label>';
        echo '</form>';
        echo '</div>';
        //sort order
        echo '<div class="eventfolio-category-form">';
        echo '<form method="get" action="" style="display: flex; align-items: center; gap: 10px; margin: 0;">';
        echo '<input type="hidden" name="page" value="eventfolio_events">';
        echo '<input type="hidden" name="mode" value="' . esc_attr($mode) . '">';
        echo '<input type="hidden" name="category" value="' . esc_attr($selected_category) . '">';
        echo '<input type="hidden" name="past" value="' . ($past ? 'true' : 'false') . '">';
        echo '<input type="hidden" name="future" value="' . ($future ? 'true' : 'false') . '">';
        // Toggle button: a/d (ascending/descending)
        $next_sort = ($sort === 'a') ? 'd' : 'a';
        $sort_icon = ($sort === 'a')
            ? '<span class="dashicons dashicons-arrow-down-alt"></span>'
            : '<span class="dashicons dashicons-arrow-up-alt"></span>';
        $sort_label = ($sort === 'a') ? 'Ascending' : 'Descending';
        echo '<button type="submit" name="sort" value="' . $next_sort . '"
            style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 3px; font-size: 15px;">'
            . $sort_icon . $sort_label . '</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        //show list interface
        ef_admin_events_list($selected_category, $past, $future, $sort);
    }
    elseif ($mode=='calendar')
    {//show calendar interface
        ef_admin_events_calendar($selected_category);
    }
}
}

function ef_admin_events_list($category, $past, $future, $sort)
{
    // Example fetch (replace with your data source)
    $events = ef_get_events();

    echo '<div class="eventfolio-list eventfolio-events-list">';

echo '<div class="eventfolio-row-noheight eventfolio-header eventfolio-events-header">';
echo    '<div class="eventfolio-col eventfolio-events-col-icon"></div>';   // No label for image/icon
echo    '<div class="eventfolio-col eventfolio-events-col-main">';
echo        '<div class="eventfolio-list">';
echo           '<div class="eventfolio-row">';
echo                '<div class="eventfolio-events-col-title eventfolio-col">Title</div>';
echo                '<div class="eventfolio-events-col-location eventfolio-col">Location</div>';
echo           '</div>';
echo            '<div class="eventfolio-row">';
echo                '<div class="eventfolio-events-col-title eventfolio-col">Start</div> ';
echo                '<div class="eventfolio-events-col-location eventfolio-col">Repeat</div>';
echo            '</div>';
echo            '<div class="eventfolio-row">';
echo               '<div class="eventfolio-events-col-title eventfolio-col">End</div> ';
echo               '<div class="eventfolio-events-col-location eventfolio-col">Repeat Details</div>';
echo            '</div>';
echo        '</div>';
echo    '</div>';
echo    '<div class="eventfolio-col eventfolio-events-col-actions">Actions</div>';
echo '</div>';

    echo '</div>';
}

function ef_admin_events_calendar($category)
{
}
