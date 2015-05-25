<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/get_video_filter.php');

require_once($cwd[__FILE__] . '/generate_count_nav.php');

$video_min = $boinc_db->real_escape_string($_POST['video_min']);
$video_count = $boinc_db->real_escape_string($_POST['video_count']);
$video_filter_text = $boinc_db->real_escape_string($_POST['video_filter_text']);
$event_filter_text = $boinc_db->real_escape_string($_POST['event_filter_text']);
$showing_all_videos = $boinc_db->real_escape_string($_POST['showing_all_videos']);
$video_id_filter = $boinc_db->real_escape_string($_POST['video_id_filter']);

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = csg_get_user();
$query = "";

if ($video_filter_text == '' && $event_filter_text == '' && !is_numeric($video_id_filter)) {
    if (csg_is_special_user($user, true) && $showing_all_videos == 'true') {
        $query = "SELECT count(v2.id) FROM video_2 AS v2";
    } else {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 INNER JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE v2.timed_obs_count > 0";
    }

//    error_log("COUNT NAV QUERY: $query");
} else {
    create_filter($video_filter_text, $event_filter_text, $filter_query, $has_observation_query, $video_id_filter);

    if (csg_is_special_user($user, true) && $showing_all_videos == 'true') {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 WHERE " . $filter_query;
    } else {
        $query = "SELECT count(v2.id) FROM video_2 AS v2 INNER JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE " . $filter_query;
    }

//    error_log("COUNT NAV QUERY: $query");
}

$result = query_wildlife_video_db($query);

$row = $result->fetch_assoc();

$max_items = $row['count(v2.id)'];

generate_count_nav($max_items, $video_min, $video_count, true);

?>
