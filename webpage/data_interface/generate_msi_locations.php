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
    'outdir:'
);

$opt = getopt("", $longops);

$expert     = isset($opt['expert']);
$matched    = isset($opt['matched']);
$unmatched  = isset($opt['unmatched']) && !$matched;
$citizen    = $matched || $unmatched;
$outdir     = isset($opt['outdir']) ? $opt['outdir'] : '/tmp';

// default values
if (!$expert && !$citizen) {
    $expert = true;
    $citizen = true;
    $unmatched = true;
}

$filename = "$outdir/msi_locations";
if ($expert)
    $filename .= "_expert";
if ($matched)
    $filename .= "_matched";
if ($unmatched)
    $filename .= "_unmatched";
$filename .= ".bin";

// see if we already have the file and touch it and return
if (file_exists($filename)) {
    echo "File exists: $filename";

    try {
        touch($filename);
    } catch (Exception $e) {
        // eat it
    }

    exit(0);
}

echo "Opening: $filename\n";

// open the stream for writing
$outstream = fopen($filename, 'wb');

$msis = array();


// matched is a little different
if ($matched) {
    echo "Generate data for matched\n";
    die("Matched currently unsupported.");
}

// expert and unmatched are the same logic
if ($expert || $unmatched) {
    $where = "";
    if ($expert && !$unmatched) {
        echo "Generating data for expert\n";
        $where = "WHERE is_expert=1";
    } elseif (!$expert && $unmatched) {
        echo "Generating data for unmatched\n";
        $where = "WHERE is_expert=0";
    } else {
        echo "Generating data for expert and unmatched\n";
    }

    $result = query_wildlife_video_db("SELECT DISTINCT msi.id AS msi_id, io.id AS io_id FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON io.image_id = msi.image_id $where ORDER BY msi_id");

    while ($row = $result->fetch_assoc()) {
        $msi_id = $row['msi_id'];
        $io_id  = $row['io_id'];

        $msi_result = query_wildlife_video_db("SELECT * FROM image_observation_boxes WHERE image_observation_id = $io_id");
        if (!$msi_result || $msi_result->num_rows <= 0) 
            continue;
        //fwrite($outstream, pack("V", $msi_count));
        
        if ($msi_result->num_rows > 0 && !isset($msis[$msi_id])) {
            $msis[$msi_id] = array();
        }

        while ($msi_row = $msi_result->fetch_assoc()) {
            $msis[$msi_id][] = array(
                'species_id' => $msi_row['species_id'],
                'x' => $msi_row['x'],
                'y' => $msi_row['y'],
                'width' => $msi_row['width'],
                'height' => $msi_row['height']
            );
        }
    }
}

echo "Saving data\n";

// write out the number of MSIs
fwrite($outstream, pack("V", count($msis)));

// write out the individual MSIs
foreach ($msis as $msi_id => &$msi) {
    // write out the msi id
    fwrite($outstream, pack("V", $msi_id));

    // write out the number of observations for this MSI
    fwrite($outstream, pack("V", count($msi)));

    // write out each observations
    foreach ($msi as &$obs) {
        fwrite($outstream, pack("VVVVV", $obs['species_id'], $obs['x'], $obs['y'], $obs['width'], $obs['height']));
    }
}

// close the file
fclose($outstream);

exit();

?>
