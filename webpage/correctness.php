<?php

function getExpert($video_id) {
    $query = "SELECT user_id FROM timed_observations WHERE video_id = $video_id AND expert = 1 LIMIT 1";
    $result = query_wildlife_video_db($query);
    $user_id = -1;
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
    }
    return $user_id;
}

function getUsers($video_id) {
    $query = "SELECT user_id FROM timed_observations WHERE video_id = $video_id AND expert = 0";
    $result = query_wildlife_video_db($query);
    $user_ids = array();
    while ($row = $result->fetch_assoc()) {
        $user_ids[] = $row['user_id'];
    }
    return $user_ids;
}

// Function to calculate square of value - mean
function stdev_square($x, $mean) { return pow($x - $mean,2); }

// Function to calculate standard deviation (uses stdev_square)    
function stdev($array, $mean) {
    // square root of sum of squares devided by N-1
    return sqrt(array_sum(array_map("stdev_square", $array, array_fill(0,count($array), $mean))) / (count($array)-1));
}

/* Queries the user observation table */
function getBufferCorrectness($obs_id, $expert_id, $buffer) {
    $event_query = "SELECT video_id, event_id, start_time_s AS start_time, end_time_s AS end_time FROM timed_observations AS obs WHERE obs.id = $obs_id AND start_time_s >= 0 AND start_time_s <= end_time_s AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND i.start_time_s >= 0 AND i.start_time_s <= i.end_time_s)";
    $event_result = query_wildlife_video_db($event_query);

    $num_match_events = 0;

    // Get event and find expert match
    while ($event_row = $event_result->fetch_assoc()) {
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $start_sec = $event_row['start_time'];
        $end_sec = $event_row['end_time'];

        $start_sec_top = $start_sec - $buffer;
        $start_sec_bot = $start_sec + $buffer;
        $end_sec_top = $end_sec - $buffer;
        $end_sec_bot = $end_sec + $buffer;
        $match_query = "SELECT * FROM timed_observations WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND start_time_s BETWEEN $start_sec_top AND $start_sec_bot AND end_time_s BETWEEN $end_sec_top AND $end_sec_bot";
        $match_result = query_wildlife_video_db($match_query);
        $num_matches = $match_result->num_rows;

        if ($num_matches >= 1) {
            // User and Expert (True Positive)
            return array(1, true);
        } else {
            // User and No Expert (False Positive)
            return array(0, false);
        }
    }
    // Error on database retrieval (Probably undefined expert id)
    assert(false);
}

/* Queries the computed events table */
function getBufferAccuracy($obs_id, $algorithm_id, $buffer) {
    $event_query = "SELECT video_id, event_id, start_time_s AS start_time, end_time_s AS end_time FROM timed_observations WHERE id = $obs_id";
    $event_result = query_wildlife_video_db($event_query);

    // Get event and find match
    while ($event_row = $event_result->fetch_assoc()) {
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $start_sec = $event_row['start_time'];
        $end_sec = $event_row['end_time'];

        $start_sec_top = $start_sec - $buffer;
        $start_sec_bot = $start_sec + $buffer;
        $end_sec_top = $end_sec - $buffer;
        $end_sec_bot = $end_sec + $buffer;

        $front_match_query = "SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id WHERE comp.algorithm_id = $algorithm_id AND video_id = $video_id AND comp.version_id = alg.beta_version_id AND (start_time_s BETWEEN $start_sec_top AND $start_sec_bot OR end_time_s BETWEEN $start_sec_top AND $start_sec_bot)";
        $front_match_result = query_wildlife_video_db($front_match_query);
        $num_front_matches = $front_match_result->num_rows;

        $back_match_query = "SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id WHERE comp.algorithm_id = $algorithm_id AND video_id = $video_id AND comp.version_id = alg.beta_version_id AND (start_time_s BETWEEN $end_sec_top AND $end_sec_bot OR end_time_s BETWEEN $end_sec_top AND $end_sec_bot)";
        $back_match_result = query_wildlife_video_db($back_match_query);
        $num_back_matches = $back_match_result->num_rows;

        $start_match = FALSE;
        $end_match = FALSE;
        if ($num_front_matches >= 1) {
            $start_match = TRUE;
        }
        if ($num_back_matches >= 1) {
            $end_match = TRUE;
        }

        return array($start_match, $end_match);
    }
    assert(false);
}

