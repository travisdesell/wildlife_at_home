<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');

$result = query_wildlife_video_db("select t1.* from test_image_observations as t1 inner join images as t2 on t1.image_id = t2.id where t2.views >= t2.needed_views and t2.verified <> 1;");

$images = array();
$id = -1;
$i = array();

while ($row = $result->fetch_assoc()) {
	$current_id = $row['image_id'];
	$i[] = $row;
	if ($id == $current_id) {
		$images[] = $i;
		$i = array();
	}
	$id = $current_id;
}

foreach ($images as $image) {
	for ($i = 0; $i < count($image); $i++) {
		error_log("The image id is " . $image[$i]['image_id']);
	}
}
?>
