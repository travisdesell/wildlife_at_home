<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

$user = csg_get_user();
$user_id = $user['id'];

$video_id = $boinc_db->real_escape_string($_POST['video_id']);
$species_id = $boinc_db->real_escape_string($_POST['species_id']);
$location_id = $boinc_db->real_escape_string($_POST['location_id']);
//$random = $boinc_db->real_escape_string($_POST['random']);

//get a simple hash for the location and species id, so all combinations are unique
//this is good unless we get over 100 locations (which won't happen for awhile, if ever)
$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];
$difficulty = $active_video_id[$species_location_hash]['difficulty'];

$delete_obs_query = "DELETE FROM timed_observations WHERE user_id = $user_id AND video_id = $video_id AND (start_time_s < 0 OR end_time_s < 0)";
$delete_obs_result = query_wildlife_video_db($delete_obs_query);
$delete_count = $wildlife_db->affected_rows;

unset( $active_video_id[$species_location_hash] );

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "', total_events = total_events - $delete_count WHERE id = $user_id";
$user_result = query_boinc_db($user_query);

?>
