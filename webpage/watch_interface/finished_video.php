<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

//require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
//Mustache_Autoloader::register();

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');
require_once($cwd . '/watch_interface/observation_table.php');

$user = get_user();
$user_id = $user['id'];

$video_id = mysql_real_escape_string($_POST['video_id']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];
$difficulty = $active_video_id[$species_location_hash]['difficulty'];


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

//Add this video to the list of watched videos for this user.
$watched_videos_query = "INSERT INTO watched_videos SET user_id = $user_id, video_id = $video_id, start_time='$watching_start_time', end_time = '" . date('Y-m-d H:i:s', time()) . "', difficulty = '$difficulty'";
$watched_videos_result = attempt_query_with_ping($watched_videos_query, $wildlife_db);
if (!$watched_videos_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $watched_videos_query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $watched_videos_query\n");
}

//Also need to increment view count on the video
$video_query = "UPDATE video_2 SET watch_count = watch_count + 1, crowd_status = 'WATCHED' WHERE id = $video_id";
$video_result = attempt_query_with_ping($video_query, $wildlife_db);
if (!$video_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video_query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video_query\n");
}

//Need to set events to completed
$video_query = "UPDATE timed_observations SET completed = true WHERE video_id = $video_id AND user_id = $user_id";
$video_result = attempt_query_with_ping($video_query, $wildlife_db);
if (!$video_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video_query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video_query\n");
}

/**
 * Give the user a random video in case they dismiss the modal.
 */

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

unset( $active_video_id[$species_location_hash] );

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = attempt_query_with_ping($user_query, $boinc_db);
if (!$user_result) {
    error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
    die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
}


//Get the list of observations for this video so we can display them to the user
$observations_query = "SELECT timed_observations.*, observation_types.name, observation_types.category FROM timed_observations, observation_types WHERE timed_observations.video_id = $video_id AND observation_types.id = timed_observations.event_id ORDER BY user_id, start_time_s";
error_log($observations_query);

$observations_result = attempt_query_with_ping($observations_query, $wildlife_db);
if (!$observations_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observations_query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observations_query\n");
}

$finished_modal_info['observations'] = array();
while ($row = mysql_fetch_assoc($observations_result)) {
    $query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $user_result = attempt_query_with_ping($query, $boinc_db);
    $user_row = mysql_fetch_assoc($user_result);

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

$finished_modal_template = file_get_contents($cwd . "/templates/finished_video_modal.html");
$mustache_engine = new Mustache_Engine;

$response['html'] = $mustache_engine->render($finished_modal_template, $finished_modal_info);
$response['video_id'] = $video_id;

echo json_encode($response);
?>
