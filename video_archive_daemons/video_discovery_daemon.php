<?php

require_once("wildlife_db.php");

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
    $command = "avconv -y -i {$filename} 2>&1";
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
        echo("No duration found.\n");
        throw new Exception('Duration not found by ffmpeg, corrupt video?');
    }

    $timetotalarray = multi_explode("/[:\.]/", $matches[1]);

//    echo "timetotalarray[0]: " . $timetotalarray[0] . "\n";
//    echo "timetotalarray[1]: " . $timetotalarray[1] . "\n";
//    echo "timetotalarray[2]: " . $timetotalarray[2] . "\n";
//    echo "timetotalarray[3]: " . $timetotalarray[3] . "\n";

//    echo "timetotalarray: " . $timetotalarray[0] . ":" . $timetotalarray[1] . ":" . $timetotalarray[2] . "." . $timetotalarray[3] . "\n";

    $total_seconds = ($timetotalarray[0] * 3600) + ($timetotalarray[1] * 60) + $timetotalarray[2];

    if ($timetotalarray[3] > 0) $total_seconds += 1;

//    echo "total_seconds: " . $total_seconds . "\n";

//    if ($total_seconds == 0) die("Duration too low -- this should be a problem.\n");
    return $total_seconds;
}


/**
 * Check and see if the video was already added to the database
 */
function already_inserted($filename) {
    $query = "SELECT count(*) FROM video_2 AS total WHERE archive_filename LIKE '" . $filename . "'"; 
    $results = query_video_db($query);

    $row = mysql_fetch_assoc($results);

    if ($row['count(*)'] == 0) return false;
    else return true;
}

/**
 * SCRIPT STARTS HERE
 */

$dir = "/share/wildlife/archive";

$count = 0;
$directory_iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

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

        //name format is : 
        //GROUSE:
        //  /share/wildlife/archive/oil_development/2012/sharptailed_grouse/Belden/149.085_hatch/5-30-12_149085/
        //  /share/wildlife/archive/oil_development/2013/sharptailed_grouse/Belden/149.783_N1_Hatch/149.783_7.2.13/
        //
        //  Directory after site is animal id
        //
        //PLOVER/TERN:
        //  /share/wildlife/archive/missouri_river_project/2012/least_tern/XXXX.X/N1030/7-16-2012_N1030/
        //  /share/wildlife/archive/missouri_river_project/2013/least_tern/1352.1/410/06252013/
        //  /share/wildlife/archive/missouri_river_project/2012/piping_plover/XXXX.X/N1031_24LED_cam/7-23-12_N1031/
        //  /share/wildlife/archive/missouri_river_project/2013/piping_plover/1357.0/506/05252013/
        //
        //  Directory after species name is river mile, directory after that is animal id

        $parts = split("/", $filename);

        for ($i = 0; $i < count($parts); $i++) {
            if ($parts[$i] == 'missouri_river_project' || $parts[$i] == 'oil_development' || $parts[$i] == 'lekking' || $parts[$i] == 'Coteau_Ranch') break;
        }

        $project = $parts[$i];

        if ($project == "missouri_river_project") continue;
        if ($project == "oil_development") continue;
        if ($project == "lekking") continue;