/* Queries the computed events table */
function getFalsePositives($video_id, $user_id, $algorithm_ids, $buffer) {
    $not_in_vid_id = 4;
    if (is_array($algorithm_ids)) {
        $not_in_vid_query = "SELECT start_time_s AS start_time, end_time_s AS end_time FROM timed_observations AS obs WHERE obs.video_id = $video_id AND obs.user_id = $user_id AND obs.event_id = $not_in_vid_id AND start_time_s >= 0 AND start_time_s <= end_time_s AND EXISTS (SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id WHERE obs.video_id = comp.video_id AND comp.version_id = alg.beta_version_id AND comp.start_time_s >= 0 AND comp.start_time_s <= comp.end_time_s AND (comp.algorithm_id = $algorithm_ids[0]";
        for ($i = 1; $i < count($algorithm_ids); $i++) {
            $not_in_vid_query += " OR comp.algorithm_id = $algorithm_ids[$i]";
        }
        $not_in_vid_query += ")";
    } else {
        $not_in_vid_query = "SELECT start_time_s AS start_time, end_time_s AS end_time FROM timed_observations AS obs WHERE obs.video_id = $video_id AND obs.user_id = $user_id AND obs.event_id = $not_in_vid_id AND start_time_s >= 0 AND start_time_s <= end_time_s AND EXISTS (SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id WHERE obs.video_id = comp.video_id AND comp.version_id = alg.beta_version_id AND comp.start_time_s >= 0 AND comp.start_time_s <= comp.end_time_s AND comp.algorithm_id = $algorithm_ids)";
    }
    $result = query_wildlife_video_db($not_in_vid_query);

    // Get event and find match
    $num_matches = 0;
    $total_seconds = 0;
    while ($row = $result->fetch_assoc()) {
        $start_sec = $row['start_time'] + $buffer;
        $end_sec = $row['end_time'] - $buffer;
        $total_seconds += $end_sec - $start_sec;

        $match_query = "SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id WHERE comp.algorithm_id = $algorithm_ids AND video_id = $video_id AND comp.version_id = alg.beta_version_id AND start_time_s >= $start_sec AND end_time_s <= $end_sec";
        $match_result = query_wildlife_video_db($match_query);
        $num_matches += $match_result->num_rows;
    }
    return array($num_matches, $total_seconds);
}

/* Queries the user observation table */
function getEuclideanCorrectness($obs_id, $expert_id, $threshold = 95) {
    $event_query = "SELECT obs.video_id, obs.event_id, vid.duration_s, obs.start_time_s AS start_time, obs.end_time_s AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.id = $obs_id AND obs.start_time_s >= 0 AND obs.start_time_s <= obs.end_time_s AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND i.start_time_s >= 0 AND i.start_time_s <= i.end_time_s)";
    $event_result = query_wildlife_video_db($event_query);

    $num_match_events = 0;

    while ($event_row = $event_result->fetch_assoc()) {
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $video_duration = $event_row['duration_s'];
        $start_time = $event_row['start_time'];
        $end_time = $event_row['end_time'];
        $match_query = "SELECT obs.start_time_s AS start_time, obs.end_time_s AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND obs.start_time_s >= 0 AND obs.start_time_s <= obs.end_time_s";
        $match_result = query_wildlife_video_db($match_query);

        $min_dist = -1;
        $max_dist = ($video_duration*1.41421356237); // Divide by hypotenuse of a square
        while ($row = $match_result->fetch_assoc()) {
            $temp_start = $row['start_time'];
            $temp_end = $row['end_time'];
            $dist = sqrt((($temp_start - $start_time)*($temp_start - $start_time)) + (($temp_end - $end_time)*($temp_end - $end_time)));
            if ($dist <= $max_dist && ($min_dist == -1 || $dist < $min_dist)) {
                $min_dist = $dist;
            }
        }

        if ($min_dist == -1) {
            // User and No Expert (False Positive)
            return array(0, false);
        } else {
            // User and Expert (True Positive)
            $correctness = 1-($min_dist/$max_dist);
            if ($correctness * 100 >= $threshold) {
                return array($correctness, true);
            } else {
                return array($correctness, false);
            }
        }
    }
    // Error on database retrieval
    assert(false);
}

