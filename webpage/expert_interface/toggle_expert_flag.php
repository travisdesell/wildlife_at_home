<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/user.php");

$video_id = $boinc_db->real_escape_string($_POST['video_id']);

$user = csg_get_user();
if (!csg_is_special_user($user, true)) {
    error_log("non project scientists cannot toggle the expert flag.");
    die();
}
$query = "SELECT count(*) FROM timed_observations WHERE video_id = $video_id";

$result = query_wildlife_video_db($query);

$row = $result->fetch_assoc();
$previous = $row['count(*)'];

if ($previous == 0) $previous = 'UNWATCHED';
else $previous = 'WATCHED';

$query = "UPDATE video_2 SET expert_finished = IF (expert_finished != 'FINISHED', 'FINISHED', '$previous') WHERE id = $video_id";
$result = query_wildlife_video_db($query);

$query = "SELECT expert_finished FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);

echo json_encode($result->fetch_assoc());

?>
