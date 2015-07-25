<?php

require_once("wildlife_db.php");

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

$species_id = 1;
$location_id = 0;

if ($modulo > -1) {
    /**
     *  Each process will have it's modulo, so, a process with modulo 1
     *  of 7 will process any unwatermarked video in the database with
     *  id % 7 == 1. This way no processes are working on the same videos.
     */
    echo "This is child $modulo of $number_of_processes\n";

    $iteration = 0;
    $videos_not_found = 0;

    while(true) {   //Loop until there are no more videos to watermark.
        $query = "";

        //Iterate over all the locations and species so we're generating
        //videos for them somewhat uniformly.
        if ($iteration % 6 == 0) {
            $species_id = 1;
            $location_id = 1;
        } else if ($iteration % 6 == 1) {
            $species_id = 1;
            $location_id = 2;
        } else if ($iteration % 6 == 2) {
            $species_id = 1;
            $location_id = 3;
        } else if ($iteration % 6 == 3) {
            $species_id = 2;
            $location_id = 4;
        } else if ($iteration % 6 == 4) {
            $species_id = 3;
            $location_id = 4;
        } else if ($iteration % 6 == 5) {
            $species_id = 4;
            $location_id = 7;
        }
        $iteration++;

        $query = "SELECT id, archive_filename, watermarked_filename, processing_status, duration_s FROM video_2 WHERE (id % $number_of_processes) = $modulo AND processing_status = 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id LIMIT 1";
        echo $query . "\n";

        $result = query_video_db($query);
        if(!$result) {
            echo("Query error: $videos_not_found\n");
        } else {
            $row = $result->fetch_assoc();
        }

        if (!$row) { 
            echo("No videos left to convert, attempt: $videos_not_found\n");
            $videos_not_found++;
            continue;
            if ($videos_not_found >= 5) die("No video left to convert with modulo $modulo!");
        } else {
            $videos_not_found = 0;
        }

        $video_id = $row['id'];
        $archive_filename = $row['archive_filename'];
        $watermarked_filename = $row['watermarked_filename'];
        $processing_status = $row['processing_status'];
        $duration_s = $row['duration_s'];
        echo "query: '$query'\n";
        echo "id: " . $video_id . "\n";
        echo "archive_filename: " . $archive_filename . "\n";
        echo "watermarked_filename: " . $watermarked_filename . "\n\n";
        echo "processing_status: " . $processing_status . "\n\n";
        echo "archive_duration_s: " . $duration_s . "\n\n";

        //This video hasn't been watermarked yet, watermark it.

        //Need to try and create the directories to the file.
        $base_directory = substr($watermarked_filename, 0, strrpos($watermarked_filename, "/"));
        echo "attempting to create directories if they don't exist: $base_directory\n";
        mkdir($base_directory, 0755 /*all for owner, read/execute for others*/, true /*recursive*/);

        //Run FFMPEG to do the watermarking, also convert the file to mp4 so we can
        //use HTML5 to stream it
        $und_watermark_file = "/video/wildlife/und_watermark.png";
        $duck_watermark_file = "/video/wildlife/duck_watermark.png";

        //This should generate better sized videos
        if($location_id == 7) {
            $command = "ffmpeg -y -i $archive_filename -i $und_watermark_file -i $duck_watermark_file -vcodec h264 -qscale:v 3 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10 [und_marked]; [2:v]scale=79:50 [duck]; [und_marked][duck]overlay=x=10:y=(main_h-overlay_h)' $watermarked_filename.mp4 2>&1; echo $?";
        } else {
            $command = "ffmpeg -y -i $archive_filename -i $und_watermark_file -vcodec libx264 -preset slow -filter_complex 'overlay=x=10:y=10' -b:v 200k $watermarked_filename.mp4 2>&1; echo $?";
        }

        echo "\n\n$command\n\n";
        $output_status = shell_exec($command);
        //        echo "strlen(output status): " . strlen($output_status) . "\n";
        //        echo "output status: " . $output_status . "\n\n";
        //1 looks like an error

        if (strlen($output_status) < 2 || $output_status{strlen($output_status) - 2} != '0') {
            echo "FFMPEG conversion to mp4 failed, dying!\n";
            echo "output status:\n\n" . $output_status ."\n\n";
            die();
        }

        echo "shell exec 1 completed\n\n";

        if($location_id == 7) {
            $command = "ffmpeg -y -i $archive_filename -i $und_watermark_file -i $duck_watermark_file -vcodec theora -qscale:v 6 -an -filter_complex '[1:v]scale=87:40 [und]; [0:v][und]overlay=x=10:y=10 [und_marked]; [2:v]scale=79:50 [duck]; [und_marked][duck]overlay=x=10:y=(main_h-overlay_h)' $watermarked_filename.ogv 2>&1; echo $?";
        } else {
            $command = "ffmpeg -y -i $archive_filename -i $und_watermark_file -vcodec theora -preset slow -filter_complex 'overlay=x=10:y=10' -b:v 200k $watermarked_filename.ogv 2>&1; echo $?";
        }

        echo "\n\n$command\n\n";

        $output_status = shell_exec($command);
        if (strlen($output_status) < 2 || $output_status{strlen($output_status) - 2} != '0') {
            echo "FFMPEG conversion to ogv failed, dying!\n";
            echo "output status:\n\n" . $output_status ."\n\n";
            die();
        }

        echo "shell exec 2 completed\n\n";
//        die();

        /**
         *  After the file has been successfully watermarked, update the processing_status to 'WATERMARKED'
         *  for both the video and its segments. Now teh splitting daemon will be able to take the watermarked
         *  file and generate the segments.
         *
         *  We also need to add its md5 hash and the file size so boinc can use these to generate workunits
         */
        $md5_hash = md5_file($watermarked_filename . ".mp4");
        $filesize = filesize($watermarked_filename . ".mp4");

        $query = "UPDATE video_2 SET processing_status = 'WATERMARKED', size = $filesize, md5_hash = '$md5_hash', ogv_generated = true, needs_reconversion = false WHERE id = " . $video_id;
        $result = query_video_db($query);
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

