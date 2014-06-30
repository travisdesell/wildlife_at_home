<?php

require_once("../webpage/wildlife_db.php");
require_once("../webpage/boinc_db.php");

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

//$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
//mysql_select_db("wildlife", $boinc_db);


$query = "SELECT id, required_views, watch_count, crowd_status FROM video_2 WHERE id = 241";
$results = mysql_query($query, $wildlife_db);
if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

while ($row = mysql_fetch_assoc($results)) {
    $video_id = $row['id'];
    $required_views = $row['required_views'];
    $watch_count = $row['watch_count'];
    $crowd_status = $row['crowd_status'];

    echo "id: $video_id - required_views: $required_views, watch_count: $watch_count, crowd_status: $crowd_status\n";

    $to_query = "SELECT id, event_id, user_id, start_time_s, end_time_s, completed, status FROM timed_observations WHERE video_id = $video_id";
    $to_results = mysql_query($to_query, $wildlife_db);
    if (!$to_results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $to_query\n");

    $users = array();
    while ($to_row = mysql_fetch_assoc($to_results)) {
        $to_id = $to_row['id'];
        $to_event_id = $to_row['event_id'];
        $to_user_id = $to_row['user_id'];
        $to_start_time_s = $to_row['start_time_s'];
        $to_end_time_s = $to_row['end_time_s'];
        $to_completed = $to_row['completed'];
        $to_status = $to_row['status'];

        echo "    id: $to_id, event_id: $to_event_id, user_id: $to_user_id, start_time_s: $to_start_time_s, end_time_s: $to_end_time_s, completed: $to_completed, status: $to_status\n";
        if ($to_completed) {
            if (!array_key_exists($to_user_id, $users)) {
                $users[$to_user_id] = 0;
            }
            $users[$to_user_id]++;
        }
    }


    if ( count($users) != $watch_count || 
        ($watch_count > 0 && $watch_count < 5 && $required_views != $watch_count + 1)) {
        echo "    watch_count: $watch_count != count(\$users): " . count($users) . "\n";

        $new_required_views = count($users) + 1;
        if ($crowd_status === 'VALIDATED' || $crowd_status === 'NO_CONSENSUS') {
            $new_required_views--;
        }

        if ($new_required_views < 2) $new_required_views = 2;
        if ($new_required_views > 5) $new_required_views = 5;

        $update_query = "UPDATE video_2 SET required_views = " . $new_required_views . ", watch_count = " . count($users) . " WHERE id = $video_id";
        echo $update_query . "\n";
        $update_results = mysql_query($update_query, $wildlife_db);
        if (!$update_results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $update_query\n");
//        die();
    }

    if (count($users) > 0) {
        echo "    " . json_encode($users) . " - size: " . count($users) . "\n";
    }
}

?>
