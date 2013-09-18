<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/special_user.php');

$video_id = mysql_real_escape_string($_POST['video_id']);

if (!is_special_user()) {
    error_log("non project scientists cannot toggle the expert flag.");
    die();
}

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT expert_obs_count FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);
$previous = $row['expert_obs_count'];

if ($previous == 0) $previous = 'UNWATCHED';
else $previous = 'WATCHED';

$query = "UPDATE video_2 SET expert_finished = IF (expert_finished != 'FINISHED', 'FINISHED', '$previous') WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$query = "SELECT expert_finished FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

echo json_encode(mysql_fetch_assoc($result));

?>
