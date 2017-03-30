<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=msi_locations.bin");
header("Pragma: no-cache");
header("Expires: 0");

$outstream = fopen("php://output", "w");

// only grab Jennifer's results for now
$result = query_wildlife_video_db("SELECT DISTINCT msi.id AS msi_id, io.id AS io_id FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON io.image_id = msi.image_id WHERE io.user_id = 152553 ORDER BY msi_id");

$num_msi = $result->num_rows;
fwrite($outstream, pack("V", $num_msi));

while ($row = $result->fetch_assoc()) {
    $msi_id = $row['msi_id'];
    $io_id  = $row['io_id'];

    fwrite($outstream, pack("V", $msi_id));

    $msi_result = query_wildlife_video_db("SELECT * FROM image_observation_boxes WHERE image_observation_id = $io_id");
    $msi_count = $msi_result->num_rows;
    fwrite($outstream, pack("V", $msi_count));

    while ($msi_row = $msi_result->fetch_assoc()) {
        fwrite($outstream, pack("VVVVV", $msi_row['species_id'], $msi_row['x'], $msi_row['y'], $msi_row['width'], $msi_row['height']));
    }
}

fclose($outstream);

exit();

?>
