<?php

require_once("../video_archive_daemons/wildlife_db.php");

$video_table = "video_2";
$segment_table = "video_segment_2";
$observation_table = "observation_2";
$species_table = "species";
$location_table = "locations";

/**
 * SCRIPT STARTS HERE
 */
//
//Connect to the database.
mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);

$iteration = 1;

function t_test($species_name, $n1, $avg1, $stddev1, $n2, $avg2, $stddev2) {

    $denom = (($n1 - 1) * $stddev1 * $stddev1) + (($n2 - 1) * $stddev2 * $stddev2);
    $denom /= $n1 + $n2 - 2;
    $denom = sqrt($denom);

    $t = ($avg1 - $avg2) / $denom * sqrt( (1 / $n1) + (1 / $n2) );

    echo "strict presence vs strict absence\n";
    echo "T test result for species $species_name = $t\n";
    echo "degress of freedom: " . ($n1 + $n2 - 2) . "\n";

}

function print_data($query, $file, &$avg, &$stddev, &$n) {
    echo "\n$query\n";
    $vs2_result = mysql_query($query);
    if (!$vs2_result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

    $missing_count = 0;

    $probabilities = array();

    $fp = fopen($file, 'w');
    while ($row = mysql_fetch_assoc($vs2_result)) {
        $probability = $row['probability'];
        $probabilities[] = $probability;

        fwrite($fp, $row['probability'] . "\n");
    }
    fclose($fp);

    $avg = 0;
    for ($i = 0; $i < count($probabilities); $i++) {
//        echo "$probabilities[$i]\n";
        $avg += $probabilities[$i];
    }
    $avg /= count($probabilities);

    $stddev = 0;
    for ($i = 0; $i < count($probabilities); $i++) {
        $stddev += ($avg - $probabilities[$i]) * ($avg - $probabilities[$i]);
    }
    $stddev /= count($probabilities);
    $stddev = sqrt($stddev);

    $n = count($probabilities);

    echo "n: $n, avg: " . $avg . ", stddev: " . $stddev . ", med: " . $probabilities[$n / 2] . " \n";
}

/* get a segment which needs to be generated from the watermarked file */

#$query = "SELECT probability FROM classifications WHERE species_id = $species_id ORDER BY probability";

function get_species_name($species_id) {
    $species_name = "";
    if ($species_id == 1) $species_name = "sharptailed_grouse";
    if ($species_id == 2) $species_name = "least_tern";
    if ($species_id == 3) $species_name = "piping_plover";
    return $species_name;
}

function get_data($type, $species_id) {
    $species_name = get_species_name($species_id);

    $query = "select probability FROM observations, classifications WHERE classifications.video_segment_id = observations.video_segment_id AND classifications.species_id = $species_id AND observations.$type > 0 ORDER BY probability";
    print_data($query, $species_name . "__" . $type . "_1.dat");

    $query = "select probability FROM observations, classifications WHERE classifications.video_segment_id = observations.video_segment_id AND classifications.species_id = $species_id AND observations.$type < 0 ORDER BY probability";
    print_data($query, $species_name . "__" . $type . "_0.dat");
}

function get_active_data($species_id) {
    $species_name = get_species_name($species_id);

    /*
    $query = "select probability FROM observations, classifications WHERE classifications.video_segment_id = observations.video_segment_id AND classifications.species_id = $species_id" .
        " AND observations.status = 'CANONICAL' " .
        " AND tag = 'motion_1' " .
        " AND " .
        " (observations.bird_presence > 0" .
        "  OR observations.bird_return > 0" .
        "  OR observations.bird_leave > 0" .
        "  OR observations.predator_presence > 0" .
        "  OR observations.nest_defense > 0" .
        "  OR observations.nest_success > 0" .
        "  OR observations.chick_presence > 0" .
        "  OR observations.interesting > 0)" .
        " ORDER BY probability";
     */
    $query = "SELECT probability FROM classifications as c WHERE c.species_id = $species_id AND c.tag = 'motion_1' AND EXISTS (SELECT * FROM observations as o WHERE o.video_segment_id = c.video_segment_id AND o.status = 'CANONICAL' AND (o.bird_return > 0 OR o.bird_leave > 0 OR o.predator_presence > 0 OR o.nest_defense > 0 OR o.nest_success > 0 OR o.chick_presence > 0 OR o.interesting > 0)) ORDER BY probability";
//    $query = "SELECT probability FROM classifications as c WHERE c.species_id = $species_id AND c.tag = 'motion_1' AND EXISTS (SELECT * FROM observations as o WHERE o.video_segment_id = c.video_segment_id AND o.status = 'CANONICAL' AND (o.bird_presence > 0 OR o.bird_return > 0 OR o.bird_leave > 0 OR o.predator_presence > 0 OR o.nest_defense > 0 OR o.nest_success > 0 OR o.chick_presence > 0 OR o.interesting > 0)) ORDER BY probability";
    print_data($query, $species_name . "__active_1.dat", $avg1, $stddev1, $n1);

    /*
    $query = "select probability FROM observations, classifications WHERE classifications.video_segment_id = observations.video_segment_id AND classifications.species_id = $species_id" .
        " AND observations.status = 'CANONICAL' " .
        " AND tag = 'motion_1' " .
        " AND " .
        " (observations.bird_absence > 0" .
        "  AND observations.bird_presence < 0" .
        "  AND observations.bird_return < 0" .
        "  AND observations.bird_leave < 0" .
        "  AND observations.predator_presence < 0" .
        "  AND observations.nest_defense < 0" .
        "  AND observations.nest_success < 0" .
        "  AND observations.chick_presence < 0" .
        "  AND observations.interesting < 0)" .
        " ORDER BY probability";
     */
    $query = "SELECT probability FROM classifications as c WHERE c.species_id = $species_id AND c.tag = 'motion_1' AND EXISTS (SELECT * FROM observations as o WHERE o.video_segment_id = c.video_segment_id AND o.status = 'CANONICAL' AND (o.bird_presence < 0 AND o.bird_return < 0 AND o.bird_leave < 0 AND o.predator_presence < 0 AND o.nest_defense < 0 AND o.nest_success < 0 AND o.chick_presence < 0 AND o.interesting < 0)) ORDER BY probability";
    print_data($query, $species_name . "__active_0.dat", $avg2, $stddev2, $n2);

    t_test($species_name, $n1, $avg1, $stddev1, $n2, $avg2, $stddev2);
}

function get_surf_data($species_id, $tag) {
    $species_name = get_species_name($species_id);

    $query = "select probability FROM observations as o, classifications as c WHERE c.video_segment_id = o.video_segment_id AND c.species_id = $species_id" .
        " AND o.status = 'CANONICAL' " .
        " AND c.tag = '$tag' " .
        " AND " .
        " (o.bird_presence > 0" .
        "  AND o.bird_absence < 0)" .
//        "  AND hour(classifications.start_time) between 7 and 19 " .
        " ORDER BY probability";
    print_data($query, $species_name . "__$tag" . "_1.dat", $avg1, $stddev1, $n1);

    $query = "select probability FROM observations as o, classifications as c WHERE c.video_segment_id = o.video_segment_id AND c.species_id = $species_id" .
        " AND o.status = 'CANONICAL' " .
        " AND c.tag = '$tag' " .
        " AND " .
        " (o.bird_absence > 0" .
        "  AND o.bird_presence < 0)" .
//        "  AND hour(classifications.start_time) between 7 and 19 " .
        " ORDER BY probability";
    print_data($query, $species_name . "__$tag" . "_0.dat", $avg2, $stddev2, $n2);

    t_test($species_name, $n1, $avg1, $stddev1, $n2, $avg2, $stddev2);

    /*
    $query = "select probability FROM observations as o, classifications as c WHERE c.video_segment_id = o.video_segment_id AND c.species_id = $species_id" .
        " AND o.status = 'CANONICAL' " .
        " AND c.tag = '$tag' " .
        " AND " .
        " (o.bird_absence > 0)" .
//        "  AND o.bird_presence > 0)" .
//        "  AND hour(classifications.start_time) between 7 and 19 " .
        " ORDER BY probability";
    print_data($query, $species_name . "__$tag" . "_0.5.dat", $avg2, $stddev2, $n2);


    $denom = (($n1 - 1) * $stddev1 * $stddev1) + (($n2 - 1) * $stddev3 * $stddev3);
    $denom /= $n1 + $n2 - 2;
    $denom = sqrt($denom);

    $t = ($avg1 - $avg3) / $denom * sqrt( (1 / $n1) + (1 / $n2) );

    echo "any absence vs any presence\n";
    echo "T test result for species $species_id = $t\n";
    echo "degress of freedom: " . ($n1 + $n2 - 2) . "\n";
    */
}


$species_id = 1;
get_active_data($species_id);

$species_id = 2;

/*
get_data("bird_return", $species_id);
get_data("bird_leave", $species_id);
get_data("bird_presence", $species_id);
get_data("interesting", $species_id);
 */
#get_active_data($species_id);
get_surf_data($species_id, "tern_1");

$species_id = 3;

/*
get_data("bird_return", $species_id);
get_data("bird_leave", $species_id);
get_data("bird_presence", $species_id);
get_data("interesting", $species_id);
 */
#get_active_data($species_id);
get_surf_data($species_id, "plover_good_1");
get_surf_data($species_id, "plover_all_1");

?>
