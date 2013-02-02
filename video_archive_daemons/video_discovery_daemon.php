<?php

require_once("wildlife_db.php");

$video_table = "video_2";
$segment_table = "video_segment_2";
$observation_table = "observation_2";
$species_table = "species";
$location_table = "locations";


/**
 *  Takes the filename, and returns the substring from $start up to the next 
 *  $separator char.
 *  After that, it updates the value of $start to the position of the separator 
 *  char + 1.
 */
function parse_next_dir($filename, $separator, &$start) {
    $prev = $start;
    $start = strpos($filename, $separator, $prev);

    $value = substr($filename, $prev, ($start - $prev));

    $start = $start + 1;

    return $value;
}

/**
 * This splits a string based on multiple delimiters
 */
function multi_explode($pattern, $string, $standardDelimiter = ':') {
    // replace delimiters with standard delimiter, also removing redundant delimiters
    $string = preg_replace(array($pattern, "/{$standardDelimiter}+/s"), $standardDelimiter, $string);

    // return the results of explode
    return explode($standardDelimiter, $string);
}

/**
 *  This runs ffmpeg on the video to determine it's duration
 *  TODO: there might be a bug here because some of the video durations seem kind of weird.
 */
function get_video_duration($filename) {
    $command = "ffmpeg -y -i {$filename} 2>&1";
    echo "command: '$command'\n";
    ob_start();
    passthru($command);


    $info = ob_get_contents();
    ob_end_clean();

    /**
     * Output from ffmpeg looks like:
     *
     * ffmpeg -i /video/wildlife/archive/oil_development/sharptailed_grouse/Lostwood/149.564_depredation/5-31-12_149564/CH00_20120531_155651MN.avi
     * ffmpeg version 0.7.3-4:0.7.3-0ubuntu0.11.10.1, Copyright (c) 2000-2011 the Libav developers
     *   built on Jan  4 2012 16:08:51 with gcc 4.6.1
     *   configuration: --extra-version='4:0.7.3-0ubuntu0.11.10.1' --arch=amd64 --prefix=/usr --enable-vdpau --enable-bzlib --enable-libgsm --enable-libschroedinger --enable-libspeex --enable-libtheora --enable-libvorbis --enable-pthreads --enable-zlib --enable-libvpx --enable-runtime-cpudetect --enable-vaapi --enable-gpl --enable-postproc --enable-swscale --enable-x11grab --enable-libdc1394 --enable-shared --disable-static
     *   libavutil    51.  7. 0 / 51.  7. 0
     *   libavcodec   53.  6. 0 / 53.  6. 0
     *   libavformat  53.  3. 0 / 53.  3. 0
     *   libavdevice  53.  0. 0 / 53.  0. 0
     *   libavfilter   2.  4. 0 /  2.  4. 0
     *   libswscale    2.  0. 0 /  2.  0. 0
     *   libpostproc  52.  0. 0 / 52.  0. 0
     *   [avi @ 0x1d03340] max_analyze_duration reached
     *   Input #0, avi, from '/video/wildlife/archive/oil_development/sharptailed_grouse/Lostwood/149.564_depredation/5-31-12_149564/CH00_20120531_155651MN.avi':
     *   Metadata:
     *   encoder         : transcode-1.1.5
     *   Duration: 01:22:33.60, start: 0.000000, bitrate: 508 kb/s
     *   Stream #0.0: Video: h264 (High), yuv420p, 352x240, 10 fps, 10 tbr, 10 tbn, 20 tbc
     *   At least one output file must be specified
     *
     *   This should parse out the 'Duration: 01:22:33.60'
     */
    $pattern = "/Duration:\s+([0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9]?)/";
    $exists = preg_match($pattern, $info, $matches);
    if(!$exists) {
        die("No duration found.\n");
    }

    $timetotalarray = multi_explode("/[:\.]/", $matches[1]);

    echo "timetotalarray[0]: " . $timetotalarray[0] . "\n";
    echo "timetotalarray[1]: " . $timetotalarray[1] . "\n";
    echo "timetotalarray[2]: " . $timetotalarray[2] . "\n";
    echo "timetotalarray[3]: " . $timetotalarray[3] . "\n";

    echo "timetotalarray: " . $timetotalarray[0] . ":" . $timetotalarray[1] . ":" . $timetotalarray[2] . "." . $timetotalarray[3] . "\n";

    $total_seconds = ($timetotalarray[0] * 3600) + ($timetotalarray[1] * 60) + $timetotalarray[2];

    if ($timetotalarray[3] > 0) $total_seconds += 1;

    echo "total_seconds: " . $total_seconds . "\n";

//    if ($total_seconds == 0) die("Duration too low -- this should be a problem.\n");
    return $total_seconds;
}


/**
 * Check and see if the video was already added to the database
 */
function already_inserted($filename) {
    $query = "SELECT count(*) FROM video_2 AS total WHERE archive_filename LIKE '" . $filename . "'"; 
    $results = mysql_query($query);
    if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

    $row = mysql_fetch_assoc($results);

    if ($row['count(*)'] == 0) return false;
    else return true;
}

