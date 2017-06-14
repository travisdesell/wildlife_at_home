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
);

$opt = getopt("", $longops);

$expert     = isset($opt['expert']);
$matched    = isset($opt['matched']);
$unmatched  = isset($opt['unmatched']) && !$matched;
$citizen    = $matched || $unmatched;
$start_date = isset($opt['start_date']) ? (int)$opt['start_date'] : 0;
$end_date   = isset($opt['end_date']) ? (int)$opt['end_date'] : time();

if ($matched) {
    //die('Matched not currently implemented.');
}

if (!($expert || $citizen)) {
    die('No data chosen. Chose a combination of expert and citizen.');
}

$filename = "msi_locations";
if ($expert)
    $filename .= '_expert';
if ($unmatched)
    $filename .= '_unmatched';
if ($matched)
    $filename .= '_matched';
if ($start_date)
    $filename .= '_start' . date('Ymd', $start_date);

$filename .= '_end' . date('Ymd', $end_date) . '.csv';

// see if we already have the file and touch it and return
if (file_exists("/tmp/$filename")) {
    echo "File exists: $filename";

    try {
        touch("/tmp/$filename");
    } catch (Exception $e) {
        // eat it
    }

    exit(0);
}

// open the file for writing
try {
    $fp = fopen("/tmp/$filename", "w");
} catch (Exception $e) {
    echo "Failed to open file: $filename";
    exit(1);
}

$data = array();

// expert / unmatched needs to be its own thing
if ($expert || $unmatched) {
    $where[] = "io.nothing_here = 0 AND UNIX_TIMESTAMP(submit_time) BETWEEN $start_date AND $end_date";

    if ($expert && !$unmatched) {
        $where[] = "is_expert = 1";
    } else if (!$expert && $unmatched) {
        $where[] = "is_expert = 0";
    }

    // grab all the image observations
    $result = query_wildlife_video_db("SELECT DISTINCT msi.id AS msi_id, io.id AS io_id, io.is_expert FROM image_observations AS io INNER JOIN mosaic_split_images AS msi ON io.image_id = msi.image_id WHERE ".implode(" AND ", $where));

    // go through all the responses
    while ($row = $result->fetch_assoc()) {
        $msi_id = $row['msi_id'];
        $io_id  = $row['io_id'];
        $key = intval($row['is_expert']) == 1 ? 'expert' : 'unmatched';

        $white_result =  query_wildlife_video_db("SELECT COUNT(*) AS whites FROM image_observations AS io INNER JOIN image_observation_boxes AS iob ON iob.image_observation_id = io.id WHERE io.id=$io_id AND iob.species_id=2");

        $blue_result  =  query_wildlife_video_db("SELECT COUNT(*) AS blues FROM image_observations AS io INNER JOIN image_observation_boxes AS iob ON iob.image_observation_id = io.id WHERE io.id=$io_id AND iob.species_id=1000000");

        $whites = ($white_result->fetch_assoc())['whites'];
        $blues  = ($blue_result->fetch_assoc())['blues'];

        if (!isset($data[$msi_id])) {
            $data[$msi_id] = array(
                'expert' => array(),
                'unmatched' => array(),
                'matched' => array()
            );
        }

        // add the data in to the correct location
        $data[$msi_id][$key][] = array(
            'whites' => $whites,
            'blues' => $blues
        );       
    }
}

// doing the matching is gonna be a major pain, think about it
if ($matched) {

}

// setup the csv file
$line = array('msi');
if ($expert) {
    array_push(
        $line,
        'expert_count',
        'expert_white_avg',
        'expert_white_dev',
        'expert_blue_avg',
        'expert_blue_dev'
    );
}

if ($unmatched) {
    array_push(
        $line,
        'citizen_count',
        'citizen_white_avg',
        'citizen_white_dev',
        'citizen_blue_avg',
        'citizen_blue_dev'
    );
}

fputcsv($fp, $line);

if (!function_exists('stats_standard_deviation')) {
    /**
    *      * This user-land implementation follows the implementation quite strictly;
    *           * it does not attempt to improve the code or algorithm in any way. It will
    *                * raise a warning if you have fewer than 2 values in your array, just like
    *                     * the extension does (although as an E_USER_WARNING, not E_WARNING).
    *                          * 
    *                               * @param array $a 
    *                                    * @param bool $sample [optional] Defaults to false
    *                                         * @return float|bool The standard deviation or false on error.
    *                                              */
    function stats_standard_deviation(array $a, $sample = false) {
        $n = count($a);
        if ($n === 0) {
            trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
            --$n;
        }
        return sqrt($carry / $n);
    }
}

function append_calculated_data(array &$ret, array &$data) {
    $count = count($data);

    if ($count == 0) {
        return array_push($ret, 0, 0, 0, 0, 0);
    } else if ($count == 1) {
        return array_push($ret,
            1,
            $data[0]['whites'],
            0,
            $data[0]['blues'],
            0
        );
    }

    $whites = array();
    $blues = array();

    // get a list for the whites and blues from each respondant
    foreach ($data as &$val) {
        $whites[] = $val['whites'];
        $blues[] = $val['blues'];
    }

    return array_push(
        $ret,
        $count,
        round(((float)array_sum($whites)) / count($whites), 2),
        round(stats_standard_deviation($whites), 2),
        round(((float)array_sum($blues)) / count($blues), 2),
        round(stats_standard_deviation($blues), 2)
    );
}

ksort($data);

// go through the data and write each line
foreach ($data as $msi_num => &$msi) {
    $line = array($msi_num);
    if ($expert) {
        append_calculated_data($line, $msi['expert']);
    }
    if ($unmatched) {
        append_calculated_data($line, $msi['unmatched']);
    }

    // write the line out
    fputcsv($fp, $line);
}

fclose($fp);
exit(0);

?>
