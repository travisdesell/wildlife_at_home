<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

$user = csg_get_user();
$user_id = $user['id'];

$video_id = mysql_real_escape_string($_POST['video_id']);

$query = "UPDATE video_2 SET needs_revalidation = 1 WHERE id = $video_id";
error_log("QUERY: $query");
$result = query_wildlife_video_db($query);

$response['html'] = '';

echo json_encode($response);
?>
