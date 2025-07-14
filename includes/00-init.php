<?php
if (!defined('ABSPATH')) exit;

// includes/00-init.php

function ef_enqueue_admin_css()
{
    wp_enqueue_style(
        'eventfolio-admin-css',
        EF_PLUGIN_URL . 'assets/Eventfolio.css',
        [],
        filemtime(EF_PLUGIN_PATH . 'assets/Eventfolio.css')
    );
}
function ef_enqueue_public_css_js()
{
    wp_enqueue_style(
        'eventfolio-public-css',
        EF_PLUGIN_URL . 'assets/Eventfolio.css',
        [],
        filemtime(EF_PLUGIN_PATH . 'assets/Eventfolio.css')
    );
    wp_enqueue_script(
        'eventfolio-public-js',
        EF_PLUGIN_URL . 'assets/Eventfolio.js',
        ['jquery'],
        filemtime(EF_PLUGIN_PATH . 'assets/Eventfolio.js'),
        true
    );
}
function ef_enqueue_admin_debug_js()
{
    wp_enqueue_script(
        'eventfolio-debug',
        EF_PLUGIN_URL . 'assets/debug.js',
        [],
        filemtime(EF_PLUGIN_PATH . 'assets/debug.js'),
        true
    );
}
function ef_enqueue_admin_media_js($hook)
{
    if(isset($_GET['page']) && strpos($_GET['page'], 'eventfolio') !== false)
    {
        wp_enqueue_media();
        wp_enqueue_script(
            'eventfolio-media',
            EF_PLUGIN_URL . 'assets/Eventfolio.js',
            ['jquery'],
            filemtime(EF_PLUGIN_PATH . 'assets/Eventfolio.js'),
            true
        );
    }
}

// Register the [eventfolio] shortcode
add_shortcode('eventfolio', function($atts) {
    $atts = shortcode_atts([
        'category'  => '',
        'view'      => 'calendar', // or 'list'
        'date'      => '',         // optional, defaults to today
        'show_nav'  => 'true'      // show category/date switcher
    ], $atts, 'eventfolio_calendar');

    ob_start();
    if ($atts['view'] === 'list') {
        ef_public_events_list($atts['category'], $atts['date']);
    } else {
        ef_public_events_calendar($atts['category'], $atts['date'], $atts['show_nav']);
    }
    return ob_get_clean();
});

// Add rewrite rules for pretty URLs: /eventfolio/category-slug and /eventfolio/category-slug/view
add_action('init', function() {
    add_rewrite_rule(
        '^eventfolio/([^/]+)(?:/(calendar|list))?/?$',
        'index.php?eventfolio_category_slug=$matches[1]&eventfolio_view=$matches[2]&event_id=$matches[3]',
        'top'
    );
});

// Register the custom query vars
add_filter('query_vars', function($vars) {
    $vars[] = 'eventfolio_category_slug';
    $vars[] = 'eventfolio_view';
    $vars[] = 'event_id';
    return $vars;
});

// Template redirect: intercept if the URL matches
add_action('template_redirect', function() {
    $slug = get_query_var('eventfolio_category_slug');
    $view = get_query_var('eventfolio_view') ?: 'calendar'; // default to calendar
    $id = intval(get_query_var('event_id') ?: '0'); // default to calendar

    if ($slug) {
        status_header(200);
        get_header(); // load standard WP header
        echo '<div class="eventfolio-public-calendar">';
        if ($view === 'list') 
        {
            ef_public_events_list($slug);
        } 
        elseif ($view==='calendar') 
        {
            ef_public_events_calendar($slug);
        }
        elseif ($view==='event') 
        {
            ef_public_event_view($slug, $id);
        }
        echo '</div>';
        get_footer(); // load standard WP footer
        exit;
    }
});

// Flush rewrite rules on plugin activation (do this in your main plugin file if needed)
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
