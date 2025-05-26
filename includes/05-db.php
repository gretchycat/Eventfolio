<?php

// Ensure this file is not accessed directly.
if (!defined('ABSPATH')) exit;

global $wpdb;

// Table names
$ef_events           = $wpdb->prefix . 'ef_events';
$ef_categories       = $wpdb->prefix . 'ef_categories';
$ef_event_categories = $wpdb->prefix . 'ef_event_categories';
$ef_signups          = $wpdb->prefix . 'ef_signups';
$ef_user_permissions = $wpdb->prefix . 'ef_user_permissions';

// --- Table creation functions ---

function ef_create_events_table()
{
    global $wpdb, $ef_events;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $ef_events (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        description text,
        start_time datetime NOT NULL,
        end_time datetime NOT NULL,
        location varchar(255),
        created_by bigint(20) unsigned NOT NULL,
        status varchar(32) NOT NULL DEFAULT 'draft',
        teaser text,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ef_create_categories_table()
{
    global $wpdb, $ef_categories;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $ef_categories (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        slug varchar(128) NOT NULL UNIQUE,
        name varchar(128) NOT NULL,
        description text,
        visibility varchar(16) NOT NULL DEFAULT 'public',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ef_create_event_categories_table()
{
    global $wpdb, $ef_event_categories;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $ef_event_categories (
        event_id bigint(20) unsigned NOT NULL,
        category_slug varchar(128) NOT NULL,
        PRIMARY KEY (event_id, category_slug),
        KEY category_slug (category_slug)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ef_create_signups_table()
{
    global $wpdb, $ef_signups;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $ef_signups (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        event_id bigint(20) unsigned NOT NULL,
        user_id bigint(20) unsigned NOT NULL,
        status varchar(32) NOT NULL DEFAULT 'pending',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function ef_create_user_permissions_table()
{
    global $wpdb, $ef_user_permissions;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $ef_user_permissions (
        user_id bigint(20) unsigned NOT NULL,
        permissions text NOT NULL,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// --- Hooked triggers for user lifecycle ---

add_action('wp_login', function($user_login, $user)
{
    global $wpdb, $ef_user_permissions;
    $exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $ef_user_permissions WHERE user_id = %d", $user->ID));
    if (!$exists)
    {
        $wpdb->insert($ef_user_permissions, [
            'user_id' => $user->ID,
            'permissions' => 'view_event',
            'updated_at' => current_time('mysql')
        ]);
    }
}, 10, 2);

add_action('delete_user', function($user_id)
{
    global $wpdb, $ef_user_permissions;
    $wpdb->delete($ef_user_permissions, ['user_id' => $user_id]);
});

// --- Utility: install all tables ---
function ef_install_tables()
{
    ef_create_events_table();
    ef_create_categories_table();
    ef_create_event_categories_table();
    ef_create_signups_table();
    ef_create_user_permissions_table();
}

// --- Add an event (basic version) ---
function ef_add_event($args)
{
    global $wpdb, $ef_events;
    $defaults = [
        'title' => '',
        'description' => '',
        'start_time' => '',
        'end_time' => '',
        'location' => '',
        'created_by' => get_current_user_id(),
        'status' => 'draft',
        'teaser' => ''
    ];
    $data = wp_parse_args($args, $defaults);
    $wpdb->insert($ef_events, $data);
    return $wpdb->insert_id;
}

// --- Update user permissions ---
function ef_update_user_permissions($user_id, $permissions_csv)
{
    global $wpdb, $ef_user_permissions;
    $wpdb->replace($ef_user_permissions, [
        'user_id' => $user_id,
        'permissions' => $permissions_csv,
        'updated_at' => current_time('mysql')
    ]);
}
