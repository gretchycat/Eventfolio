<?php

if (!defined('ABSPATH')) exit;

// Display a public-facing list of upcoming events.
function ef_public_events_list($category_slug = '', $date = '', $show_nav = true)
{
    if($date=='')
        $date=date_i18n("Y-m-d", current_time('timestamp'));
    ef_events_list($category_slug, $date);
    // TODO: Render a list UI for public users.
    // Parameters:
    //   $category_slug - filter events by this category slug (string)
    //   $date          - show events near this date (YYYY-MM-DD), optional
    //   $show_nav      - if true, render navigation controls (bool)
}


