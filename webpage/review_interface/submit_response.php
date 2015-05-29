<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/user.php");

$observation_id = $boinc_db->real_escape_string($_POST['observation_id']);
$response_comments = $boinc_db->real_escape_string($_POST['response_comments']);
$status = $boinc_db->real_escape_string($_POST['validation_status']);

$user = csg_get_user();
$responder_id = $user['id'];
$responder_name = $user['name'];

if (!csg_is_special_user($user, true)) return;

/**
 *  If event swapped between invalid, unvalidated, valid then change valid event count for user
 */

$query = "UPDATE timed_observations SET response_comments = '$response_comments', report_status = 'RESPONDED', responder_id = $responder_id, responder_name = '$responder_name', status = '$status' WHERE id = $observation_id";
$result = query_wildlife_video_db($query);

?>
