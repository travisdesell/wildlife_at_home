<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

require $cwd . '/../../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$user = csg_get_user();
$user_id = $user['id'];

$video_id = mysql_real_escape_string($_POST['video_id']);
$query = "SELECT species_id FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);
$row = $result->fetch_assoc();
$species_id = $row['species_id'];


//need to pass in expert_only as the last argument
$response['html'] = get_timed_observation_table($video_id, $user_id, $response['observation_count'], $species_id, csg_is_special_user($user, false));

echo json_encode($response);
?>
