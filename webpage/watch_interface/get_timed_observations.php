<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');
require_once('/home/tdesell/wildlife_at_home/webpage/watch_interface/observation_table.php');

$user = get_user();
$user_id = $user['id'];

$video_id = mysql_real_escape_string($_POST['video_id']);
$species_id = mysql_real_escape_string($_POST['species_id']);

$response['html'] = get_timed_observation_table($video_id, $user_id, $response['observation_count'], $species_id, 0);

echo json_encode($response);
?>
