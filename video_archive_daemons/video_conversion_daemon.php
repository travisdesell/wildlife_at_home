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

    //Connect to the database.
    mysql_connect("localhost", $wildlife_user, $wildlife_pw);
    mysql_select_db($wildlife_db);

    $iteration = 0;
    $videos_not_found = 0;

    while(true) {   //Loop until there are no more videos to watermark.
        $query = "";

        //Iterate over all the locations and species so we're generating
        //videos for them someone uniformly.
        if ($iteration % 5 == 0) {
            $species_id = 1;
            $location_id = 1;
        } else if ($iteration % 5 == 1) {
            $species_id = 1;
            $location_id = 2;
        } else if ($iteration % 5 == 2) {
            $species_id = 1;
            $location_id = 3;
        } else if ($iteration % 5 == 3) {
            $species_id = 2;
            $location_id = 4;
        } else if ($iteration % 5 == 4) {
            $species_id = 3;
            $location_id = 4;
        }
        $iteration++;

        $query = "SELECT id, archive_filename, watermarked_filename, processing_status, duration_s FROM video_2 WHERE (id % $number_of_processes) = $modulo AND processing_status != 'SPLIT' AND species_id = $species_id AND location_id = $location_id LIMIT 1";
        echo $query . "\n";

        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);

        if (!$row) { 
            echo("No videos left to convert, attempt: $videos_not_found");
            $videos_not_found++;
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

        if ($row['processing_status'] == 'UNWATERMARKED') {
            //This video hasn't been watermarked yet, watermark it.

            //Need to try and create the directories to the file.
            $base_directory = substr($watermarked_filename, 0, strrpos($watermarked_filename, "/"));
            echo "attempting to create directories if they don't exist: $base_directory\n";
            mkdir($base_directory, 0755 /*all for owner, read/execute for others*/, true /*recursive*/);

            //Run FFMPEG to do the watermarking, also convert the file to mp4 so we can
            //use HTML5 to stream it
            $watermark_file = "/video/wildlife/watermark.png";
            //This was being used to generate the watermarked files
//          $command = "/home/tdesell/ffmpeg/bin/ffmpeg -y -i $archive_filename -ar 44100 -vb 400000 -qmax 5 -vcodec libx264 -level 30 -maxrate 10000000 -bufsize 10000000 -vprofile baseline -g 30 -vf \"movie=$watermark_file [watermark]; [in] [watermark] overlay=10:10 [out]\" $watermarked_filename";

//          this is used for generating the streaming segments
//          $command = "/usr/bin/ffmpeg -y -i " . $watermarked_filename . " -vcodec libx264 -vpre slow -vpre baseline -g 30 -ss " . $s_h . ":" . $s_m . ":" . $s_s . " -t " . $d_h . ":" . $d_m . ":" . $d_s . " " . $segment_filename . ".mp4";

            //This should generate better sized videos
            $command = "/home/tdesell/ffmpeg/bin/ffmpeg -y -i $archive_filename -vcodec libx264 -vpre slow -vpre baseline -g 30 -vf \"movie=$watermark_file [watermark]; [in] [watermark] overlay=10:10 [out]\" $watermarked_filename.mp4";

            echo "\n\n$command\n\n";
            shell_exec($command);

            echo "shell exec 1 completed\n\n";

            $command = "/usr/bin/ffmpeg -y -i $watermarked_filename.mp4 -vcodec libtheora -acodec libvorbis -ab 160000 -g 30 $watermarked_filename.ogv";
            echo "\n\n$command\n\n";
            shell_exec($command);


            echo "shell exec 2 completed\n\n";

            /**
             *  After the file has been successfully watermarked, update the processing_status to 'WATERMARKED'
             *  for both the video and its segments. Now teh splitting daemon will be able to take the watermarked
             *  file and generate the segments.
             *
             *  We also need to add its md5 hash and the file size so boinc can use these to generate workunits
             */
            $md5_hash = md5_file($watermarked_filename . ".mp4");
            $filesize = filesize($watermarked_filename . ".mp4");

            $query = "UPDATE video_2 SET processing_status = 'WATERMARKED', size = $filesize, md5_hash = '$md5_hash', ogv_generated = true WHERE id = " . $row['id'];
            $result = mysql_query($query);
            if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

            $query = "UPDATE video_segment_2 SET processing_status = 'WATERMARKED' WHERE video_id = " . $row['id'];
            $result = mysql_query($query);
            if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");
        }

        //The video has now been watermarked (either by this script, or done before), split it into
        //segments for streaming by the crowd sourced video watching webpages

        //1. Determine how many segments to make.

        $rval = mt_rand() / mt_getrandmax();
        if ($rval < (1.0/3.0)) {
            $segment_duration = 5 * 60;
        } else if ($rval < (2.0/3.0)) {
            $segment_duration = 10 * 60;
        } else {
            $segment_duration = 20 * 60;
        }

        $number_segments = ceil($duration_s / $segment_duration);
        echo "video   duration: $duration_s\n";
        echo "segment duration: $segment_duration\n";
        echo "generating $number_segments streaming segments\n";

        //2. Clean up what was in there before for streaming segments
        $query = "DELETE FROM video_segment_2 WHERE video_id = $video_id";
        echo $query . "\n";
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        $segment_filepath = "/share/wildlife/streaming_2";

        $paths = explode("/", $archive_filename);
        for ($i = 4; $i < count($paths); $i++) {
            echo "appending paths[$i]: '" . $paths[$i] . "\n";
            $segment_filepath .= "/" . $paths[$i];
        }
        $segment_filepath = substr($segment_filepath, 0, -4);

        //We need to remove any previously created files, in case the daemon hung in the middle of creating them and is
        //now generating them with a different duration.
        $command = "rm " . $segment_filepath . "_CHILD*";
        echo "removing: $command\n\n";
        shell_exec($command);
//        die();

        if ($duration_s <= $segment_duration) {
            //The duration of the segment would be greater than the length of the actual video,
            //so we don't need to convert it a second time.  Just use the watermarked file.
            $query = "INSERT INTO video_segment_2 SET " .
                            "video_id = $video_id, " .
                            "filename = '" . substr($watermarked_filename, 0, -4) . "', " .
                            "crowd_obs_count = 0, expert_obs_count = 0, machine_obs_count = 0, interesting_count = 0, " .
                            "processing_status = 'DONE', " .
                            "number = 0, " .
                            "location_id = $location_id, " .
                            "species_id = $species_id, " .
                            "crowd_status = 'UNWATCHED', " .
                            "duration_s = $duration_s, " .
                            "broken = 0, too_dark = 0, " .
                            "required_views = 2, " .
                            "report_status = 'UNREPORTED', " .
                            "validate_for_review = false, " .
                            "instructional = false, ";

            if ($species_id == 1) {
                $query .= "release_to_public = true";
            } else {
                $query .= "release_to_public = false";
            }

            echo $query . "\n";
            $result = mysql_query($query);
            if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

        } else {

            for ($current_segment = 0; $current_segment < $number_segments; $current_segment++) {
                $segment_filename = $segment_filepath . "_CHILD" . $current_segment;

                echo "CREATING FILE: '$segment_filename'\n";

                //Need to try and create the directories to the file.
                $base_directory = substr($segment_filename, 0, strrpos($segment_filename, "/"));
                echo "attempting to create directories if they don't exist: $base_directory\n";
                mkdir($base_directory, 0755 /*all for owner, read/execute for others*/, true /*recursive*/);

                /**
                 *  Calculate the start and ending time for the video segment.
                 *  FFMPEG is a pain, so we need to convert from seconds to hh:mm:ss
                 */
                $start_time = $segment_duration * $current_segment;

                if (($start_time + $segment_duration) > $duration_s)  {
                    $segment_duration = ($duration_s - $start_time);
                }

                $s_h = (int) ($start_time / 3600);
                $s_m = (int) (($start_time - ($s_h * 3600)) / 60);
                $s_s = (int) ($start_time - ($s_h * 3600) - ($s_m * 60));

                $d_h = 0;
                $d_m = (int) ($segment_duration / 60);
                $d_s = (int) ($segment_duration - ($d_m * 60));

                if ($s_h < 10) $s_h = "0" . $s_h;
                if ($s_m < 10) $s_m = "0" . $s_m;
                if ($s_s < 10) $s_s = "0" . $s_s;
                if ($d_h < 10) $d_h = "0" . $d_h;
                if ($d_m < 10) $d_m = "0" . $d_m;
                if ($d_s < 10) $d_s = "0" . $d_s;

                //Run FFMPEG to create the segment from the watermarked video 
                $command = "/usr/bin/ffmpeg -y -i " . $watermarked_filename . ".mp4 -vcodec libx264 -vpre slow -vpre baseline -g 30 -ss " . $s_h . ":" . $s_m . ":" . $s_s . " -t " . $d_h . ":" . $d_m . ":" . $d_s . " " . $segment_filename . ".mp4";
                echo "command:\n\n" . $command . "\n\n";
                shell_exec($command);

                //also generate an ogv file for firefox
                $command = "/usr/bin/ffmpeg -y -i " . $watermarked_filename . ".ogv -vcodec libtheora -acodec libvorbis -ab 160000 -g 30 -ss " . $s_h . ":" . $s_m . ":" . $s_s . " -t " . $d_h . ":" . $d_m . ":" . $d_s . " " . $segment_filename . ".ogv";
                echo "command:\n\n" . $command . "\n\n";
                shell_exec($command);

                //Convert the file.
                $query = "INSERT INTO video_segment_2 SET " .
                            "video_id = $video_id, " .
                            "filename = '$segment_filename', " .
                            "crowd_obs_count = 0, expert_obs_count = 0, machine_obs_count = 0, interesting_count = 0, " .
                            "processing_status = 'DONE', " .
                            "number = $current_segment, " .
                            "location_id = $location_id, " .
                            "species_id = $species_id, " .
                            "crowd_status = 'UNWATCHED', " .
                            "duration_s = $segment_duration, " .
                            "broken = 0, too_dark = 0, " .
                            "required_views = 2, " .
                            "report_status = 'UNREPORTED', " .
                            "validate_for_review = false, " .
                            "instructional = false, ";

                if ($species_id == 1) {
                        $query .= "release_to_public = true";
                } else {
                        $query .= "release_to_public = false";
                }

                echo $query . "\n";
                $result = mysql_query($query);
                if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");
            }
        }

        $query = "UPDATE video_2 SET streaming_segments = $number_segments, processing_status = 'SPLIT' where id = $video_id";
        echo $query . "\n";
        $result = mysql_query($query);
        if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

//        die("DEAD!");
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
