<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=msi_true_counts.csv");
header("Pragma: no-cache");
header("Expires: 0");

$outstream = fopen("php://output", "w");

// only grab Jennifer's results for now
$result = query_wildlife_video_db("SELECT DISTINCT msi.id AS msi_id, io.id AS io_id FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON io.image_id = msi.image_id WHERE io.user_id = 152553");

while ($row = $result->fetch_assoc()) {
    $msi_id = $row['msi_id'];
    $io_id  = $row['io_id'];

    $white_result =  query_wildlife_video_db("SELECT COUNT(*) AS whites FROM image_observations AS io INNER JOIN image_observation_boxes AS iob ON iob.image_observation_id = io.id WHERE io.id=$io_id AND iob.species_id=2");
    $blue_result  =  query_wildlife_video_db("SELECT COUNT(*) AS blues FROM image_observations AS io INNER JOIN image_observation_boxes AS iob ON iob.image_observation_id = io.id WHERE io.id=$io_id AND iob.species_id=1000000");

    $whites = ($white_result->fetch_assoc())['whites'];
    $blues  = ($blue_result->fetch_assoc())['blues'];

    fputcsv($outstream, array($msi_id, $whites, $blues));
}

fclose($outstream);

exit();

?>
