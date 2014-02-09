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

$video_id = mysql_real_escape_string($_POST['video_id']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$random = mysql_real_escape_string($_POST['random']);

//get a simple hash for the location and species id, so all combinations are unique
//this is good unless we get over 100 locations (which won't happen for awhile, if ever)
$species_location_hash = ($location_id * 100) + $species_id;

$active_video_id = json_decode( $user['active_video_id'], true );
$watching_start_time = $active_video_id[$species_location_hash]['start_time'];


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

//Add this video to the list of watched videos for this user.
$watched_videos_query = "INSERT INTO watched_videos SET user_id = $user_id, video_id = $video_id, start_time='$watching_start_time', end_time = '" . date('Y-m-d H:i:s', time()) . "'";
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



$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

if ($random == 'true') {
    unset( $active_video_id[$species_location_hash] );
} else {
    //check to see if the next video has been processed, otherwise show a random video.
    $active_video_id[$species_location_hash]['video_id']++;
    $active_video_id[$species_location_hash]['start_time'] = date('Y-m-d H:i:s', time());

    $video_result = attempt_query_with_ping("SELECT release_to_public, processing_status FROM video_2 WHERE id = " . $active_video_id[$species_location_hash]['video_id'], $wildlife_db);
    $row = mysql_fetch_assoc($video_result);

    error_log("release_to_public: '" . $row['release_to_public'] . ", processing_status: '" . $row['processing_status'] . "'\n");

    if ($row['release_to_public'] == 0 || $row['processing_status']  == "UNWATERMARKED") {
        error_log("NEXT VIDEO NON-PUBLIC (" . ($row['release_to_public'] == 0) . ") or UNWATERMARKED (" . ($row['processing_status'] == "UNWATERMARKED") . ")\n");
        unset( $active_video_id[$species_location_hash] );
    } else {
        //We're now viewing the next video in the sequence.  Add an empty event for it.

        $is_special_user = is_special_user__fixme($user, true);
        $query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '', end_time = '', event_id ='', comments = '', video_id = '$video_id', species_id = $species_id, location_id = $    location_id, expert = $is_special_user";
        $result = attempt_query_with_ping($query, $wildlife_db);
        if (!$result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        }

        //we added an observation for the user so increment their total events
        $user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
        $user_result = attempt_query_with_ping($user_query, $boinc_db);
        if (!$user_result) {
            error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
            die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
        }

    }
}


$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = attempt_query_with_ping($user_query, $boinc_db);
if (!$user_result) {
    error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
    die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
}

?>
