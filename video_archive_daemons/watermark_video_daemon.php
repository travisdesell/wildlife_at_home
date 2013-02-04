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
        pcntl_wait($status); //Protect against Zombie children
        $child_pids[] = $pid;
    }
}

if ($modulo == -1) {
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

} else {
    /**
     *  Each process will have it's modulo, so, a process with modulo 1
     *  of 7 will process any unwatermarked video in the database with
     *  id % 7 == 1. This way no processes are working on the same videos.
     */
    echo "This is child $modulo of $number_of_processes\n";

    //Connect to the database.
    mysql_connect("localhost", $wildlife_user, $wildlife_pw);
    mysql_select_db($wildlife_db);

    while(true) {   //Loop until there are no more videos to watermark.
        $query = "SELECT id, archive_filename, watermarked_filename FROM video_2 WHERE (id % $number_of_processes) = $modulo AND processing_status = 'UNWATERMARKED' LIMIT 1";

        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);

        if (!$row) {  //No videos left to watermark, we can quit.
            echo "No videos to watermark with modulo $modulo of $number_of_processes.\n";
            break;
        }

        echo "query: '$query'\n";
        echo "id: " . $row['id'] . "\n";
        echo "archive_filename: " . $row['archive_filename'] . "\n";
        echo "watermarked_filename: " . $row['watermarked_filename'] . "\n\n";

        //Need to try and create the directories to the file.
        $base_directory = substr($row['watermarked_filename'], 0, strrpos($row['watermarked_filename'], "/"));
        echo "attempting to create directories if they don't exist: $base_directory\n";
        mkdir($base_directory, 0755 /*all for owner, read/execute for others*/, true /*recursive*/);

        //Run FFMPEG to do the watermarking, also convert the file to mp4 so we can
        //use HTML5 to stream it
        $watermark_file = "/video/wildlife/watermark.png";
        $command = "ffmpeg -y -i " . $row['archive_filename']. " -ar 44100 -vb 400000 -qmax 5 -vf \"movie=$watermark_file [watermark]; [in] [watermark] overlay=10:10 [out]\" " . $row['watermarked_filename'];
        shell_exec($command);

        /**
         *  After teh file has been successfully watermarked, update the processing_status to 'WATERMARKED'
         *  for both the video and its segments. Now teh splitting daemon will be able to take the watermarked
         *  file and generate the segments.
         */
        $query = "UPDATE video_2 SET processing_status = 'WATERMARKED' WHERE id = " . $row['id'];
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $query = "UPDATE video_segment_2 SET processing_status = 'WATERMARKED' WHERE video_id = " . $row['id'];
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        break;
    }
}

?>
