<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

$longops = array(
    'bg_ratio:',
    'size:',
    'test:',
    'outdir:'
);

$opt = getopt("", $longops);

$bg_ratio   = isset($opt['bg_ratio']) ? (int)$opt['bg_ratio'] : 50;
$size       = isset($opt['size']) ? (int)$opt['size'] : 18;
$testidx    = isset($opt['test']) ? $opt['test'] : '';
$outdir     = isset($opt['outdir']) ? $opt['outdir'] : '/tmp';

// check the bg ratio
if ($bg_ratio <= 0 || $bg_ratio >= 100) {
    die('Sane bg ratio required. (1 - 99).');
}

// check the size
if ($size < 10 || $size > 100) {
    die('Sane size required. (10 - 100).');
}

$skip = array();
if ($testidx) {
    $skip = json_decode(file_get_contents($testidx), true);
}

// configure the filename
$filename = "bgonly";
$filename .= '_' . $bg_ratio . 'percent';

// see if we already have the file and touch it and return
if (file_exists("$outdir/$filename.idx")) {
    echo "File exists: $filename";

    try {
        touch("/$outdir/$filename.idx");
    } catch (Exception $e) {
        // eat it
    }

    exit(0);
}

// GRAB THE DATA OF ALL IMAGES WITH OBS
//////////

// species
$species_allowable = array(2, 1000000);
$species_where = "species_id IN (" . join(",", $species_allowable) . ")";

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/tools/idx.php");

try {
    // size x size x 3 (rgb square)
    $idx = new IDX(0x08, array($size, $size, 3));
} catch (Exception $e) {
    die($e->getMessage());
}

$bg_multiplier = ((float)$bg_ratio) / ((float)(100 - $bg_ratio));

$data = array();
$halfsize = $size / 2.0;

function add_image(array &$data, $image_id, $image_filename, $image_width, $image_height)
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
            'nothing' => false,
            'boxes' => array(),
            'bg_boxes' => array()
        );
    }
}

$total_observations = 0;

echo "Finding observations\n\n";

$mosaic_result = query_wildlife_video_db("SELECT id FROM mosaic_images WHERE id <= 56");
while ($mosaic_row = $mosaic_result->fetch_assoc()) {
    $mosaic_id = $mosaic_row['id'];

    $split_result = query_wildlife_video_db("SELECT msi.id, msi.image_id, msi.width, msi.height, i.archive_filename FROM mosaic_split_images AS msi INNER JOIN images AS i ON i.id = msi.image_id WHERE mosaic_image_id=$mosaic_id");
    while ($split_row = $split_result->fetch_assoc()) {
        $image_id = $split_row['image_id'];
        $msi_id = $split_row['id'];
        $image_width = $split_row['width'];
        $image_height = $split_row['height'];

        // skip
        if (isset($skip[$image_id])) {
            echo "Skipping $image_id\n";
            continue;
        }

        add_image($data, $image_id, $split_row['archive_filename'], $split_row['width'], $split_row['height']);

        // get all the boxes for that image
        $io_result = query_wildlife_video_db("SELECT id, nothing_here FROM image_observations AS io WHERE io.image_id=$image_id");
        while ($io_row = $io_result->fetch_assoc()) {
            if ($io_row['nothing_here']) {
                $data[$image_id]['nothing'] = true;
            } else {
                // add in all the boxes for this observation
                $io_id = $io_row['id'];
                $iob_result = query_wildlife_video_db("SELECT * FROM image_observation_boxes AS iob WHERE image_observation_id=$io_id AND $species_where");

                if ($iob_result->num_rows <= 0)
                    continue;

                // normalize the x / y / w / h
                $iob_row = $iob_result->fetch_assoc();
                $x = $iob_row['x'] + intval($iob_row['width'] / 2.0 - $halfsize);
                $y = $iob_row['y'] + intval($iob_row['height'] / 2.0 - $halfsize);

                // make sure x and y are within range
                if ($x < 0) $x = 0;
                if (($x + $size) >= $image_width) $x = $image_width - $size - 1;
                if ($y < 0) $y = 0;
                if (($y + $size) >= $image_height) $y = $image_height - $size - 1;

                // add in the box
                $data[$image_id]['boxes'][] = array(
                    'x' => $x,
                    'y' => $y,
                    'species' => $iob_row['species_id']
                );

                // increase our count
                $total_observations++;
            }
        }
    }
}

$total_images = count($data);
$total_background = (int)($total_observations * $bg_multiplier);
$bg_per_image = (int)ceil($total_background / $total_images);

if ($bg_per_image < 10) {
    $bg_per_image = 10;
}

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

echo "Generating IDX\n";
echo "Total images: $total_images\n";
echo "Total observations: $total_observations\n";
echo "Background per image: $bg_per_image\n";

// generate the background data
foreach ($data as &$mosaic) {
    $bg_locations = array(); 

    // go until we get the background amount we need
    $width = $mosaic['image_width'];
    $height = $mosaic['image_height'];
    $max_x = $width - $size - 1;
    $max_y = $height - $size - 1;

    while (count($bg_locations) < $bg_per_image) {
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

// open the images and update the idx
foreach ($data as &$mosaic) {
    try {
        $imagick = new Imagick($mosaic['filename']);
        $imagick->setFirstIterator();
    } catch (Exception $e) {
        die($e->getMessage());
    }

    foreach ($mosaic['boxes'] as &$box) {
        // only concerned about background
        if ($box['species'] != -1) {
            continue;
        }

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
        //$species_idx[] = array($box['species']);
    }

    $imagick->clear();
}

// save the idx files
$idx->saveToFile("$outdir/$filename.idx");
//$species_idx->saveToFile("$outdir/${filename}_species.idx");

exit(0);

?>
