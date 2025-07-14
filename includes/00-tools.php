<?php

if (!defined('ABSPATH')) exit;
// --- Admin navigation ---
function ef_admin_nav($selected='')
{
    $pages = [
        'eventfolio'                => 'Info',
        'eventfolio_events'         => 'Events',
        'eventfolio_categories'     => 'Categories',
        'eventfolio_locations'      => 'Locations',
        'eventfolio_user_permissions'    => 'User Permissions',
    ];
    echo '<div class="ef-admin-nav" style="margin-bottom:1em;">';
    foreach ($pages as $slug => $label)
    {
        $url = admin_url('admin.php?page=' . $slug);
        $class = 'button';
        if ($label == $selected)
            $class .= ' ef-admin-nav-selected';
        echo '<a href="' . esc_url($url) . '" class="' . $class . '" style="margin-right:0.5em;">' . esc_html($label) . '</a>';
    }
    echo '</div>';
}

function ef_request_var($key, $default = null)
{
    if (isset($_POST[$key])) return $_POST[$key];
    if (isset($_GET[$key])) return $_GET[$key];
    return $default;
}

function ef_str_to_bool($val)
{
    // Normalize and trim input
    if (is_bool($val)) return $val; // Already a bool
    $val = strtolower(trim((string)$val));
    if ($val === '1' || $val === 'true' || $val === 'yes' || $val === 'on')
    {
        return true;
    }
    if ($val === '0' || $val === 'false' || $val === 'no' || $val === 'off' || $val === '')
    {
        return false;
    }
    // Fallback: treat any non-empty string as true (PHP default)
    return (bool) $val;
}

function ef_style()
{
    add_action('admin_enqueue_scripts', 'ef_enqueue_admin_css');
}

// Define roles and their canonical permissions
function ef_get_role_definitions()
{
    return [
        'guest' => ['view_teaser'],
        'user' => ['view_event', 'rsvp'],
        'organizer' => ['view_event', 'rsvp', 'create_event', 'edit_event', 'delete_event', 'assign_category', 'manage_own_rsvps'],
        'admin' => ['view_event', 'rsvp', 'create_event', 'edit_event', 'delete_event', 'assign_category', 'manage_own_rsvps', 'manage_all_rsvps', 'manage_permissions', 'manage_categories', 'manage_users']
    ];
}

function ef_role_priority($role) {
    $priority = ['guest' => 0, 'user' => 1, 'organizer' => 2, 'admin' => 3];
    return $priority[$role] ?? -1;
}

function ef_best_matching_role($csv_perms)
{
    $role_definitions=ef_get_role_definitions();
    $user_perms = explode(',', $csv_perms);
    $best_role='';
    if(strlen($csv_perms)==0)
    {
        $count=999;
        foreach ($role_definitions as $role => $perms)
        {
            $rc=count($perms);
            if ($rc<$count)
            {
                $count=$rc;
                $best_role=$role;
            }
        }
    }
    else
    {
        $best_score=999.0; //0 is best
        $common=0;
        foreach ($role_definitions as $role => $perms)
        {
            $overlap=count(array_intersect($user_perms, $perms));
            $count=count($perms);
            $ucount=count($user_perms);
            $score=abs(((float)$overlap/(float)$count)-1.0);
            if ($best_role=='')
                $best_role=$role;
/*            error_log('---------');
            error_log('Testing '.$role);
            error_log('Counts: ov.'.$overlap.' rl.'.$count);
            error_log('User: '.implode(',', $user_perms));
            error_log('Role: '.implode(',', $perms));
            error_log('Score: '.$score);*/
            if ($score <= $best_score)
            {
                if ($overlap>$common)
                {
                    $common=$overlap;
                    $best_score=$score;
                    $best_role=$role;
                }
            }
        }
    }
    return $best_role;
}

