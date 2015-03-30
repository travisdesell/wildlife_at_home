<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$result= array();
$project_id= $_POST['p'];
if (!$project_id) $project_id = 1;

$res = query_wildlife_video_db("select species, species_id from species_lookup where species_id = any (select species_id from species_project_lookup where project_id=$project_id)");

while ($row = $res->fetch_assoc()) {
	$result[] = array( "name" => $row['species'],
			"id" => $row['species_id']);
}
echo json_encode($result);
?>