//        echo "CHECKING: $filename\n";
        if (already_inserted($filename)) continue;

        $directory_year = $parts[$i + 1];
        $species = $parts[$i + 2];

        if ($project == "missouri_river_project") {
            $site = "Missouri River";
            $rivermile = $parts[$i + 3];
            $animal_id = $parts[$i + 4];
        } else if ($project == "Coteau_Ranch") {
            $site = "Coteau Ranch";
            $animal_id = $parts[$i + 3];
        } else {
            $site = $parts[$i + 3];
            $animal_id = $parts[$i + 4];
        }

        $date = basename($filename);
        $file = basename($filename);

        $year = substr($file, 5, 4);
        $month = substr($file, 9, 2);
        $day = substr($file, 11, 2);

        $hour = substr($file, 14, 2);
        $minute = substr($file, 16, 2);
        $second = substr($file, 18, 2);

        if ($year == '0000' || !is_numeric($year)) {
            echo "filename is: '$filename'\n";
            echo "year is: '$year'\n";
            echo "improperly formatted year! file is: '$file'\n";
            continue;
            die("improperly formatted year! file is: '$file'\n");
            $year = '0000';
            $month = '00';
            $day = '00';
            $hour = '00';
            $minute = '00';
            $second = '00';
        }

        try {
            $duration_s = get_video_duration($filename);
        } catch (Exception $e) {
            die("Problems parsing duration.\n");
        }

        if ($duration_s == 0) {
            echo "Duration was 0, skipping: $filename\n";
            continue;
        }


        $watermarked_filename = "/share/wildlife/watermarked/" . substr($filename, strlen("/share/wildlife/archive/"));
        $watermarked_filename = str_replace(".avi", "", $watermarked_filename);

        $crowd_obs_count = 0;
        $expert_obs_count = 0;
        $machine_obs_count = 0;
        $processing_status = "UNWATERMARKED";

        if ($species == "sharptailed_grouse") {
            $species_id = 1;
        } else if ($species == "least_tern") {
            $species_id = 2;
        } else if ($species == "piping_plover") {
            $species_id = 3;
        } else if ($species == "BWTE") {
            $species_id = 4;
        } else {
            die("Unknown species encountered: '$species'");
        }

        if ($project == "oil_development") {
            $project_id = 1;
        } else if ($project == "lekking") {
            $project_id = 2;
        } else if ($project == "missouri_river_project") {
            $project_id = 3;
        } else if ($project == "Coteau_Ranch") {
            $project_id = 4;
        } else {
            die("Unknown project encountered: '$project'");
        }

        if ($site == "Belden") {
            $location_id = 1;
        } else if ($site == "Blaisdell") {
            $location_id = 2;
        } else if ($site == "Lostwood") {
            $location_id = 3;
        } else if ($site == "Missouri River") {
            $location_id = 4;
        } else if ($site == "Coteau Ranch") {
            $location_id = 7;
        } else {
            echo "filename: $filename \n";
            die("Unknown location encountered: '$site' for year '$directory_year'\n");
        }

        $archive_filename = $filename;
        echo $filename . "\n";
        echo $watermarked_filename . "\n";
        echo "\tproject: '" . $project . "'\n";
        echo "\tproject_id: '" . $project_id . "'\n";
        echo "\tdirectory_year: '" . $directory_year . "'\n";
        echo "\tspecies: '" . $species . "'\n";
        echo "\tspecies_id: '" . $species_id . "'\n";
        echo "\tlocation: '" . $site . "'\n";
        echo "\tlocation_id: '" . $location_id . "'\n";
        echo "\tanimal_id: '" . $animal_id . "'\n";
        if ($species == 2 || $species == 3) {
            echo "\trivermile: '" . $rivermile. "'\n";
        }
        echo "\tstart_time: '" . $date . "'\n";
        echo "\tfile: '" . $file . "'\n";

        $start_time = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;
        echo "\tstart_time: '" . $start_time . "'\n";

        echo "\tduration_s: " . $duration_s . "\n";
        echo "\tprocessing_status: " . $processing_status . "\n";

        $query = "INSERT INTO video_2 SET " .
            "  archive_filename = '$archive_filename'" .
            ", watermarked_filename = '$watermarked_filename'" .
            ", project_id = '$project_id'" .
            ", location_id = '$location_id'" .
            ", species_id = '$species_id'" .
            ", animal_id = '$animal_id'";

        if ($species == 2 || $species == 3) {
            $query .= ", rivermile = '$rivermile'";
        }

        $query .=
            ", start_time = '$start_time'" .
            ", crowd_obs_count = '$crowd_obs_count'" .
            ", expert_obs_count = '$expert_obs_count'" .
            ", machine_obs_count = '$machine_obs_count'" .
            ", streaming_segments = -1" .  //another daemon is going to do this now
            ", processing_status = '$processing_status'" .
            ", duration_s = '$duration_s'" .
            ", release_to_public = false";

        echo $query . "\n";
//        die();

        $result = query_video_db($query);

        $count++;
    }
}

echo $count . " videos in '" . $dir . "'\n";

echo "updating total video progress\n";

$query = "UPDATE progress AS p SET total_video_s = (SELECT SUM(duration_S) FROM video_2 AS v2 WHERE v2.species_id = p.species_id AND v2.location_id = p.location_id)";
//$results = query_video_db($query);

?>
