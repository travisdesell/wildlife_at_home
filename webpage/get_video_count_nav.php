<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/generate_count_nav.php');
require_once($cwd . '/get_video_segment_query.php');
require_once($cwd . '/user.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$filters = array();
if (array_key_exists('filters', $_POST)) {
    $filters = $_POST['filters'];
}

if (array_key_exists('instructional', $filters) && $filters['instructional'] == 'true') {
    $user = get_user(false);
    $user_id = $user['id'];
} else if ($_POST['all_users'] == 'true') {
    $user = get_user();
    $user_id = $user['id'];

    if (!is_special_user__fixme($user, true)) {
        //don't let non-project scientists display all videos
        error_log("NOT SPECIAL USER!");
        die();
    }
} else {
    $user = get_user();
    $user_id = $user['id'];
}

create_filter($filters, $filter, $reported_filter);

$display_nav_numbers = true;
if (empty($filters)) {
    $display_nav_numbers = false;
    $max_items = 0;
} else {
    //error_log("the filter is: " . $filter);

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $query = "";
    if (strlen($reported_filter) > 0) $reported_filter = " AND " . $reported_filter;
    if (strlen($filter) > 0) $filter = " AND " . $filter;

    if (array_key_exists('instructional', $filters) && $filters['instructional'] == 'true') {
        $query = "SELECT count(vs2.id) FROM video_segment_2 vs2 RIGHT JOIN observations o ON (vs2.crowd_obs_count > 0 $reported_filter $filter AND o.status = 'EXPERT' AND o.video_segment_id = vs2.id)";

    } else if ($_POST['all_users'] == 'true') {
        $query = "SELECT count(vs2.id) FROM video_segment_2 vs2 RIGHT JOIN observations o ON (vs2.crowd_obs_count > 0 $reported_filter $filter AND o.video_segment_id = vs2.id)";
        error_log ("query: $query");

    } else {
        if ($_POST['show_hidden'] == 'false') $filter .= " AND o.hidden = false";

        $query = "SELECT count(vs2.id) FROM video_segment_2 vs2 RIGHT JOIN observations o ON (vs2.crowd_obs_count > 0 $reported_filter $filter AND o.user_id = $user_id AND o.video_segment_id = vs2.id)";
    }

    //echo "<!-- $query -->\n";


    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    }

    $row = mysql_fetch_assoc($result);

    $max_items = $row['count(vs2.id)'];
}

generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers);

?>
