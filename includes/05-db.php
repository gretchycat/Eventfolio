<?php

if (!defined('ABSPATH')) exit;

// --- Table name constants (set only once) ---
if (!defined('EF_EVENTS_TABLE')) {
    global $wpdb;
    define('EF_EVENTS_TABLE',           $wpdb->prefix . 'eventfolio_events');
    define('EF_CATEGORIES_TABLE',       $wpdb->prefix . 'eventfolio_categories');
    define('EF_EVENT_CATEGORIES_TABLE', $wpdb->prefix . 'eventfolio_event_categories');
    define('EF_SIGNUPS_TABLE',          $wpdb->prefix . 'eventfolio_signups');
    define('EF_USER_PERMISSIONS_TABLE', $wpdb->prefix . 'eventfolio_user_permissions');
}

// --- Table creation functions ---
function ef_create_events_table()
{
    $table = EF_EVENTS_TABLE;
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
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
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
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
    $table = EF_EVENT_CATEGORIES_TABLE;
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
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
    $table = EF_SIGNUPS_TABLE;
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
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
    $table = EF_USER_PERMISSIONS_TABLE;
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
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
    $table = EF_USER_PERMISSIONS_TABLE;
    global $wpdb;
    $exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE user_id = %d", $user->ID));
    if (!$exists)
    {
        $wpdb->insert($table, [
            'user_id' => $user->ID,
            'permissions' => 'view_event',
            'updated_at' => current_time('mysql')
        ]);
    }
}, 10, 2);

add_action('delete_user', function($user_id)
{
    $table = EF_USER_PERMISSIONS_TABLE;
    global $wpdb;
    $wpdb->delete($table, ['user_id' => $user_id]);
});

// --- Utility: install all tables ---
function ef_install_tables()
{
    ef_create_events_table();
    ef_create_categories_table();
    ef_ensure_categories_exist();
    ef_create_event_categories_table();
    ef_create_signups_table();
    ef_create_user_permissions_table();
}

/**********************************************************/
/*                                                        */
/*                    event processing                    */
/*                                                        */
/**********************************************************/
function ef_add_event($args)
{
    $table = EF_EVENTS_TABLE;
    global $wpdb;
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
    $wpdb->insert($table, $data);
    return $wpdb->insert_id;
}

/**********************************************************/
/*                                                        */
/*                   ucategory processing                 */
/*                                                        */
/**********************************************************/
function ef_ensure_categories_exist()
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    if (!$count) {
        $wpdb->insert($table, [
            'slug' => 'events',
            'name' => 'Events',
            'visibility' => 'public',
            'description' => ''
        ]);
    }
}

function ef_insert_category($slug, $name, $visibility, $description)
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    return $wpdb->insert($table, [
        'slug' => $slug,
        'name' => $name,
        'visibility' => $visibility,
        'description' => $description
    ]);
}

function ef_update_category($id, $name, $visibility, $description)
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    return $wpdb->update($table, [
        'name' => $name,
        'visibility' => $visibility,
        'description' => $description
    ], ['id' => $id]);
}

function ef_delete_category($id)
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    return $wpdb->delete($table, ['id' => $id]);
}

function ef_category_slug_exists($slug)
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE slug = %s", $slug));
}

function ef_get_categories()
{
    $table = EF_CATEGORIES_TABLE;
    global $wpdb;
    return $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");
}

/**********************************************************/
/*                                                        */
/*              user permission processing                */
/*                                                        */
/**********************************************************/
function ef_update_user_permissions($user_id, $permissions_csv)
{
    $table = EF_USER_PERMISSIONS_TABLE;
    global $wpdb;
    $wpdb->replace($table, [
        'user_id' => $user_id,
        'permissions' => $permissions_csv,
        'updated_at' => current_time('mysql')
    ]);
}

function ef_sync_user_permissions_table()
{
    global $wpdb;

    $table = EF_USER_PERMISSIONS_TABLE;

    // Add guest row (user_id = 0) if not present
    $guest_row = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE user_id = %d", 0));
    if (!$guest_row)
    {
        ef_reset_user_permissions(0);
    }
    // Get all WordPress users
    $user_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->users}");
    // Ensure every real user has a row
    foreach ($user_ids as $uid)
    {
        $exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $table WHERE user_id = %d", $uid));
        if (!$exists)
        {
            ef_reset_user_permissions($uid);
        }
    }
}

function ef_get_user_permissions($user_id)
{
    global $wpdb, $ef_user_permissions;
    $table = EF_USER_PERMISSIONS_TABLE;
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT permissions FROM $table WHERE user_id = %d",
            $user_id
        )
    );
    if ($row && isset($row->permissions))
    {
        return $row->permissions;
    }
    return '';
}

function ef_get_all_user_permissions()
{
    global $wpdb;
    $table = EF_USER_PERMISSIONS_TABLE;
    return $wpdb->get_results("SELECT * FROM $table");
}

function ef_reset_user_permissions($user_id)
{
    $roles = ef_get_role_definitions();
    $activator_id=intval(get_option('eventfolio_activating_admin_user_id'));
    if ($user_id == 0) { // guest
        $perms = $roles['guest'];
    }
    elseif ($user_id == $activator_id)
    { // activating admin
        $perms = $roles['admin'];
    }
    else
    { // all others
        $perms = ef_get_user_permissions(0);
    }
    ef_update_user_permissions($user_id, implode(',', $perms));
}

