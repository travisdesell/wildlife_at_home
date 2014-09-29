<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

require $cwd . '/../../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$user = csg_get_user();
$user_id = $user['id'];
$is_special_user = csg_is_special_user($user, true);

$video_id = mysql_real_escape_string($_POST['video_id']);
$event_id  = mysql_real_escape_string($_POST['event_id']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$comments = mysql_real_escape_string($_POST['comments']);

$user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
$user_result = query_boinc_db($user_query);

$query = "SELECT species_id, location_id FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);

$row = $result->fetch_assoc();
$species_id = $row['species_id'];
$location_id = $row['location_id'];

$query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '$start_time', end_time = '$end_time', start_time_s = '-1', end_time_s = '-1', event_id ='$event_id', comments = '$comments', video_id = '$video_id', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
$result = query_wildlife_video_db($query);
$observation_id = $wildlife_db->insert_id;

$query = "UPDATE video_2 SET timed_obs_count = timed_obs_count + 1, expert_finished = IF(expert_finished = 'UNWATCHED', 'WATCHED', expert_finished) WHERE id = $video_id";
$result = query_wildlife_video_db($query);

$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

echo json_encode($response);
?>
