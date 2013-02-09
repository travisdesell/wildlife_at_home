<?php

require_once("wildlife_db.php");

$video_table = "video_2";
$segment_table = "video_segment_2";
$observation_table = "observation_2";
$species_table = "species";
$location_table = "locations";

/**
 * SCRIPT STARTS HERE
 */

if (count($argv) != 2) {
    die("Error, invalid arguments. usage: php $argv[0] <number of processes>\n");
}

$number_of_processes = $argv[1];
$modulo = -1;

$child_pids = array();

/**
 *  PHP has no threads, so we need to spawn a number of processes to 
 *  watermark the video in parallel (to speed things up).
 */
for ($i = 0; $i < $number_of_processes; $i++) {
    $pid = pcntl_fork();

    if ($pid == -1) {
        die("Error, could not fork. Dying.\n");
    } else if (!$pid) {
        $modulo = $i;
        break;

    } else {
        $child_pids[] = $pid;
    }
}

if ($modulo > -1) {
    /**
     *  Each process will have it's modulo, so, a process with modulo 1
     *  of 7 will process any unwatermarked video in the database with
     *  id % 7 == 1. This way no processes are working on the same videos.
     */
    echo "This is child $modulo of $number_of_processes\n";

    //Connect to the database.
    mysql_connect("localhost", $wildlife_user, $wildlife_pw);
    mysql_select_db($wildlife_db);

    $iteration = 1;

    while(true) {   //Loop until there are no streaming segments to generate
        /* get a segment which needs to be generated from the watermarked file */
        $query = "SELECT id, video_id, number, filename FROM video_segment_2 WHERE (id % $number_of_processes) = $modulo AND processing_status = 'WATERMARKED' AND location_id = " . (($iteration % 4) + 1) . " LIMIT 1";

        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);

        if (!$row) {  //No segments left to generate, we can quit.
            echo "No videos to split with modulo $modulo of $number_of_processes for location: " . (($iteration %4) + 1 ) . ", sleeping 60 seconds\n";
            $iteration++;

            sleep(60); //sleep 5 minutes
            continue;
        }

        $segment_id = $row['id'];
        $segment_number = $row['number'];
        $archive_video_id = $row['video_id'];
        $segment_filename = $row['filename'];

        echo "query: '$query'\n";
        echo "id: " . $segment_id . "\n";
        echo "number: " . $segment_number . "\n";
        echo "video_id: " . $archive_video_id . "\n";
        echo "segment_filename: " . $segment_filename . "\n";

        /* Get required information about the file that the segment is being generated for */
        $query = "SELECT location_id, species_id, watermarked_filename, duration_s, streaming_segments FROM video_2 WHERE id = " . $row['video_id'];
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);

        $watermarked_filename = $row['watermarked_filename'];
        $archive_duration_s = $row['duration_s'];
        $streaming_segments = $row['streaming_segments'];
        $location_id = $row['location_id'];
        $species_id = $row['species_id'];

        echo "query: '$query'\n";
        echo "watermarked_filename: " . $watermarked_filename . "\n";
        echo "archive_duration_s: " . $archive_duration_s. "\n";
        echo "streaming_segments: " . $streaming_segments . "\n";


        //Need to try and create the directories to the file.
        $base_directory = substr($segment_filename, 0, strrpos($segment_filename, "/"));
        echo "attempting to create directories if they don't exist: $base_directory\n";
        mkdir($base_directory, 0755 /*all for owner, read/execute for others*/, true /*recursive*/);

        /**
         *  Calculate the start and ending time for the video segment.
         *  FFMPEG is a pain, so we need to convert from seconds to hh:mm:ss
         */
        $start_time = 180 * $segment_number;

        $duration = 180;    //duration should be 3 minutes or until the end of the video.
        if (($start_time + 180) > $archive_duration_s)  {
            $duration = ($archive_duration_s - $start_time);
        }

        $s_h = (int) ($start_time / 3600);
        $s_m = (int) (($start_time - ($s_h * 3600)) / 60);
        $s_s = (int) ($start_time - ($s_h * 3600) - ($s_m * 60));

        $d_h = 0;
        $d_m = (int) ($duration / 60);
        $d_s = (int) ($duration - ($d_m * 60));

        if ($s_h < 10) $s_h = "0" . $s_h;
        if ($s_m < 10) $s_m = "0" . $s_m;
        if ($s_s < 10) $s_s = "0" . $s_s;
        if ($d_h < 10) $d_h = "0" . $d_h;
        if ($d_m < 10) $d_m = "0" . $d_m;
        if ($d_s < 10) $d_s = "0" . $d_s;


        //Run FFMPEG to create the 3 minute (or less) segment from the watermarked video 
        $command = "ffmpeg -y -i " . $watermarked_filename . " -vcodec libx264 -vpre slow -vpre baseline -g 30 -ss " . $s_h . ":" . $s_m . ":" . $s_s . " -t " . $d_h . ":" . $d_m . ":" . $d_s . " " . $segment_filename;

        echo "command:\n\n" . $command . "\n\n";

        shell_exec($command);

        /**
         * Update the video_segment_2 table to specify that this segment is 'DONE'.
         * If all segments are done for the archive video, set it's processing status to 'SPLIT'
         */
        $query = "UPDATE video_segment_2 SET processing_status = 'DONE', duration_s = $duration WHERE id = " . $segment_id;
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $query = "SELECT count(*) FROM video_segment_2 WHERE processing_status = 'DONE' AND video_id = " . $archive_video_id;
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);

        $split_segments_generated = $row['count(*)'];

        echo "split segments generated: $split_segments_generated -- streaming_segments: $streaming_segments\n";

        if ($split_segments_generated == $streaming_segments) {
            //All the streaming segments for the video have been generated from the watermark,
            //update it's entry in the database.

            $query = "UPDATE video_2 SET processing_status = 'SPLIT' WHERE id = " . $archive_video_id;
            $result = mysql_query($query);
            if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");
        }

        $query = "UPDATE progress SET available_video_s = available_video_s + $duration WHERE progress.location_id = $location_id and progress.species_id = $species_id";
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $iteration++;
     }

} else {
    /**
     * This is the parent process. It just needs to wait for the child
     * processes to complete.  $child_pids stored the process id (pid)
     * of each child, so we can wait on them with the pcntl_waitpid
     * function.
     */
    echo "This is the parent.\n";
    for ($i = 0; $i < $number_of_processes; $i++) {
        echo "\twaiting on child " . $child_pids[$i] . " to finish.\n";
        pcntl_waitpid($child_pids[$i], $status);
        echo "\tchild " . $child_pids[$i] . " has finished.\n\n";
    }
}


?>
