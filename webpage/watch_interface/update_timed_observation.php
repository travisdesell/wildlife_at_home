<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

require $cwd[__FILE__] . '/../../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$user = csg_get_user();
$user_id = $user['id'];

$observation_id = mysql_real_escape_string($_POST['observation_id']);
$video_id = mysql_real_escape_string($_POST['video_id']);
$event_id  = mysql_real_escape_string($_POST['event_id']);
$start_time = mysql_real_escape_string($_POST['start_time']);
$end_time = mysql_real_escape_string($_POST['end_time']);
$start_time_s = mysql_real_escape_string($_POST['start_time_s']);
$end_time_s = mysql_real_escape_string($_POST['end_time_s']);
$comments = mysql_real_escape_string($_POST['comments']);
$tags = mysql_real_escape_string($_POST['tags']);

$query = "SELECT species_id FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);
$row = $result->fetch_assoc();
$species_id = $row['species_id'];

$query = "UPDATE timed_observations SET start_time = '$start_time', end_time = '$end_time', start_time_s = $start_time_s, end_time_s = $end_time_s, event_id ='$event_id', comments = '$comments', tags = '$tags' WHERE id = $observation_id";
$result = query_wildlife_video_db($query);

$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

//error_log(json_encode($response));

echo json_encode($response);
?>
