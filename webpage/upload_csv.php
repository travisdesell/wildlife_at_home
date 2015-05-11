<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

// Set Column Numbers
/*
$input_file = "13March2015_ForKyle_PaulData.csv";
$file_name_col = 5;
$presence_col = 6;
$on_off_col = 7;
$behavior_col = 9;
$start_time_col = 11;
$end_time_col = 12;
$comments_col = 14;
$user_id_col = 15;
*/

$input_file = "13March2015_ForKyle_NickData.csv";
$file_name_col = 5; $presence_col = 6;
$on_off_col = 7;
$behavior_col = 9;
$start_time_col = 11;
$end_time_col = 12;
$comments_col = 17;
$user_id_col = 22;


function getVideoID($video_name) {
    $query = "SELECT id FROM video_2 WHERE watermarked_filename LIKE '%$video_name'";
    $result = query_wildlife_video_db($query);
    $id = NULL;
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
    }
    if ($id == NULL) {
        echo "Couldn't get video ID with name of '$video_name'";
    }
    return $id;
}

function getSpeciesID($video_id) {
    $query = "SELECT species_id FROM video_2 WHERE id = $video_id";
    $result = query_wildlife_video_db($query);
    $id = NULL;
    while ($row = $result->fetch_assoc()) {
        $id = $row['species_id']; }
    if ($id == NULL) {
        echo "Couldn't get species ID for video with ID of '$video_id'";
    }
    return $id;
}

function getLocationID($video_id) {
    $query = "SELECT location_id FROM video_2 WHERE id = $video_id";
    $result = query_wildlife_video_db($query);
    $id = NULL;
    while ($row = $result->fetch_assoc()) {
        $id = $row['location_id'];
    }
    if ($id == NULL) {
        echo "Couldn't get location ID for video with ID of '$video_id'";
    }
    return $id;
}

function getStartTime($video_id) {
    $query = "SELECT start_time FROM video_2 WHERE id = $video_id";
    $result = query_wildlife_video_db($query);
    $time = NULL;
    while ($row = $result->fetch_assoc()) {
        $time = strtotime($row['start_time']);
    }
    if ($time == NULL) {
        echo "Couldn't get start time for video with ID of '$video_id'";
    }
    return $time;
}

function getEndTime($video_id) {
    $query = "SELECT start_time, duration_s FROM video_2 WHERE id = $video_id";
    $result = query_wildlife_video_db($query);
    $time = NULL;
    while ($row = $result->fetch_assoc()) {
        $time = strtotime($row['start_time']) + $row['duration_s'];
    }
    if ($time == NULL) {
        echo "Couldn't get end time for video with ID of '$video_id'";
    }
    return $time;
}

function insertObservation($event_id, $user_id, $start_time, $end_time, $comments, $video_name, $expert) {
    if ($event_id == NULL) {
        echo "event_id is NULL";
        return FALSE;
    }
    if ($user_id == NULL) {
        echo "user_id is NULL";
        return FALSE;
    }
    if ($start_time == NULL) {
        echo "start_time is NULL";
        return FALSE;
    }
    if ($end_time == NULL) {
        echo "end_time is NULL";
        return FALSE;
    }
    if ($comments == NULL) {
        $comments = "NULL";
    }
    if ($video_name == NULL) {
        echo "video_name is NULL";
        return FALSE;
    }
    if ($expert == NULL) {
        echo "expert is NULL";
        return FALSE;
    }

    $video_id = getVideoID($video_name);
    if ($video_id == NULL) {
        return FALSE;
    }
    $species_id = getSpeciesID($video_id);
    if ($species_id == NULL) {
        return FALSE;
    }
    $location_id = getLocationID($video_id);
    if ($location_id == NULL) {
        return FALSE;
    }

    $video_start_time = getStartTime($video_id);
    if ($video_start_time == NULL) {
        return FALSE;
    }

    $video_end_time = getEndTime($video_id);
    if ($video_end_time == NULL) {
        return FALSE;
    }

    // Check Event Bounds
    if ($end_time < $start_time) {
        $end_time = date(DATE_RFC850, $end_time);
        $start_time = date(DATE_RFC850, $start_time);
        echo "End_time ('$end_time') less than Start_time ('$start_time')";
        return FALSE;
    }
    if ($start_time < $video_start_time) {
        $start_time = $video_start_time;
    }
    if ($end_time > $video_end_time) {
        $end_time = $video_end_time;
    }

    $start_time_s = $start_time - $video_start_time;
    $end_time_s = $end_time - $video_start_time;
    $completed = 1;
    $auto_generated = 1; // Should this be true?

    $query = "INSERT INTO timed_observations (event_id, user_id, start_time, end_time, comments, video_id, species_id, location_id, expert, start_time_s, end_time_s, completed, auto_generated) VALUES ($event_id, $user_id, $start_time, $end_time, '$comments', $video_id, $species_id, $location_id, $expert, $start_time_s, $end_time_s, $completed, $auto_generated)";
    //echo "$query\n";
    /*
    $result = query_wildlife_video_db($query);
    if($result == TRUE) {
        return TRUE;
    }
    return FALSE;
     */
    return TRUE;
}

$row = 1;
if (($handle = fopen("$input_file", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $num_cols = count($data);
        $row++;
        //echo "$num_cols columns in row $row:\n";
        /*
        for ($col = 0; $col < $num_cols; $col++) {
            echo $data[$col] . ", ";
        }
        echo "\n";
        */

        $video_names = explode(';', $data[$file_name_col]);

        // Determine event type;
        $presence = trim(strtoupper($data[$presence_col]));
        if ($presence == "PRESENT") {
            $on_off = trim(strtoupper($data[$on_off_col]));
            if ($on_off == "ON") {
                $event_id = 41;
            } else if ($on_off == "OFF") {
                $event_id = 42;
            } else if ($on_off == "NA") {
                $behavior = trim(strtoupper($data[$behavior_col]));
                if ($behavior == "NEST EXCHANGE") {
                    $event_id = 11;
                } else {
                    echo " Unknown Behavior: '$behavior' (Error in row '$row')\n";
                }
            } else {
                echo " Unknown On Off Value: '$on_off' (Error in row '$row')\n";
            }
        } elseif ($presence == "ABSENT") {
            $event_id = 4;
        } else {
            echo " Unknown Presence: '$presence' (Error in row '$row')\n";
            //exit("Unknown value in presence column.\n");
        }

        $user_id = trim($data[$user_id_col]);
        $comments = trim($data[$comments_col]);
        $expert = 1;

        foreach ($video_names as $video_name) {
            $start_time = strtotime($data[$start_time_col]);
            $end_time = strtotime($data[$end_time_col]);

            $success = insertObservation($event_id, $user_id, $start_time, $end_time, $comments, $video_name, $expert);
            if (!$success) {
                echo " (Error in row '$row')\n";
                //exit("Insert observation error\n");
            }

            // TODO Add a second observation if there is a behavior and bird is on nest
        }
    }
    fclose($handle);
}
?>
