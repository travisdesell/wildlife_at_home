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
$is_special_user = is_special_user__fixme($user, true);

$video_id = mysql_real_escape_string($_POST['video_id']);
$event_id  = mysql_real_escape_string($_POST['event_id']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$comments = mysql_real_escape_string($_POST['comments']);


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
$user_result = attempt_query_with_ping($user_query, $boinc_db);
if (!$user_result) {
    error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
    die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
}


$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT species_id, location_id FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}
$row = mysql_fetch_assoc($result);
$species_id = $row['species_id'];
$location_id = $row['location_id'];

$query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '$start_time', end_time = '$end_time', start_time_s = '-1', end_time_s = '-1', event_id ='$event_id', comments = '$comments', video_id = '$video_id', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}
$observation_id = mysql_insert_id($wildlife_db);

$query = "UPDATE video_2 SET timed_obs_count = timed_obs_count + 1 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}



$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

echo json_encode($response);
?>
