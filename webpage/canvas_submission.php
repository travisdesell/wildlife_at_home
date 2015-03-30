<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

//TEMPORARY//
//$user_id = 0;

error_log("id: " . $_POST['some_id']);

$data = json_decode($_POST['data'], true);

$nothing_here = $data['nothing_here'];

if(!$nothing_here)
{
	for ($i = 0; $i < count($data); $i++) {
    	error_log("data[" . $i . "] height: " . $data["$i"]['height'] . 
        	",  width: " . $data["$i"]['width'] .
        	", left: " . $data["$i"]['left'] .
        	", top: " . $data["$i"]['top'] . 
		", nest: " . $data["$i"]['nest'] . 
		", species: " . $data["$i"]['species'] . 
		", comments: " . $data["$i"]['comments'] . 
		", image id: " . $data["$i"]['image_id'] .
		", nothing_here: " . $data["$i"]['nothing_here']);
	

	$image_id = $data[$i][image_id];
	$height = $data[$i][height];
	$height = (int)preg_replace('/\D/', '', $height);
	$width = $data[$i][width];
	$width = (int)preg_replace('/\D/', '', $width );
	$top = $data[$i][top];
	$top = (int)preg_replace('/\D/', '', $top );
	$left = $data[$i][left];
	$left = (int)preg_replace('/\D/', '', $left );
	$species = $data[$i][species];
	$comments = mysql_escape_string($data[$i][comments]);
	$nest = $data[$i][nest];
	$nothing = $data[$i][nothing_here];

	if($nest) $nest = 1;
	else $nest = 0;

	query_wildlife_video_db("INSERT INTO test_image_observations " .
				"(user_id, image_id, height, width, top, left_side, species_id, comments, nest, nothing_here) " .
			"VALUES ($user_id, $image_id, $height, $width, $top, $left, '$species', '$comments', $nest, $nothing);");
	}
}
else
{
	error_log(" nothing_here: " . $data['nothing_here'] .
		", image id: " . $data['image_id'] .
		", comments: " . $data['comments']);

	$comments = mysql_escape_string($data[comments]);
	
	query_wildlife_video_db("INSERT INTO test_image_observations " .
				"(user_id, image_id, comments, nothing_here) " .
			" VALUES ($user_id, $data[image_id], '$comments', $data[nothing_here]);");
}


//TODO Get Picture info from the database as well - BCC

error_log("got data from canvas_test.php: '$data'");
//TODO Actually add this info to the database
//Or hire/force someone to manually watch the error log and keep track - BCC

//TODO verify data from users -Jaeden


?>
