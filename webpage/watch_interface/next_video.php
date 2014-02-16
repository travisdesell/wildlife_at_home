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
$difficulty = $active_video_id[$species_location_hash]['difficulty'];

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

if ($random == 'true') {
    unset( $active_video_id[$species_location_hash] );
} else {
    //check to see if the next video has been processed, otherwise show a random video.
    $active_video_id[$species_location_hash]['difficulty'] = 'easy';
    $active_video_id[$species_location_hash]['start_time'] = date('Y-m-d H:i:s', time());

    $video_query = "select v2.id from video_2 v1, video_2 v2 where v1.id = $video_id and v2.animal_id = v1.animal_id AND v2.start_time > v1.start_time AND v2.release_to_public = true AND v2.processing_status != 'UNWATERMARKED' ORDER BY v2.start_time limit 1";
    $video_result = attempt_query_with_ping($video_query, $wildlife_db);
    error_log($video_query);

    $row = mysql_fetch_assoc($video_result);

    if ($row) {
        //We're now viewing the next video in the sequence.  Add an empty event for it.
        $active_video_id[$species_location_hash]['video_id'] = $row['id'];

        $is_special_user = is_special_user__fixme($user, true);
        $query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '', end_time = '', event_id ='', comments = '', video_id = '" . $active_video_id[$species_location_hash]['video_id'] . "', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
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
    } else {
        unset( $active_video_id[$species_location_hash] );
    }
}

$user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
$user_result = attempt_query_with_ping($user_query, $boinc_db);
if (!$user_result) {
    error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
    die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
}

?>
