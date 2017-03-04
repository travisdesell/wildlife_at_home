<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../db_info/hodor.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

// change this for a different queue size
$queue_size = 16;
$queue_timeout = 0;

$iteration = -1;
$species_location_arr = array(
    array(
        'specied_id' => 1,
        'location_id' => 1
    ),
    array(
        'specied_id' => 1,
        'location_id' => 2
    ),
    array(
        'specied_id' => 1,
        'location_id' => 3
    ),
    array(
        'specied_id' => 2,
        'location_id' => 4
    ),
    array(
        'specied_id' => 3,
        'location_id' => 4
    ),
    array(
        'specied_id' => 4,
        'location_id' => 7
    )
);

function next_iteration(int $iter, array &$arr) {
    ++$iter;
    if ($iter >= count($arr) || $iter < 0) {
        $iter = 0;
    }
    return $iter;
}

while (1) {
    // decrease our queue timeout
    if ($queue_timeout > 0) {
        --$queue_timeout;
    }

    // query the server for the queued file list
    $queued = array();
    exec("ssh $hodor_username@$hodor_host 'ls $hodor_queue_dir'", $queued);

    // query the server for the done file list
    $done = array();
    exec("ssh $hodor_username@$hodor_host 'ls $hodor_done_dir'", $done);

    // build our ids (done or queued)
    $ids = array();
    foreach ($queued as &$q) {
        $ids[] = explode('.', $q, 2)[0];
    }
    foreach ($done as &$d) {
        $id = explode('.', $d, 2)[0];

        // we need to check if it's already in the array because there
        // is the chance that between the queued and done SSH calls,
        // one file could be moved from queued to done
        if (!in_array($id, $ids)) {
            $ids[] = $id;
        }
    }

    // fill the queue if we have slots (if we aren't timed out)
    for ($i = count($queued); $queue_timeout == 0 && $i < $queue_size; ++$i, ++$iteration) {
        $iteration = next_iteration($iteration, $species_location_arr);
        $firstiteration = $iteration;

        echo "Filling the queue with " . ($queue_size - count($queued)) . " videos\n";

        // loop through all our species / locations once until we get one
        $result = null;
        do {
            $species_id  = $species_location_arr[$iteration]['specied_id'];
            $location_id = $species_location_arr[$iteration]['location_id'];

            $notin = "";
            if (count($ids) > 0) {
                $notin = "AND id NOT IN (" . implode(',', $ids) . ")";
            }

            $query = "SELECT id, archive_filename FROM video_2 WHERE processing_status = 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id $notin LIMIT 1";

            $result = query_wildlife_video_db($query);
            if ($result && $result->num_rows == 1) {
                break;
            }

            $iteration = next_iteration($iteration, $species_location_arr);
        } while ($iteration != $firstiteration);

        // if we still don't have an result, we've done all the videos!
        if (!$result || $result->num_rows != 1) {
            echo "[WARNING] No more videos to convert!\n";
            $queue_timeout = 100; // timeout for 100 cycles
        }

        // get the row
        $row = $result->fetch_assoc();
        $video_id = $row['id'];
        $archive_filename = $row['archive_filename'];
        $ext = pathinfo($archive_filename)['extension'];

        // triple check that our id isn't set in the array
        if (in_array($video_id, $ids)) {
            echo "\t[ERROR] ID is already in the list: $video_id\n";
            continue;
        }

        // upload the file
        echo "\nUploading $video_id => $archive_filename...\n";
        $arr = array();
        $retval = 1;
        $videoname = "$video_id.$ext";
        exec("scp $archive_filename $hodor_username@$hodor_host:$hodor_upload_dir/$videoname", $arr, $retval);

        // check for errors
        if ($retval != 0) {
            echo "\t[ERROR] Upload: $arr[0]\n";
            continue;
        }

        // move the file from upload to queue
        exec("ssh $hodor_username@$hodor_host 'mv $hodor_upload_dir/$videoname $hodor_queue_dir/$videoname'");

        // ad our video_id to the end of the ids so it isn't picked up again
        $ids[] = $video_id;

        // done
        echo "\tDone.\n";
    }


    // download the files
    foreach ($done as &$video) {
        // split the extension and the id
        $parts = pathinfo($video);
        $video_id = $parts['filename'];
        $ext = $parts['extension'];

        if ($ext != 'mp4' && $ext != 'ogv') {
            echo "\n[ERROR] $video isn't a good video filename.\n";
            exec("ssh $hodor_username@$hodor_host 'rm $hodor_done_dir/$video'");
            continue;
        }

        // get the id and extension
        echo "\nDownloading $video_id => $video...\n";

        // figure out where in the database it is
        $result = query_wildlife_video_db("SELECT watermarked_filename FROM video_2 WHERE id=$video_id AND processing_status='UNWATERMARKED'");

        // if it's not in the database, just delete the file from the server
        if (!$result || $result->num_rows == 0) {
            echo "\t[ERROR] Unable to find id = $video_id\n";
            //exec("ssh $hodor_username@$hodor_host 'rm $hodor_done_dir/$video'");
            continue;
        }

        // make our directory
        $row = $result->fetch_assoc();
        $watermarked_filename = $row['watermarked_filename'] . ".$ext";
        mkdir(dirname($watermarked_filename), 0775, true /*recursive*/);

        // if the file already exists, unlink it (for issues with partial files)
        if (file_exists($watermarked_filename)) {
            echo "\tFile already exists... Deleting before downloading.\n";
            unlink($watermarked_filename);
        }

        // scp the file down
        echo "\tSaving as $watermarked_filename...\n";
        $arr = array();
        $retval = 1;
        exec("scp $hodor_username@$hodor_host:$hodor_done_dir/$video $watermarked_filename", $arr, $retval);

        // make sure scp succeeded
        if ($retval != 0) {
            echo "\t\t[ERROR] Failed to SCP the file. Will try again later.\n";
            continue;
        }
        echo "\t\tDone.\n";

        // update the database
        $md5_hash = md5_file($watermarked_filename);
        $filesize = filesize($watermarked_filename);

        $ogvset = "";
        if ($ext == 'ogv') {
            $ogvset = "ogv_generated=true, "; 
        }

        echo "\tUpdating the database...\n";
        $query = "UPDATE video_2 SET $ogvset processing_status='WATERMARKED', size=$filesize, md5_hash='$md5_hash', needs_reconversion=false WHERE id=$video_id";
        $result = query_wildlife_video_db($query);

        if (!$result) {
            echo "\t\t[ERROR] Failed to update the databse. Will try again later.\n";
            continue;
        }
        echo "\t\tDone.\n";

        echo "\tDeleting remote file...\n";
        exec("ssh $hodor_username@$hodor_host 'rm $hodor_done_dir/$video'");
        echo "\t\tDone.";
    }

    // sleep for 1 minute
    sleep(60);
}

?>
