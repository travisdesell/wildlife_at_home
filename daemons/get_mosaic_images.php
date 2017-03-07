#!/usr/bin/env php

<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');

/** Prints out the usage for the program and exits. */
function usage($what = null) {
    if ($what)
        echo "\n$what\n";

    echo "\nUsage: php get_mosaic_images.php -o outdir [--with INT] [--without INT] [--project INT] [--year INT]\n\n";
    echo "\t-o           - output directory\n";
    echo "\t--project    - project id to use (0 for all) [DEFAULT: 5]\n";
    echo "\t--with       - num with observations [DEFAULT: 5]\n";
    echo "\t--without    - num without observations [DEFAULT: 1]\n";
    echo "\t--year / -y  - year for the observations [DEFAULT: 2015]\n";
    echo "\t--shift / -s - shift the output pixes [DEFAULT: true for 2015]\n";

    exit();
}

$shortops = "o:y:s";
$longops = array(
    "project:",
    "with:",
    "without:",
    "year:",
    "shift"
);

$options = getopt($shortops, $longops);
if (!$options || !isset($options['o'])) {
    usage();
}

$year = 2015;
if (isset($options['y'])) {
    $year = intval($options['y']);
} else if (isset($options['year'])) {
    $year = intval($options['year']);
}

$shifted = $year == 2015;
if (isset($options['s']) || isset($options['shift'])) {
    $shifted = true;
}

$numwithobs = 5;
if (isset($options['with'])) {
    $numwithobs = intval($options['with']);
}

$numwithoutobs = 1;
if (isset($options['without'])) {
    $numwithoutobs = intval($options['without']);
}

$project_ids = array("5");
if (isset($options['project'])) {
    $project = intval($options['project']);
    if ($project == 0) {
        $project_ids = array("4", "5");
    } else {
        $project_ids = array($project);
    }
}

$numtotal = $numwithobs + $numwithoutobs;

$outdir = $options['o'];

echo "\nCreating testing image set.\n";
if ($shifted) {
    echo "\tShifting output\n";
}

$rshift = 233.0 / 150.0;
$gshift = 255.0 / 189.0;
$bshift = 236.0 / 190.0;

$mosaic_results = query_wildlife_video_db("SELECT * FROM mosaic_images WHERE year = $year AND project_id IN (".implode(",", $project_ids).")");
while (($mosaic_row = $mosaic_results->fetch_assoc())) {
    // get 8 images with data and 2 without for this mosaic
    $mosaic_id = $mosaic_row['id'];

    // first get a list of all the observations
    echo "\nMosaic ID: $mosaic_id\n";
    echo "\tFetching $numwithobs unique observations...\n";
    $results = query_wildlife_video_db("SELECT msi.id AS msi_id, i.archive_filename AS filename, io.id AS io_id, io.user_id AS user_id FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON msi.image_id = io.image_id INNER JOIN images as i ON i.id = msi.image_id INNER JOIN image_observation_experts AS ioe ON ioe.user_id = io.user_id WHERE msi.mosaic_image_id = $mosaic_id AND io.nothing_here = 0 GROUP BY msi.id ORDER BY rand() LIMIT $numwithobs");
    if (!$results) {
        echo "\t\tNo results. Skipping.\n";
        continue;
    }

    $obs_data = $results->fetch_all(MYSQLI_ASSOC);
    $numobs = count($obs_data);
    if ($numobs != $numwithobs) {
        echo "\t\t$numobs when at least $numwithobs required. Skipping.\n";
        continue;
    }
    echo "\t\t$numobs unique observations found.\n";
    echo "\t\tDone.\n";

    echo "\tSelecting $numwithoutobs images without observations...\n";
    $results = query_wildlife_video_db("SELECT msi.id AS msi_id, i.archive_filename AS filename, io.id AS io_id, io.user_id AS user_id FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON msi.image_id = io.image_id INNER JOIN images as i ON i.id = msi.image_id INNER JOIN image_observation_experts AS ioe ON ioe.user_id = io.user_id WHERE msi.mosaic_image_id = $mosaic_id AND io.nothing_here = 1 GROUP BY msi.id ORDER BY rand() LIMIT $numwithoutobs");
    if (!$results) {
        echo "\t\tNo results. Skipping.\n";
        continue;
    }

    $nonobs_data = $results->fetch_all(MYSQLI_ASSOC);
    $numnonobs = count($nonobs_data);
    if ($numnonobs != $numwithoutobs) {
        echo "\t\t$numnonobs when at least $numwithoutobs required. Skipping.\n";
        continue;
    }
    echo "\t\tDone.\n";

    $data = array(
        'obs' => $obs_data,
        'nonobs' => $nonobs_data
    );

    echo "\tCopying files into '$outdir'...\n";
    foreach ($data as $type => &$arr) {
        foreach ($arr as &$obs) {
            // get the count in this image
            $results = query_wildlife_video_db("SELECT COUNT(*) AS count FROM image_observation_boxes WHERE image_observation_id = ${obs['io_id']}");
            $count = 0;
            if ($results && ($row = $results->fetch_assoc())) {
                $count = $row['count'];
            }

            $filename = "$outdir/${type}_mosaic${mosaic_id}_msi${obs['msi_id']}_user${obs['user_id']}_count${count}";
            if ($shifted) {
                $filename .= "_shifted";
            }
            $filename .= ".png";

            echo "\t\t${obs['filename']} to\n\t\t\t$filename\n";
            if ($shifted) {
                $im = new Imagick(realpath($obs['filename']));
                if (!$im) {
                    echo "\t\tError loading file...\n";
                    continue;
                }

                $areaIterator = $im->getPixelRegionIterator(0, 0, $im->getImageWidth(), $im->getImageHeight());
                foreach ($areaIterator as $rowIterator) {
                    foreach ($rowIterator as $pixel) {
                        $color = $pixel->getColor();
                        $r = $color['r'] * $rshift;
                        $g = $color['g'] * $gshift;
                        $b = $color['b'] * $bshift;

                        if ($r > 255) $r = 255;
                        if ($g > 255) $g = 255;
                        if ($b > 255) $b = 255;
                        
                        $pixel->setColor("rgba($r, $g, $b, 0)");
                    }

                    $areaIterator->syncIterator();
                }

                if (!$im->writeImage($filename)) {
                    echo "\t\t\tERROR writing shifted file!\n";
                }

                $areaIterator->clear();
                $im->clear();
            }
            else if (!copy($obs['filename'], $filename)) {
                echo "\t\t\tERROR copying!\n";
            }
            echo "\t\t\tSuccess!\n";
        }
    }

    echo "\t\tDone.\n";
}

echo "\n\tDone.\n";

?>
