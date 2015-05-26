<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/user.php");

$video_id = $boinc_db->real_escape_string($_POST['video_id']);
$is_private = $boinc_db->real_escape_string($_POST['is_private']);

error_log("TOGGLING PRIVATE VIDEO -- video_id: $video_id, is_private: $is_private");

$user = csg_get_user();
if (!csg_is_special_user($user, true)) {
    error_log("non project scientists cannot submit expert observations.");
    die();
}

if ($is_private == 'false') {
    $release_to_public = 'false';
} else {
    $release_to_public = 'true';
}

$query = "UPDATE video_2 SET release_to_public = $release_to_public WHERE id = $video_id";
error_log($query);

$result = query_wildlife_video_db($query);


$query = "UPDATE video_segment_2 SET release_to_public = $release_to_public WHERE video_id = $video_id";
error_log($query);

$result = query_wildlife_video_db($query);

$response['is_private'] = $release_to_public;

echo json_encode($response);

?>
