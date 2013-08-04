<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$observation_id = mysql_real_escape_string($_POST['observation_id']);

error_log("post: " . json_encode($_POST));

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT video_id FROM expert_observations WHERE id = $observation_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);
$video_id = $row['video_id'];

$query = "DELETE FROM expert_observations WHERE id = $observation_id";
error_log("query: " . $query);
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$query = "UPDATE video_2 SET expert_obs_count = expert_obs_count - 1, expert_finished = IF(expert_obs_count = 0 AND expert_finished = 'WATCHED', 'UNWATCHED', expert_finished) WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$query = "SELECT expert_obs_count FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$response['observation_count'] = $row['expert_obs_count'];
echo json_encode($response);
?>
