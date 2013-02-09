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
//        die("No duration found.\n");
        return 0;
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

mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);

$query = "SELECT id, filename FROM video_segment_2 WHERE processing_status != 'UNWATERMARKED' and duration_s = 0";
$results = mysql_query($query);
if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

while ($row = mysql_fetch_assoc($results)) {
    $duration_s = get_video_duration($row['filename']);

    $query_2 = "UPDATE video_segment_2 SET duration_s = $duration_s WHERE id = " . $row['id'];
    $result_2 = mysql_query($query_2);
    if (!$result_2) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query_2\n");

    echo $query_2 . "\n";
}

?>
