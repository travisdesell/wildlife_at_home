#!/usr/bin/env php

<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$rshift = 233.0 / 150.0;
$gshift = 255.0 / 189.0;
$bshift = 236.0 / 190.0;

echo "\nrunning shift_images.php with $rshift, $gshift, $bshift\n";

$result = query_wildlife_video_db("SELECT i.id, i.archive_filename FROM mosaic_split_images AS msi INNER JOIN images AS i ON i.id = msi.image_id WHERE i.year=2015 AND (SELECT COUNT(*) FROM uas_blueshift_images WHERE image_id=i.id) = 0");
while (($row = $result->fetch_assoc())) {
    $image_id = $row['id'];
    $archive_filename = $row['archive_filename'];

    // create the blueshift filename
    $pathparts = pathinfo($archive_filename);
    $blueshift_filename = $pathparts['dirname'] . '/' . $pathparts['filename'] . '_blueshift.' . $pathparts['extension'];

    // load in the image and shift the values
    echo "\nOpening $archive_filename...\n";
    $img = new Imagick($archive_filename);
    echo "\tDone.\n";

    echo "Converting $archive_filename...\n";
    $imageIterator = $img->getPixelIterator();
    foreach ($imageIterator as $row => $pixels) {
        foreach ($pixels as $column => $pixel) {
            $color = $pixel->getColor();
            $r = intval($color['r'] * $rshift);
            $g = intval($color['g'] * $gshift);
            $b = intval($color['b'] * $bshift);

            if ($r > 255) $r = 255;
            if ($g > 255) $g = 255;
            if ($b > 255) $b = 255;

            $pixel->setColor("rgba($r, $g, $b, 0)");
        }

        $imageIterator->syncIterator();
    }
    echo "\tDone.\n";

    // write out the image and clear
    echo "Saving to $blueshift_filename...\n";
    if ($img->writeImage($blueshift_filename)) {
        $temp_result = query_wildlife_video_db("INSERT INTO uas_blueshift_images (image_id, archive_filename) VALUES ($image_id, '$blueshift_filename')");
        if ($temp_result == NULL) {
            echo "\tError inserting into the database!\n";
        }
    }
    echo "\tDone.\n";

    $img->clear();
}

?>
