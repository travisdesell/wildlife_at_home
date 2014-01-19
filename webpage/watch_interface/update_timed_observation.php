<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');
require_once($cwd . '/watch_interface/observation_table.php');

$user = get_user();
$user_id = $user['id'];

$observation_id = mysql_real_escape_string($_POST['observation_id']);
$video_id = mysql_real_escape_string($_POST['video_id']);
$event_id  = mysql_real_escape_string($_POST['event_id']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$start_time_s = mysql_real_escape_string($_POST['start_time_s']);
$end_time_s = mysql_real_escape_string($_POST['end_time_s']);
$comments = mysql_real_escape_string($_POST['comments']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$tags = mysql_real_escape_string($_POST['tags']);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "UPDATE timed_observations SET start_time = '$start_time', end_time = '$end_time', start_time_s = $start_time_s, end_time_s = $end_time_s, event_id ='$event_id', comments = '$comments', tags = '$tags' WHERE id = $observation_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

//error_log(json_encode($response));

echo json_encode($response);
?>
