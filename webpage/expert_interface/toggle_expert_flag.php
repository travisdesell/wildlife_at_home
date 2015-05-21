<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

$video_id = $boinc_db->real_escape_string($_POST['video_id']);

if (!is_special_user__fixme()) {
    error_log("non project scientists cannot toggle the expert flag.");
    die();
}

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT count(*) FROM timed_observations WHERE video_id = $video_id";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);
$previous = $row['count(*)'];

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
