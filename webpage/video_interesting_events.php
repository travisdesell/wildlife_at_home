<?php

header('Content-type: text/plain');

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

if (!isset($video_id)) {
    $video_id = 14515;
}

$query = "SELECT event_id, (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE video_id = $video_id AND (event_id = 6 OR event_id = 6 OR event_id = 7 OR event_id = 11 OR event_id = 18 OR event_id = 26 OR event_id = 30 OR event_id = 34 OR event_id = 35 OR event_id = 36 OR event_id = 37 OR event_id = 41) AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.end_time) > TO_SECONDS(obs.start_time) AND expert = 1";
$result = query_wildlife_video_db($query);

while ($row = $result->fetch_assoc()) {
    echo $row['event_id'] . " ";
    echo $row['start_time'] . " ";
    echo "\n";
    echo $row['event_id'] . " ";
    echo $row['end_time'];
    echo "\n";
}

?>
