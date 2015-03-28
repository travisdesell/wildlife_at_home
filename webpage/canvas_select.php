<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$result= array();

$res = query_wildlife_video_db("select species from species_lookup");

while ($row = $res->fetch_assoc()) {
	$result[] = $row['species'];
}
echo json_encode($result);
?>
