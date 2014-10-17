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

$video_id = mysql_real_escape_string($_POST['video_id']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];
$difficulty = $active_video_id[$species_location_hash]['difficulty'];

//Add this video to the list of watched videos for this user.
$watched_videos_query = "REPLACE INTO watched_videos SET user_id = $user_id, video_id = $video_id, start_time='$watching_start_time', end_time = '" . date('Y-m-d H:i:s', time()) . "', difficulty = '$difficulty'";
$watched_videos_result = query_wildlife_video_db($watched_videos_query);

//Also need to increment view count on the video
$video_query = "UPDATE video_2 SET watch_count = watch_count + 1, crowd_status = 'WATCHED' WHERE id = $video_id";
$video_result = query_wildlife_video_db($video_query);

//Need to set events to completed
$video_query = "UPDATE timed_observations SET completed = true WHERE video_id = $video_id AND user_id = $user_id";
$video_result = query_wildlife_video_db($video_query);

$initial_obs_query = "REPLACE INTO initial_observations(id, event_id, user_id, start_time, end_time, comments, video_id, species_id, tags, location_id, expert, start_time_s, end_time_s, completed, status, auto_generated, report_status, report_comments, response_comments, reporter_id, responder_id, reporter_name, responder_name) SELECT id, event_id, user_id, start_time, end_time, comments, video_id, species_id, tags, location_id, expert, start_time_s, end_time_s, completed, status, auto_generated, report_status, report_comments, response_comments, reporter_id, responder_id, reporter_name, responder_name FROM timed_observations WHERE timed_observations.video_id = $video_id AND timed_observations.user_id = $user_id";
$initial_obs_result = query_wildlife_video_db($initial_obs_query);



/**
 * Give the user a random video in case they dismiss the modal.
 */
unset( $active_video_id[$species_location_hash] );

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = query_boinc_db($user_query);


//Get the list of observations for this video so we can display them to the user
$observations_query = "SELECT timed_observations.*, observation_types.name, observation_types.category FROM timed_observations, observation_types WHERE timed_observations.video_id = $video_id AND observation_types.id = timed_observations.event_id ORDER BY user_id, start_time_s";
error_log($observations_query);

$observations_result = query_wildlife_video_db($observations_query);

$finished_modal_info['observations'] = array();
while ($row = $observations_result->fetch_assoc()) {
    $query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $user_result = query_boinc_db($query);
    $user_row = $user_result->fetch_assoc();

    $row['user_name'] = $user_row['name'];

    $row['event_name'] = $row['category'] . ' - ' . $row['name'];
    $row['start_time'] = substr($row['start_time'], strpos($row['start_time'], ' ') + 1);
    $row['end_time'] = substr($row['end_time'], strpos($row['end_time'], ' ') + 1);

    $finished_modal_info['observations'][] = $row;
}

$previous_row = array();
$last_pos = 0;
for ($i = 0; $i < count($finished_modal_info['observations']); $i++) {
    if ($i == 0 || strcmp($finished_modal_info['observations'][$i]['user_name'], $finished_modal_info['observations'][$i - 1]['user_name']) != 0) {
        $finished_modal_info['observations'][$i]['new_user'] = true;
        $finished_modal_info['observations'][$last_pos]['user_event_count'] = ($i - $last_pos) * 2;
        $last_pos = $i;
    }
}
$finished_modal_info['observations'][$last_pos]['user_event_count'] = ($i - $last_pos) * 2;
$finished_modal_info['video_id'] = $video_id;

$finished_modal_template = file_get_contents($cwd[__FILE__] . "/../templates/finished_video_modal.html");
$mustache_engine = new Mustache_Engine;

$response['html'] = $mustache_engine->render($finished_modal_template, $finished_modal_info);
$response['video_id'] = $video_id;

echo json_encode($response);
?>
