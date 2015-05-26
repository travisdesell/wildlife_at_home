<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/user.php");

$observation_id = $boinc_db->real_escape_string($_POST['observation_id']);
$comments = $boinc_db->real_escape_string($_POST['comments']);

$user = csg_get_user();
$reporter_id = $user['id'];
$reporter_name = $user['name'];


$query = "UPDATE timed_observations SET report_comments = '$comments', report_status = 'REPORTED', reporter_id = $reporter_id, reporter_name = '$reporter_name' WHERE id = $observation_id";
$result = query_wildlife_video_db($query);

?>
