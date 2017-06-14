<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

if (count($argv) < 3) {
    die("Usage: php " . $argv[0] . " test.json directory\n");
}

$json = json_decode(file_get_contents($argv[1]), true);
$dir  = $argv[2];

echo "Copying mosaic images to $dir...\n";
foreach ($json as $image_id => &$msi_id) {
    $result = query_wildlife_video_db("SELECT archive_filename FROM uas_blueshift_images WHERE image_id=$image_id");
    $row = null;

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $result = query_wildlife_video_db("SELECT archive_filename FROM images WHERE id=$image_id");
        if (!$result || $result->num_rows <= 0)
            continue;

        $row = $result->fetch_assoc();
    }

    $filename = $row['archive_filename'];
    if (!copy($filename, "$dir/msi$msi_id.png")) {
        echo "Failed to copy $filename to $dir/msi$msi_id.png\n";
    }
}

?>
