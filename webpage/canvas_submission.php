<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

//$user = csg_get_user();
//$user_id = $user['id'];

error_log("id: " . $_POST['some_id']);

$data = json_decode($_POST['data'], true);

for ($i = 0; $i < count($data); $i++) {
    error_log("data[" . $i . "] height: " . $data["$i"]['height'] . 
        ",  width: " . $data["$i"]['width'] .
        ", left: " . $data["$i"]['left'] .
        ", top: " . $data["$i"]['top'] . 
	", nest: " . $data["$i"]['nest'] . 
	", species: " . $data["$i"]['species'] . 
	", comments: " . $data["$i"]['comments'] . 
	", image id: " . $data["$i"]['image_id']);
}

error_log("got data from canvas_test.php: '$data'");


?>
