<?php

header('Content-type: application/json');

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

if (!isset($video_id)) {
    $video_id = 6440;
}

//echo "Video ID: $video_id";
$query = "SELECT ot.name AS event_name, (UNIX_TIMESTAMP(vid.start_time) + start_time_s) AS start_time, (UNIX_TIMESTAMP(vid.start_time) + end_time_s) AS end_time FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id JOIN observation_types AS ot ON event_id = ot.id JOIN video_2 AS vid ON vid.id = comp.video_id WHERE comp.video_id = $video_id AND comp.algorithm_id = 3 AND comp.start_time_s > 0 AND comp.start_time_s <= comp.end_time_s AND comp.version_id = alg.main_version_id";
$result = query_wildlife_video_db($query);

$events = array();
while ($row = $result->fetch_assoc()) {
    array_push($events, array($row['event_name'], (int)$row['start_time'], (int)$row['end_time']));
}
echo json_encode($events);

?>
