<?php

/* download a csv for the msi true counts */

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

$longops = array(
    'expert',
    'matched',
    'unmatched',
    'start_date:',
    'end_date:',
    'bg_ratio:',
    'size:',
    'id:',
    'test:'
);

$opt = getopt("", $longops);

$expert     = isset($opt['expert']);
$matched    = isset($opt['matched']);
$unmatched  = isset($opt['unmatched']) && !$matched;
$citizen    = $matched || $unmatched;
$start_date = isset($opt['start_date']) ? (int)$opt['start_date'] : 0;
$end_date   = isset($opt['end_date']) ? (int)$opt['end_date'] : time();
$bg_ratio   = isset($opt['bg_ratio']) ? (int)$opt['bg_ratio'] : 80;
$size       = isset($opt['size']) ? (int)$opt['size'] : 18;
$id         = isset($opt['id']) ? (int)$opt['id'] : 0;
$testidx    = isset($opt['test']) ? $opt['test'] : '';

if ($matched) {
    //die('Matched not currently implemented.');
}

if (!($expert || $citizen)) {
    die('No data chosen. Chose a combination of expert and citizen.');
}

// check the bg ratio
if ($bg_ratio < 0 || $bg_ratio >= 100) {
    die('Sane bg ratio required. (0 - 99).');
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
$filename = "observations";
if ($expert)
    $filename .= '_expert';
if ($unmatched)
    $filename .= '_unmatched';
if ($matched)
    $filename .= '_matched';
if ($start_date)
    $filename .= '_start' . date('Ymd', $start_date);
$filename .= '_end' . date('Ymd', $end_date) . '_' . $bg_ratio . 'percent';

if ($id > 0) {
    $filename = "$id" . "_$filename";
}

// see if we already have the file and touch it and return
if (file_exists("/tmp/$filename.idx") && file_exists("/tmp/${filename}_species.idx")) {
    echo "File exists: $filename";

    try {
        touch("/tmp/$filename.idx");
        touch("/tmp/${filename}_species.idx");
    } catch (Exception $e) {
        // eat it
    }

    exit(0);
}

// GRAB THE DATA AND PREPARE THE IDX
//////////


// setup the where clauses
$where = array();

if ($expert && !$unmatched) {
    $where[] = "is_expert = 1";
} else if ($unmatched && !$expert) {
    $where[] = "is_expert = 0";
}

$where[] = "submit_time BETWEEN $start_date AND $end_date";

// build the query
$query = "SELECT * FROM view_mosaic_observations ";

// species
$species_allowable = array(2, 1000000);
$where[] = "species_id IN (" . join(",", $species_allowable) . ")";

if (count($where)) {
    $query .= "WHERE " . implode(" AND ", $where) . " ";
}

$query .= "ORDER BY image_id ASC";

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/tools/idx.php");

try {
    // size x size x 3 (rgb square)
    $idx = new IDX(0x08, array($size, $size, 3));
    $species_idx = new IDX(0x0C, array());
} catch (Exception $e) {
    die($e->getMessage());
}

$data = array();
$bg_multiplier = ((float)$bg_ratio) / ((float)(100 - $bg_ratio));
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
            'boxes' => array()
        );
    }
}

// add the result in
if ($expert || $unmatched) {
    // expert or unmatched
    echo "Expert or umatched: $query\n";
    $result = query_wildlife_video_db($query);
    if (!$result) {
        die("No results returned.");
    }

    echo $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        $image_id = $row['image_id'];

        // skip any in the list
        if (isset($skip[$image_id])) {
            echo "Skipping $image_id\n";
            continue;
        }

        add_image($data, $image_id, $row['image_filename'], $row['image_width'], $row['image_height']);

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
}

// do the results for matching
$start_date = date("Y-m-d H:i:s", $start_date);
$end_date = date("Y-m-d H:i:s", $end_date);
if ($matched) {
    echo "Matched data between $start_date and $end_date\n";

    $query = "SELECT * FROM view_matched_observations_2 WHERE submit_time_1 BETWEEN '$start_date' AND '$end_date' AND submit_time_2 BETWEEN '$start_date' AND '$end_date' AND species_id IN (" . join(",", $species_allowable) . ")";

    echo "$query\n";

    $result = query_wildlife_video_db($query);
    if (!$result) {
        die("DB error");
    }

    echo $result->num_rows . "\n";
    while ($row = $result->fetch_assoc()) {
        $same = $row['same_image'];

        // skip
        if (isset($skip[$row['image_id_1']]) || isset($skip[$row['image_id_2']])) {
            echo "Skipping image " . $row['image_id_1'] . " or " . $row['image_id_2'] . "\n";
            continue;
        }

        add_image($data, $row['image_id_1'], $row['image_filename_1'], $row['image_width_1'], $row['image_height_1']);

        if (!$same) {
            add_image($data, $row['image_id_2'], $row['image_filename_2'], $row['image_width_2'], $row['image_height_2']);
        }


        // normalize the x / y / w / h
        $width = intval($row['width']);
        $height = intval($row['height']);

        $x = $row['x1'] + ($width / 2.0 - $halfsize);
        $y = $row['y1'] + ($height / 2.0 - $halfsize);

        // make sure x and y are within range
        if ($x < 0) $x = 0;
        if (($x + $size) >= $row['image_width_1']) $x = $row['image_width_1'] - $size - 1;
        if ($y < 0) $y = 0;
        if (($y + $size) >= $row['image_height_1']) $y = $row['image_height_1'] - $size - 1;

        $data[$row['image_id_1']]['boxes'][] = array(
            'x' => $x,
            'y' => $y,
            'species' => $row['species_id']
        );

        if (!$same) {
            $x = $row['x2'] + ($width / 2.0 - $halfsize);
            $y = $row['y2'] + ($height / 2.0 - $halfsize);

            // make sure x and y are within range
            if ($x < 0) $x = 0;
            if (($x + $size) >= $row['image_width_2']) $x = $row['image_width_2'] - $size - 1;
            if ($y < 0) $y = 0;
            if (($y + $size) >= $row['image_height_2']) $y = $row['image_height_2'] - $size - 1;

            $data[$row['image_id_2']]['boxes'][] = array(
                'x' => $x,
                'y' => $y,
                'species' => $row['species_id']
            );
        }
    }
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

// generate the background data
foreach ($data as &$mosaic) {
    $bg_locations = array(); 
    $bg_count = ceil($bg_multiplier * count($mosaic['boxes']));

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

// open the images and update the idx
foreach ($data as &$mosaic) {
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

// save the idx files
$idx->saveToFile("/tmp/$filename.idx");
$species_idx->saveToFile("/tmp/${filename}_species.idx");

exit(0);

?>
