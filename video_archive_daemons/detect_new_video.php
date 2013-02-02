<?php

require_once("wildlife_db.php");

/**
 *  Takes the filename, and returns the substring from $start up to the next $separator char.
 *  After that, it updates the value of $start to the position of the separator char + 1.
 */
function parse_next_dir($filename, $separator, &$start) {
    $prev = $start;
    $start = strpos($filename, $separator, $prev);

    $value = substr($filename, $prev, ($start - $prev));

    $start = $start + 1;

    return $value;
}

$dir = "/video/wildlife/archive";

$count = 0;
$directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);


foreach($directory_iterator as $filename => $path_object) {
    if (substr($filename, -4) == ".avi") {  //only process movie files
        /*
         * For each file:
         *  1. check to see if it has already been added to the database, it if is, skip it.
         *  2. set it's status to UNWATERMARKED.
         *  3. create entries for each 3 minute video segment of it, and set their status to UNWATERMARKED
         */

        //name format is : base/species/type_year/site/animal id/mm_dd_yy_???/CH00_yyyymmdd_hhmmssMN.avi
        $start = strlen("/video/wildlife/archive/");

        $project = parse_next_dir($filename, "/", $start);
        if ($project == "missouri_river_project") continue;
        if ($project == "lekking") continue;

        $species = parse_next_dir($filename, "/", $start);
        $site = parse_next_dir($filename, "/", $start);
        $bird_id = parse_next_dir($filename, "/", $start);

        //if the bird id contains an underscore then there is also a nest id
        //if there is no specified nest id then the nest id is 1
        $nest_id = 1;
        if (strpos($bird_id, "_")) {
            //the bird id will be everything before the first underscore
            $bird_id = substr($bird_id, 0, strpos($bird_id, "_"));

            //After the underscore could also be a note like 'hatch' or 'predation'
            $test_nest_id = substr($nest_id, strpos($bird_id, "_"));
            if (strpos($test_nest_id, "_")) {
                $nest_id_pos = 0;
                $test_nest_id = parse_next_dir($test_nest_id, "_", $nest_id_pos);
            }

            if (is_numeric($test_nest_id)) {
                $nest_id = $test_nest_id;
            }
        }

        $date = parse_next_dir($filename, "/", $start);
        $file = substr($filename, $start);

        $year = substr($file, 5, 4);
        $month = substr($file, 9, 2);
        $day = substr($file, 11, 2);

        $hour = substr($file, 14, 2);
        $minute = substr($file, 16, 2);
        $second = substr($file, 18, 2);

        echo $filename . "\n";
        echo "\tproject: '" . $project . "'\n";
        echo "\tspecies: '" . $species . "'\n";
        echo "\tsite: '" . $site . "'\n";
        echo "\tbird_id: '" . $bird_id . "'\n";
        echo "\tnest_id: '" . $nest_id . "'\n";
        echo "\tdate: '" . $date . "'\n";
        echo "\tfile: '" . $file . "'\n";

        echo "\tdate: " . $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second . "\n";


        $count++;
    }
}

echo $count . " videos in '" . $dir . "'\n";
?>
