<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

if (count($argv) < 4) {
    print "Usage: php " . $argv[0] . " outbase percent size\n";
}

$outbase = $argv[1];
$percent = intval($argv[2]);
$size = intval($argv[3]);

if ($percent <= 0 || $percent >= 100) {
    die("Percent must be between 1 and 99");
}

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/tools/idx.php");

try {
    // size x size x 3 (rgb square)
    $idx = new IDX(0x08, array($size, $size, 3));
    $species_idx = new IDX(0x0C, array());
    $msilist = array();
} catch (Exception $e) {
    die($e->getMessage());
}

$data = array();

$bg_ratio = 95;
$bg_multiplier = ((float)$bg_ratio) / ((float)(100 - $bg_ratio));
$halfsize = $size / 2.0;

function add_image(array &$data, $image_id, $image_filename, $image_width, $image_height, $msi_id)
{
    if (!isset($data[$image_id])) {
        // query for a shifted image
        $temp_result = query_wildlife_video_db("SELECT archive_filename FROM uas_blueshift_images WHERE image_id=$image_id");
        if ($temp_result && $temp_result->num_rows > 0) {
            $temp_row = $temp_result->fetch_assoc();
            $image_filename = $temp_row['archive_filename'];
        }

        $data[$image_id] = array(
            'image_width' => $image_width,
            'image_height' => $image_height,
            'filename' => $image_filename,
            'msi_id' => $msi_id,
            'boxes' => array()
        );

        return true;
    }

    return false;
}

echo "Getting expert results...\n";

$result = query_wildlife_video_db("SELECT vmo.*, msi.id AS mosaic_split_image_id FROM view_mosaic_observations AS vmo INNER JOIN mosaic_split_images AS msi ON msi.image_id = vmo.image_id WHERE is_expert=1");

if (!$result) {
    die("No results returned.");
}

echo $result->num_rows . "\n";
while ($row = $result->fetch_assoc()) {
    $image_id = $row['image_id'];
    $msi_id = $row['mosaic_split_image_id'];

    if (add_image($data, $image_id, $row['image_filename'], $row['image_width'], $row['image_height'], $msi_id)) {
        if (rand(1, 100) <= $percent) {
            $msilist[$image_id] = $msi_id;
        }
    }

    // normalize the x / y / w / h
    $x = $row['x'] + intval($row['width'] / 2.0 - $halfsize);
    $y = $row['y'] + intval($row['height'] / 2.0 - $halfsize);

    // make sure x and y are within range
    if ($x < 0) $x = 0;
    if (($x + $size) >= $row['image_width']) $x = $row['image_width'] - $size - 1;
    if ($y < 0) $y = 0;
    if (($y + $size) >= $row['image_height']) $y = $row['image_height'] - $size - 1;

    $data[$image_id]['boxes'][] = array(
        'x' => $x,
        'y' => $y,
        'species' => $row['species_id']
    );
}

$numobs = count($data);
$numtrain = count($msilist);
echo "Total images: $numobs\n";
echo "Training size: $numtrain\n";

function has_overlap(int $x, int $y, int $size, array &$boxes) {
    $x2 = $x + $size;
    $y2 = $y + $size;

    foreach ($boxes as &$box) {
        $bx = $box['x']; $bx2 = $bx + $size;
        $by = $box['y']; $by2 = $by + $size;

        if ($x < $bx2 && $x2 > $bx && $y > $by2 && $y2 < $by) {
            return true;
        }
    }

    return false;
}

// do just background
$result = query_wildlife_video_db("SELECT msi.image_id, msi.id, msi.width, msi.height, i.archive_filename FROM image_observations AS io INNER JOIN mosaic_split_images AS msi ON msi.image_id = io.image_id INNER JOIN images AS i ON i.id = io.image_id WHERE io.nothing_here=1");

while ($row = $result->fetch_assoc()) {
    $image_id = $row['image_id'];
    if (isset($msilist[$image_id]))
        continue;

    // make sure no respondants that believe something is here 
    $temp_result = query_wildlife_video_db("SELECT * FROM image_observations WHERE image_id=$image_id AND nothing_here=0");
    if ($temp_result->num_rows == 0) {
        if (add_image($data, $image_id, $row['archive_filename'], $row['width'], $row['height'], $row['id'])) {
            if (rand(1, 100) <= $percent) {
                $msilist[$image_id] = $row['id'];
            }
        }
    }
}

echo "Total number of images: " . count($data) . "\n";
echo "Total training images: " . count($msilist) . "\n";
echo "Overall ratio: " . count($msilist) / count($data) . "\n";

// generate the background data
foreach ($data as $image_id => &$mosaic) {
    if (!isset($msilist[$image_id]))
        continue;

    $bg_locations = array(); 
    $bg_count = ceil($bg_multiplier * count($mosaic['boxes']));
    if ($bg_count <= 0) {
        $bg_count = 10;
    }

    // go until we get the background amount we need
    $width = $mosaic['image_width'];
    $height = $mosaic['image_height'];
    $max_x = $width - $size - 1;
    $max_y = $height - $size - 1;

    while (count($bg_locations) < $bg_count) {
        // get a random spot in the image
        $x = rand(0, $max_x);
        $y = rand(0, $max_y);

        // if we have an overlap, we continue
        if (has_overlap($x, $y, $size, $mosaic['boxes']) || has_overlap($x, $y, $size, $bg_locations)) {
            continue;
        }

        // no overlap!
        $bg_locations[] = array(
            'x' => $x,
            'y' => $y,
            'species' => -1
        );
    }

    // add the background locations into our data
    foreach ($bg_locations as &$box) {
        $mosaic['boxes'][] = $box;
    }
}

echo "Generating IDX\n";

// open the images and update the idx
foreach ($data as $image_id => &$mosaic) {
    if (!isset($msilist[$image_id])) {
        continue;
    }

    try {
        $imagick = new Imagick($mosaic['filename']);
        $imagick->setFirstIterator();
    } catch (Exception $e) {
        die($e->getMessage());
    }

    foreach ($mosaic['boxes'] as &$box) {
        $store = array();

        $areaIterator = $imagick->getPixelRegionIterator($box['x'], $box['y'], $size, $size);
        foreach ($areaIterator as $rowIterator) {
            foreach ($rowIterator as $pixel) {
                $color = $pixel->getColor();
                $store[] = $color['r'];
                $store[] = $color['g'];
                $store[] = $color['b'];
            }
        }
        $areaIterator->clear();

        // save to idx
        $idx[] = $store;
        $species_idx[] = array($box['species']);
    }

    $imagick->clear();
}

echo "Saving the IDX files...\n";

// save the idx files
$idx->saveToFile("$outbase.idx");
$species_idx->saveToFile("${outbase}_species.idx");

echo "Saving the JSON file...\n";
$fp = fopen("$outbase.json", 'w');
fwrite($fp, json_encode($msilist));
fclose($fp);

exit(0);

?>
