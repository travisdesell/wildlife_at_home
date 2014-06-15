<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));


require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

$video_id = mysql_real_escape_string($_POST['video_id']);
$is_private = mysql_real_escape_string($_POST['is_private']);

error_log("TOGGLING PRIVATE VIDEO -- video_id: $video_id, is_private: $is_private");

if (!is_special_user__fixme()) {
    error_log("non project scientists cannot submit expert observations.");
    die();
}

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

if ($is_private == 'false') {
    $release_to_public = 'false';
} else {
    $release_to_public = 'true';
}

$query = "UPDATE video_2 SET release_to_public = $release_to_public WHERE id = $video_id";
error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");


$query = "UPDATE video_segment_2 SET release_to_public = $release_to_public WHERE video_id = $video_id";
error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$response['is_private'] = $release_to_public;

echo json_encode($response);

?>
