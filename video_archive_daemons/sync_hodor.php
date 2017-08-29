#!/usr/bin/env php

<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../db_info/hodor.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

// make sure we have directories for the queues
is_dir($hodor_queue_dir) or die("Queue directory doesn't exists: $hodor_queue_dir");
is_dir($hodor_done_dir)  or die("Queue directory doesn't exists: $hodor_done_dir");

// change this for a different queue size
$nodes = 32;
$queue_size_per_node = 16;
$queue_timeout = 0;
$queue_size = $nodes * $queue_size_per_node;
$all_processed = false;

$iteration = -1;
$species_location_arr = array(
    array(
        'species_id' => 1,
        'location_id' => 1,
        'ducks' => false
    ),
    array(
        'species_id' => 1,
        'location_id' => 2,
        'ducks' => false
    ),
    array(
        'species_id' => 1,
        'location_id' => 3,
        'ducks' => false
    ),
    array(
        'species_id' => 2,
        'location_id' => 4,
        'ducks' => false
    ),
    array(
        'species_id' => 3,
        'location_id' => 4,
        'ducks' => false
    ),
    array(
        'species_id' => 4,
        'location_id' => 7,
        'ducks' => true
    ),
    array(
        'species_id' => 4,
        'location_id' => 9,
        'ducks' => true
    ),
    array(
        'species_id' => 5,
        'location_id' => 7,
        'ducks' => true
    ),
    array(
        'species_id' => 5,
        'location_id' => 9,
        'ducks' => true
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
        echo "\nWaiting to fill the queue for $queue_timeout more iterations...\n";
        --$queue_timeout;
    }

    echo "\n\n";
    echo "Determining the queue status for $nodes nodes...\n";

    $ids = array();
    $idcounts = array_fill(1, 32, 0);
    $donebynode = array_fill(1, 32, array());
    for ($i = 1; $i <= $nodes; ++$i) {
        echo "\tNode $i...\n";
        $queue_dir = "$hodor_queue_dir/$i";
        $done_dir = "$hodor_done_dir/$i";

        // make sure the queue exists
        if (!is_dir($queue_dir)) mkdir($queue_dir, 0775, true);
        if (!is_dir($done_dir)) mkdir($done_dir, 0775, true);

        // get the queued and done variables
        $queued = array_diff(scandir($queue_dir), array('..', '.'));
        $done   = array_diff(scandir($done_dir),  array('..', '.'));

        if ($queued === false) {
            echo "\t\tError reading queue directory: $queue_dir";
            continue;
        }
        if ($done === false) {
            echo "\t\tError reading queue directory: $done_dir";
            continue;
        }

        // build our ids (done or queued)
        foreach ($queued as &$q) {
            // get rid of ducks in the name
            $id = str_replace("ducks", "", explode('.', $q, 2)[0]);
            if (!$id) continue;
            $ids[] = $id;
            $idcounts[$i] += 1;
        }
        foreach ($done as &$d) {
            // get rid of ducks in the name
            $id = str_replace("ducks", "", explode('.', $d, 2)[0]);
            if (!$id) continue;
           
            $donebynode[$i][] = $id;

            // we need to check if it's already in the array because there
            // is the chance that between the queued and done SSH calls,
            // one file could be moved from queued to done
            if (!in_array($id, $ids)) {
                $ids[] = $id;
            }
        }
    }

    // fill the queue if we have slots (if we aren't timed out)
    $node = 1;
    for ($i = count($queued); $queue_timeout == 0 && $i < $queue_size; ++$i, ++$iteration) {
        // make sure we're on a node that needs more videos
        while ($node <= $nodes && $idcounts[$node] >= $queue_size_per_node) {
            $node += 1;
        }

        // make sure we haven't filled all nodes
        if ($node > $nodes) {
            echo "Filled up all the queues for all the nodes.\n";
            break;
        }

        $iteration = next_iteration($iteration, $species_location_arr);
        $firstiteration = $iteration;

        echo "Filling the queue with " . ($queue_size - count($queued)) . " videos\n";

        // loop through all our species / locations once until we get one
        $result = null;
        do {
            $species_id  = $species_location_arr[$iteration]['species_id'];
            $location_id = $species_location_arr[$iteration]['location_id'];
            $is_ducks    = $species_location_arr[$iteration]['ducks'];

            $notin = "";
            if (count($ids) > 0) {
                $notin = "AND id NOT IN (" . implode(',', $ids) . ")";
            }

            $query = "SELECT id, archive_filename, watermarked_filename FROM video_2 WHERE processing_status = 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id $notin LIMIT 1";

            $result = query_wildlife_video_db($query);
            if ($result && $result->num_rows == 1) {
                break;
            }

            $iteration = next_iteration($iteration, $species_location_arr);
        } while ($iteration != $firstiteration);

        // if we still don't have an result, we've done all the videos!
        if (!$result || $result->num_rows != 1) {
            echo "[WARNING] No more videos to convert!\n";

            if ($queue_timeout == 0) {
                $queue_timeout = 100; // timeout for 100 cycles
            }

            break;
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
        $arr = array();
        $retval = 1;
        $videoname = "$video_id";
        if ($is_ducks) {
            $videoname = "ducks$videoname";
        }

        $archive_filename = str_replace("share", "home", $archive_filename);
        $watermarked_filename = str_replace("share", "home", $row["watermarked_filename"]) . ".mp4";
        $path = "$hodor_queue_dir/$node/$videoname.txt";
        echo "\n[Node: $node] Queuing $video_id as $videoname.txt with\n\t$archive_filename\n\t$watermarked_filename\n";
        $fp = fopen($path, "w");
        if (!$fp) {
            echo "\tError opening file for writing: $path\n";
            continue;
        }
        fwrite($fp, "$archive_filename\n");
        fwrite($fp, "$watermarked_filename\n");
        fclose($fp);

        // ad our video_id to the end of the ids so it isn't picked up again
        $ids[] = $video_id;
        $idcounts[$node] += 1;

        // done
        echo "\tDone.\n";
    }

    // download the files
    for ($i = 1; $i <= $nodes; ++$i) {
        foreach ($donebynode[$i] as &$video) {
            // split the extension and the id
            $parts = pathinfo($video);
            $video_id = str_replace("ducks", "", $parts['filename']);
            $ext = "mp4";
            $path =  "$hodor_done_dir/$i/$video.txt";

            // get the id and extension
            echo "\n[NODE: $i] Confirming completion of $video_id => $video...\n";

            // figure out where in the database it is
            $result = query_wildlife_video_db("SELECT watermarked_filename FROM video_2 WHERE id=$video_id AND processing_status='UNWATERMARKED'");

            // if it's not in the database, just delete the file from the server
            if (!$result || $result->num_rows == 0) {
                echo "\t[ERROR] Unable to find id = $video_id\n";
                unlink($path);
                continue;
            }

            // make our directory
            $row = $result->fetch_assoc();
            $watermarked_filename = $row['watermarked_filename'] . ".$ext";
            if (!file_exists($watermarked_filename)) {
                echo "\tFile doesn't exist... $watermarked_filename.\n";
                unlink($path);
                continue;
            }

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

            echo "\tDeleting done file...\n";
            unlink($path);
            echo "\t\tDone.";
        }
    }
    
    // sleep for 1 minute
    sleep(60);
}

?>
