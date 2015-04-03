<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

$user = csg_get_user();
$user_id = $user['id'];

$video_id = mysql_real_escape_string($_POST['video_id']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$random = mysql_real_escape_string($_POST['random']);

//get a simple hash for the location and species id, so all combinations are unique
//this is good unless we get over 100 locations (which won't happen for awhile, if ever)
$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];
$difficulty = $active_video_id[$species_location_hash]['difficulty'];

if ($random == 'true') {
    unset( $active_video_id[$species_location_hash] );
} else {
    //check to see if the next video has been processed, otherwise show a random video.
    $active_video_id[$species_location_hash]['difficulty'] = 'easy';
    $active_video_id[$species_location_hash]['start_time'] = date('Y-m-d H:i:s', time());

    $video_query = "select v2.id from video_2 v1, video_2 v2 where v1.id = $video_id and v2.animal_id = v1.animal_id AND v2.start_time > v1.start_time AND v2.release_to_public = true AND v2.processing_status != 'UNWATERMARKED' AND NOT EXISTS(SELECT * FROM watched_videos wv WHERE wv.video_id = v2.id AND wv.user_id = $user_id) ORDER BY v2.start_time limit 1";
    $video_result = query_wildlife_video_db($video_query);
    error_log($video_query);

    $row = $video_result->fetch_assoc();

    if ($row) {
        //We're now viewing the next video in the sequence.  Add an empty event for it.
        $active_video_id[$species_location_hash]['video_id'] = $row['id'];

        $is_special_user = csg_is_special_user($user, true);
        $query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '', end_time = '', event_id ='', comments = '', video_id = '" . $active_video_id[$species_location_hash]['video_id'] . "', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
        $result = query_wildlife_video_db($query);

        //we added an observation for the user so increment their total events
        $user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
        $user_result = query_boinc_db($user_query);
    } else {
        unset( $active_video_id[$species_location_hash] );
    }
}

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = query_boinc_db($user_query);

$response['success'] = true;
echo json_encode($response);

?>
