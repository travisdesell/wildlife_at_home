<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/get_expert_observation_table.php');
require_once($cwd . '/user.php');

$video_id = mysql_real_escape_string($_POST['video_id']);
$user_id = mysql_real_escape_string($_POST['user_id']);
$event_type = mysql_real_escape_string($_POST['event_type']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$comments = mysql_real_escape_string($_POST['comments']);

error_log("post: " . json_encode($_POST));

if (!is_special_user__fixme()) {
    error_log("non project scientists cannot submit expert observations.");
    die();
}

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "INSERT INTO expert_observations SET user_id = $user_id, start_time = '$start_time', end_time = '$end_time', event_type ='$event_type', comments = '$comments', video_id = '$video_id'";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$observation_id = mysql_insert_id($wildlife_db);

$query = "UPDATE video_2 SET expert_obs_count = expert_obs_count + 1, expert_finished = IF(expert_finished = 'UNWATCHED', 'WATCHED', expert_finished) WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$response['observation_id'] = $observation_id;
$response['html'] = get_expert_observation_table($video_id, $response['observation_count']);

echo json_encode($response);
?>
