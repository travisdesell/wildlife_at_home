<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/generate_count_nav.php');
require_once($cwd . '/get_video_filter.php');
require_once($cwd . '/user.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);
$video_filter_text = mysql_real_escape_string($_POST['video_filter_text']);
$event_filter_text = mysql_real_escape_string($_POST['event_filter_text']);

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$user = get_user();
$query = "";

if ($video_filter_text == '' && $event_filter_text == '') {
    if (is_special_user__fixme($user, true)) {
        $query = "SELECT count(v2.id) FROM video_2 AS v2";
    } else {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 INNER JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE v2.timed_obs_count > 0";
    }

//    error_log("COUNT NAV QUERY: $query");
} else {
    create_filter($video_filter_text, $event_filter_text, $filter_query, $has_observation_query);

    if (is_special_user__fixme($user, true)) {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 WHERE " . $filter_query;
    } else {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 INNER JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE " . $filter_query;
    }

//    error_log("COUNT NAV QUERY: $query");
}

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$row = mysql_fetch_assoc($result);

$max_items = $row['count(v2.id)'];

generate_count_nav($max_items, $video_min, $video_count, true);

?>