function insert_video($archive_filename, $watermarked_filename, $project_id, $location_id, $species_id, $animal_id, $start_time, $crowd_obs_count, $expert_obs_count, $machine_obs_count, $streaming_segments, $processing_status, $duration_s) {

    $query = "INSERT INTO video_2 SET " .
                "  archive_filename = '$archive_filename'" .
                ", watermarked_filename = '$watermarked_filename'" .
                ", project_id = '$project_id'" .
                ", location_id = '$location_id'" .
                ", species_id = '$species_id'" .
                ", animal_id = '$animal_id'" .
                ", start_time = '$start_time'" .
                ", crowd_obs_count = '$crowd_obs_count'" .
                ", expert_obs_count = '$expert_obs_count'" .
                ", machine_obs_count = '$machine_obs_count'" .
                ", streaming_segments = '$streaming_segments'" .
                ", processing_status = '$processing_status'" .
                ", duration_s = '$duration_s'";

    $result = mysql_query($query);
    if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");
    $video_id = mysql_insert_id();

    for ($i = 0; $i < $streaming_segments; $i++) {
        $streaming_filename = str_replace("archive", "streaming_2", $archive_filename);
        $streaming_filename = str_replace(".avi", "_CHILD$i.mp4", $streaming_filename);

        $query = "INSERT INTO video_segment_2 SET " .
                    "  video_id = '$video_id'" .
                    ", filename = '$streaming_filename'" .
                    ", crowd_obs_count = 0" .
                    ", expert_obs_count = 0" .
                    ", machine_obs_count = 0" .
                    ", interesting_count = 0" .
                    ", processing_status = 'UNWATERMARKED'";

        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");
    }
}


/**
 * SCRIPT STARTS HERE
 */

mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);


$dir = "/video/wildlife/archive";

$count = 0;
$directory_iterator = new RecursiveIteratorIterator(new 
    RecursiveDirectoryIterator($dir));

foreach($directory_iterator as $filename => $path_object) {
    if (substr($filename, -4) == ".avi") {  //only process movie files
        /*
         * For each file:
         *  1. check to see if it has already been added to the database, it if 
         *  is, skip it.
         *  2. set it's status to UNWATERMARKED.
         *  3. create entries for each 3 minute video segment of it, and set 
         *  their status to UNWATERMARKED
         */

        //name format is : base/species/type_year/site/animal 
        //id/mm_dd_yy_???/CH00_yyyymmdd_hhmmssMN.avi
        $start = strlen("/video/wildlife/archive/");

        $project = parse_next_dir($filename, "/", $start);
        if ($project == "missouri_river_project") continue;
        if ($project == "lekking") continue;

        if (already_inserted($filename)) continue;

        $species = parse_next_dir($filename, "/", $start);
        $site = parse_next_dir($filename, "/", $start);
        $animal_id = parse_next_dir($filename, "/", $start);

        //if the bird id contains an underscore then there is also a nest id
        //if there is no specified nest id then the nest id is 1
        $nest_id = 1;
        if (strpos($animal_id, "_")) {
            //the bird id will be everything before the first underscore
            $animal_id = substr($animal_id, 0, strpos($animal_id, "_"));

            //After the underscore could also be a note like 'hatch' or 
            //'predation'
            $test_nest_id = substr($nest_id, strpos($animal_id, "_"));
            if (strpos($test_nest_id, "_")) {
                $nest_id_pos = 0;
                $test_nest_id = parse_next_dir($test_nest_id, "_", 
                    $nest_id_pos);
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

        $duration_s = get_video_duration($filename);
        $streaming_segments = ceil($duration_s / 180);  //number of 3 minute segments to be generated

        $watermarked_filename = "/video/wildlife/watermarked/" . substr($filename, strlen("/video/wildlife/archive/"));
        $watermarked_filename = str_replace(".avi", ".mp4", $watermarked_filename);

        $crowd_obs_count = 0;
        $expert_obs_count = 0;
        $machine_obs_count = 0;
        $processing_status = "UNWATERMARKED";

        if ($species == "sharptailed_grouse") {
            $species_id = 0;
        } else if ($species == "piping_plover") {
            $species_id = 1;
        } else if ($species == "least_tern") {
            $species_id = 2;
        } else {
            die("Uknown species encountered: '$species'");
        }

        if ($project == "oil_development") {
            $project_id = 0;
        } else if ($project == "lekking") {
            $project_id = 1;
        } else if ($project == "missouri_river_project") {
            $project_id = 2;
        } else {
            die("Unknown project encountered: '$project'");
        }

        if ($site == "Belden") {
            $site_id = 0;
        } else if ($site == "Blaisdell") {
            $site_id = 1;
        } else if ($site == "Lostwood") {
            $site_id = 2;
        } else {
            die("Unknown location encountered: '$location'");
        }
        
        echo $filename . "\n";
        echo $watermarked_filename . "\n";
        echo "\tproject: '" . $project . "'\n";
        echo "\tproject_id: '" . $project_id . "'\n";
        echo "\tspecies: '" . $species . "'\n";
        echo "\tspecies_id: '" . $species_id . "'\n";
        echo "\tlocation: '" . $site . "'\n";
        echo "\tlocation_id: '" . $site_id . "'\n";
        echo "\tanimal_id: '" . $animal_id . "'\n";
        echo "\tnest_id: '" . $nest_id . "'\n";
        echo "\tstart_time: '" . $date . "'\n";
        echo "\tfile: '" . $file . "'\n";

        $start_time = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;
        echo "\tstart_time: '" . $start_time . "'\n";

        echo "\tduration_s: " . $duration_s . "\n";
        echo "\tstreaming_segments: " . $streaming_segments . "\n";
        echo "\tprocessing_status: " . $processing_status . "\n";

        if ($duration_s == 0) {
            echo "Duration was 0, skipping.\n";
            continue;
        }

        insert_video($filename, $watermarked_filename, $project_id, $site_id, $species_id, $animal_id, $start_time, $crowd_obs_count, $expert_obs_count, $machine_obs_count, $streaming_segments, $processing_status, $duration_s);

        $count++;
    }
}

echo $count . " videos in '" . $dir . "'\n";
?>
