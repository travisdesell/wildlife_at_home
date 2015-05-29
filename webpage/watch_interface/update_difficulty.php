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
$difficulty = $boinc_db->real_escape_string($_POST['difficulty']);

//get a simple hash for the location and species id, so all combinations are unique
//this is good unless we get over 100 locations (which won't happen for awhile, if ever)
$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];

//update the active video's difficulty
$active_video_id[$species_location_hash]['difficulty'] = $difficulty;

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = query_boinc_db($user_query);

$response['success'] = true;
echo json_encode($response);
?>