/* Queries the user observation table */
function getSegmentedEuclideanCorrectness($obs_id, $expert_id, $threshold = 95, $recurse = true) {
    $event_query = "SELECT obs.user_id, obs.video_id, obs.event_id, vid.duration_s, obs.start_time_s AS start_time, obs.end_time_s AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.id = $obs_id AND obs.start_time_s >= 0 AND obs.start_time_s <= obs.end_time_s AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND i.start_time_s >= 0 AND i.start_time_s <= i.end_time_s)";
    $event_result = query_wildlife_video_db($event_query);

    $num_match_events = 0;

    // Check all user events for a combined expert match
    while ($event_row = $event_result->fetch_assoc()) {
        $user_id = $event_row['user_id'];
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $video_duration = $event_row['duration_s'];
        $start_time = $event_row['start_time'];
        $end_time = $event_row['end_time'];
        $match_query = "SELECT obs.start_time_s AS start_time, obs.end_time_s AS end_time, obs.id FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND obs.start_time_s >= 0 AND obs.start_time_s <= obs.end_time_s";
        $match_result = query_wildlife_video_db($match_query);
        $start_times = array();
        $end_times = array();

        $min_dist = -1;
        while ($row = $match_result->fetch_assoc()) {
            $start_times[] = $row['start_time'];
            $end_times[] = $row['end_time'];
            if ($recurse) {
                $min_dist = getSegmentedEuclideanCorrectness($row['id'], $user_id, $threshold, false);
            }
        }

        $max_dist = ($video_duration*1.41421356237); // Divide by hypotenuse of a square

        foreach ($start_times as $temp_start) {
            foreach ($end_times as $temp_end) {
                $dist = sqrt((($temp_start - $start_time)*($temp_start - $start_time)) + (($temp_end - $end_time)*($temp_end - $end_time)));
                if ($min_dist == -1 || $dist < $min_dist) {
                    $min_dist = $dist;
                }
            }
        }

        if ($min_dist == -1) {
            // User and No Expert (False Positive)
            return array(0, false);
        } else {
            // User and Expert (True Positive)
            $correctness = 1-($min_dist/$max_dist);
            if ($correctness * 100 >= $threshold) {
                return array($correctness, true);
            } else {
                return array($correctness, false);
            }
        }
    }
    // Error on database retrieval
    assert(false);
}

/* Queries the user observation table */
function getEventWeight($obs_id, $expert_id) {
    $weight_query = "SELECT (SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = $expert_id) AS expert_count, (SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id) AS user_count FROM timed_observations AS t WHERE t.id = $obs_id";
    $weight_result = query_wildlife_video_db($weight_query);
    while ($weight_row = $weight_result->fetch_assoc()) {
        $user_count = $weight_row['user_count'];
        $expert_count = $weight_row['expert_count'];
        $max_count = max($user_count, $expert_count);
        return 1/$user_count * ($user_count / $max_count);
    }
    return 0;
}

/* Queries the table computed events table */
function getComputedEventWeight($comp_id, $expert_id) {
    $weight_query = "SELECT (SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = $expert_id AND obs.start_time_s >= 0 AND obs.start_time_s <= obs.end_time_s) AS expert_count, (SELECT COUNT(*) FROM computed_events AS comp WHERE comp.video_id = t.video_id AND comp.algorithm_id = t.algorithm_id AND comp.version_id = t.version_id) AS user_count FROM computed_events AS t WHERE t.id = $comp_id";
    $weight_result = query_wildlife_video_db($weight_query);
    while ($weight_row = $weight_result->fetch_assoc()) {
        $user_count = $weight_row['user_count'];
        $expert_count = $weight_row['expert_count'] * 2;
        $max_count = max($user_count, $expert_count);
        #return 1/$user_count * ($user_count / $max_count);
        return 1/$expert_count;
    }
    return 0;
}

/* Queries the user observation table */
function getEventScaledWeight($obs_id, $expert_id, $scale_factor) {
    $user_count = 0;
    $expert_count = 0;
    $max_count = 0;

    $count_query = "SELECT (SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = $expert_id) AS expert_count, (SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id) AS user_count FROM timed_observations AS t WHERE t.id = $obs_id";
    $count_result= query_wildlife_video_db($count_query);
    while ($count_row = $count_result->fetch_assoc()) {
        $user_count = $count_row['user_count'];
        $expert_count = $count_row['expert_count'];
        $max_count = max($user_count, $expert_count);
    }

    $weight_query = "SELECT (v.duration_s/(t.end_time_s - t.start_time_s + v.duration_s*$scale_factor))/(SELECT SUM(vid.duration_s/(obs.end_time_s - obs.start_time_s + vid.duration_s*$scale_factor)) FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id GROUP BY obs.user_id) AS weight FROM timed_observations AS t JOIN video_2 AS v ON v.id = t.video_id WHERE t.id = $obs_id";
    $weight_result = query_wildlife_video_db($weight_query);
    while ($weight_row = $weight_result->fetch_assoc()) {
        return $weight_row['weight'] * ($user_count / $max_count);
    }
    return 0;
}

?>
