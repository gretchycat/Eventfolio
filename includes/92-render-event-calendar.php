<?php

if (!defined('ABSPATH')) exit;

// Display a full-page public calendar for a group/category.
function ef_public_events_calendar($category_slug = '', $date = '', $show_nav = true)
{
    if($date=='')
        $date=date_i18n("Y-m-d", current_time('timestamp'));
    ef_events_calendar($category_slug, $date);
    // TODO: Render a calendar UI for public users.
    // Parameters:
    //   $category_slug - filter events by this category slug (string)
    //   $date          - current/starting date (YYYY-MM-DD), defaults to today
    //   $show_nav      - if true, render navigation controls (bool)
}


