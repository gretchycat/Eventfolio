<?php
/*
Plugin Name: Eventfolio
Plugin URI: https://hopefaithless.xyz/eventfolio
Description: Manage, categorize, and scan links across your site with bulk actions and metadata detection.
Version: 0.0.1
Author: Gretchen Maculo
Author URI: https://hopefaithless.xyz
License: MIT
Text Domain: Eventfolio
# Support: https://hopefaithless.xyz/contact
*/
defined('ABSPATH') || exit;
// Define constants early
if (!defined('EF_PLUGIN_PATH'))
{
    define('EF_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('EF_PLUGIN_URL'))
{
    define('EF_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Load all PHP files in the includes directory in sorted order
$files = glob(EF_PLUGIN_PATH . 'includes/*.php');
if ($files)
{
    sort($files, SORT_STRING | SORT_FLAG_CASE); // Ensure alphabetical, case-insensitive
    foreach ($files as $file)
    {
        require_once $file;
    }
}

// Add a Settings link on the Plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=eventfolio') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
});

register_activation_hook(__FILE__, function()
{
    ef_install_tables();
    if (is_user_logged_in())
    {
        $user = wp_get_current_user();
        if ($user && $user->ID)
        {
            update_option('eventfolio_activating_admin_user_id', intval($user->ID));
        }
    }
});

// require_once plugin_dir_path(__FILE__) . 'includes/admin-info.php';
// ...in admin_menu:

add_action('admin_menu', function()
{
    add_menu_page('Eventfolio', 'Eventfolio', 'manage_options', 'eventfolio', 'ef_admin_info_page', 'dashicons-calendar-alt');
    add_submenu_page(
        'eventfolio',
        'Events',
        'Events',
        'manage_options',
        'eventfolio_events',
        'ef_admin_events_page',
    );
    add_submenu_page(
        'eventfolio',
        'Categories',
        'Categories',
        'manage_options',
        'eventfolio_categories',
        'ef_admin_categories_page'
    );
    add_submenu_page(
        'eventfolio',
        'Locations',
        'Locations',
        'manage_options',
        'eventfolio_locations',
        'ef_admin_locations_page'
    );
    add_submenu_page(
        'eventfolio',
        'User Permissions',
        'User Permissions',
        'manage_options',
        'eventfolio_user_permissions',
        'ef_admin_user_permissions_page'
    );
});

function ef_enqueue_admin_css()
{
    $css_url = plugins_url('assets/Eventfolio.css', __FILE__);
    wp_enqueue_style('eventfolio-admin-css', $css_url, [], filemtime(__DIR__ . '/assets/Eventfolio.css'));
}

add_action('admin_enqueue_scripts', 'ef_enqueue_admin_css');

add_action('admin_enqueue_scripts', function() //DEBUG
{ //DEBUG
    wp_enqueue_script( //DEBUG
        'eventfolio-debug', //DEBUG
        plugin_dir_url(__FILE__) . 'assets/debug.js', //DEBUG
        array(), //DEBUG
        filemtime(plugin_dir_path(__FILE__) . 'assets/debug.js'), //DEBUG
        true//DEBUG
    ); //DEBUG
}); //DEBUG

add_action('admin_enqueue_scripts', function($hook)
{
    if(isset($_GET['page']) && strpos($_GET['page'], 'eventfolio') !== false)
    {
        wp_enqueue_media();
        wp_enqueue_script(
            'eventfolio-media',
            plugin_dir_url(__FILE__) . 'assets/Eventfolio.js',
            ['jquery'],
            filemtime(plugin_dir_path(__FILE__) . 'assets/Eventfolio.js'), //DEBUG
            '1.0',
            true
        );
    }
});