function template_render($template_file, $vars = array())
{
    $template_path = plugin_dir_path(__FILE__) . 'templates/' . $template_file;
    if (!file_exists($template_path)) {
        error_log( "<!-- Template $template_path not found -->");
        return "<!-- Template $template_file not found -->";
    }
    $template = file_get_contents($template_path);

    // Replace placeholders like __VARNAME__ with the variable's value
    foreach ($vars as $key => $value) {
        $template = str_replace('__' . strtoupper($key) . '__', $value, $template);
    }

    return $template;
}

function ef_recurrence_human($date, $type)
{
    $dt = new DateTime($date);

    if ($type === 'weekly') {
        // Example: "Sunday"
        return $dt->format('l');
    }

    if ($type === 'monthly') {
        // Example: "2nd Sunday"
        // Find day of week and its ordinal position this month
        $dayOfWeek = $dt->format('l');
        $day = (int)$dt->format('j');
        // Which week is this day in? (1-based)
        $ordinal = ceil($day / 7);
        // Suffix for ordinal (1st, 2nd, 3rd, 4th, etc.)
        $suffix = ['th','st','nd','rd','th','th','th','th','th','th'];
        $ordinalStr = $ordinal . ($suffix[$ordinal] ?? 'th');
        return "{$ordinalStr} {$dayOfWeek}";
    }

    if ($type === 'yearly') {
        // Example: "June 15"
        return $dt->format('F j');
    }

    // Default: just the date
    return $dt->format('Y-m-d');
}

function ef_get_next_event_times($time, $event_start, $event_end, $recurrence_type)
{
    $ref = new DateTime($time);
    $start = new DateTime($event_start);
    $end   = new DateTime($event_end);
    $duration = $start->diff($end);

    switch (strtolower($recurrence_type)) {
        case 'weekly':
            // Same weekday & time as $event_start, next after $ref
            $dow = (int)$start->format('w');   // 0=Sun, 1=Mon...
            $hour = (int)$start->format('H');
            $min  = (int)$start->format('i');
            $sec  = (int)$start->format('s');
            $next = clone $ref;
            // Go to next (or this) weekday at the correct time
            $next->setTime($hour, $min, $sec);
            $days_ahead = ($dow - (int)$next->format('w') + 7) % 7;
            if ($days_ahead === 0 && $next <= $ref) $days_ahead = 7;
            $next->modify("+$days_ahead days");
            if ($next <= $ref) $next->modify('+7 days');
            $next_end = clone $next;
            $next_end->add($duration);
            break;

        case 'monthly':
                     // Find Nth weekday in the month of $event_start
            $weekday = (int)$start->format('w'); // 0=Sun, 1=Mon, etc
            $day     = (int)$start->format('j');
            $nth     = 1 + floor(($day - 1) / 7); // e.g., 3rd Sunday
            $hour    = (int)$start->format('H');
            $min     = (int)$start->format('i');
            $sec     = (int)$start->format('s');

            // Start from current ref month
            $cur = clone $ref;
            $cur->setTime($hour, $min, $sec);

            // Helper: find nth weekday of given month/year
            $find_nth_weekday = function($year, $month, $weekday, $nth) {
                // 1st of the month
                $first = new DateTime(sprintf('%04d-%02d-01', $year, $month));
                $first_wday = (int)$first->format('w');
                $offset = ($weekday - $first_wday + 7) % 7;
                $date = 1 + $offset + 7 * ($nth - 1);
                // Check for overflow (e.g., 5th Sunday in Feb may not exist)
                if ($date > cal_days_in_month(CAL_GREGORIAN, $month, $year)) return false;
                return sprintf('%04d-%02d-%02d', $year, $month, $date);
            };

            $found = false;
            for ($i = 0; $i < 24; ++$i) { // check up to 2 years in advance
                $year = (int)$cur->format('Y');
                $month = (int)$cur->format('m');
                $nth_date = $find_nth_weekday($year, $month, $weekday, $nth);
                if ($nth_date) {
                    $candidate = new DateTime($nth_date . sprintf(' %02d:%02d:%02d', $hour, $min, $sec));
                    if ($candidate > $ref) {
                        $found = $candidate;
                        break;
                    }
                }
                $cur->modify('+1 month');
            }
            if (!$found) {
                // fallback: return original (should not happen)
                return [
                    'start' => $event_start,
                    'end'   => $event_end,
                ];
            }
            $next = $found;
            $next_end = clone $next;
            $next_end->add($duration);
            break;
        case 'yearly':
            // Use same month, day, and time as $event_start
            $mon = (int)$start->format('m');
            $dom = (int)$start->format('d');
            $hour = (int)$start->format('H');
            $min  = (int)$start->format('i');
            $sec  = (int)$start->format('s');
            $year = (int)$ref->format('Y');
            $next = new DateTime(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $mon, $dom, $hour, $min, $sec));
            if ($next <= $ref) {
                $next->modify('+1 year');
            }
            $next_end = clone $next;
            $next_end->add($duration);
            break;

        default:
            return [
                'start' => $event_start,
                'end'   => $event_end,
            ];
    }

    return [
        'start' => $next->format('Y-m-d H:i:s'),
        'end'   => $next_end->format('Y-m-d H:i:s'),
    ];
}

