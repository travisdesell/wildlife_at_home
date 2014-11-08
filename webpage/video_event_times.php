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
//$video_id = 4464;

//echo "Video ID: $video_id";
$query = "SELECT user_id, expert, ot.name AS event_name, start_time, end_time FROM timed_observations JOIN observation_types AS ot ON event_id = ot.id WHERE video_id = $video_id AND start_time > 0 AND end_time > 0 AND start_time < end_time ORDER BY expert DESC";
$result = query_wildlife_video_db($query);

$events = array();
while ($row = $result->fetch_assoc()) {
    $name_query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];

    array_push($events, array($name, $row['event_name'], $row['start_time'], $row['end_time']));
}
echo json_encode($events);

?>
