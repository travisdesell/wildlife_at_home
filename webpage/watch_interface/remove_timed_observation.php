<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

$user = csg_get_user();
$user_id = $user['id'];

$observation_id = $boinc_db->real_escape_string($_POST['observation_id']);

$user_query = "UPDATE user SET total_events = total_events - 1 WHERE id = $user_id";
$user_result = query_boinc_db($user_query);

$query = "SELECT video_id FROM timed_observations WHERE id = $observation_id";
$result = query_wildlife_video_db($query);

$row = $result->fetch_assoc();
$video_id = $row['video_id'];

$query = "DELETE FROM timed_observations WHERE id = $observation_id";
//error_log("query: " . $query);
$result = query_wildlife_video_db($query);

$query = "UPDATE video_2 SET timed_obs_count = timed_obs_count - 1 WHERE id = $video_id";
$result = query_wildlife_video_db($query);

$response['html'] = '';
//$response['html'] = get_timed_observation_table($video_id, $user_id, $response['observation_count']);

echo json_encode($response);
?>