function options_list($list, $selected='')
{
    $options='';
    foreach ($list as $key=>$item)
    {
        if(is_object($item))
        {
            $sel = $selected == $item->slug ? 'selected' : '';
            $options .= '<option value="' . esc_attr($item->slug) . '" ' . $sel . '>' . esc_html($item->name) . '</option>';
        }
        elseif(is_string($item))
        {
            $sel = $selected == $item ? 'selected' : '';
            $options .= '<option value="'.$item.'" '.$sel.'>'.$key.'</option>';
        }
    }
    return $options;
}

function create_links($list)
{
    $buffer='';
    foreach($list as $caption=>$link)
    {
        $buffer .= '<a href="'.$link.'">'.$caption.'</a>';
    }
    return $buffer;;
}

function ef_get_week_start_sunday($date)
{
    // Accepts 'Y-m-d' or anything strtotime accepts.
    $timestamp = strtotime($date);
    // Get numeric day of week: 0 = Sunday, 6 = Saturday
    $day_of_week = date('w', $timestamp);
    // Subtract $day_of_week days to get back to Sunday
    $sunday_timestamp = strtotime("-{$day_of_week} days", $timestamp);
    return date('Y-m-d', $sunday_timestamp);
}

function ef_get_next_day($date)
{
    // Accepts any date format strtotime understands
    return date('Y-m-d', strtotime($date . ' +1 day'));
}

function eventfolio_generate_map_url($location)
{
    // $location can be a DB row (object) or associative array
    $address = '';
    if (is_object($location)) {
        $address = $location->address ?: $location->name;
    } elseif (is_array($location)) {
        $address = $location['address'] ?: $location['name'];
    }
    if (!$address) return '';
    $query = urlencode($address);
    return "https://www.google.com/maps/search/?api=1&query={$query}";
}

function eventfolio_generate_osm_url($location)
{
    // Accepts object (row) or array
    $address = '';
    if (is_object($location)) {
        $address = $location->address ?: $location->name;
    } elseif (is_array($location)) {
        $address = $location['address'] ?: $location['name'];
    }
    if (!$address) return '';
    $query = urlencode($address);
    return "https://www.openstreetmap.org/search?query={$query}";
}

function ef_recursive_unslash($data)
{
    if (is_array($data)) {
        // If array, recurse on each value
        foreach ($data as $key => $value) {
            $data[$key] = ef_recursive_unslash($value);
        }
        return $data;
    } elseif (is_object($data)) {
        // If object, clone and recurse on each property
        $cloned = clone $data;
        foreach ($cloned as $key => $value) {
            $cloned->$key = ef_recursive_unslash($value);
        }
        return $cloned;
    } elseif (is_string($data)) {
        // If string, unslash
        return wp_unslash($data);
    } else {
        // For anything else (int, bool, null), return as-is
        return $data;
    }
}
