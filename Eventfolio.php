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

register_activation_hook(__FILE__, 'ef_install_tables');

// require_once plugin_dir_path(__FILE__) . 'includes/admin-info.php';
// ...in admin_menu:

add_action('admin_menu', function()
{
    add_menu_page('Eventfolio', 'Eventfolio', 'manage_options', 'eventfolio', 'ef_admin_info_page');
    // Add other submenu pages here as you build them
});

add_action('admin_menu', function()
{
    add_menu_page('Eventfolio', 'Eventfolio', 'manage_options', 'eventfolio', 'ef_admin_info_page');
    add_submenu_page('eventfolio', 'Categories', 'Categories', 'manage_options', 'eventfolio_categories', 'ef_admin_categories_page');
    // ...other submenus as needed
});
