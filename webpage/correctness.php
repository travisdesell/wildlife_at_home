<?php

function getBufferCorrectness($obs_id, $expert_id, $buffer) {
    $event_query = "SELECT video_id, event_id, TO_SECONDS(start_time) AS start_time, TO_SECONDS(end_time) AS end_time FROM timed_observations AS obs WHERE obs.id = $obs_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(start_time) < TO_SECONDS(end_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.start_time) < TO_SECONDS(i.end_time))";
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
        $match_query = "SELECT * FROM timed_observations WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND to_seconds(start_time) BETWEEN $start_sec_top AND $start_sec_bot AND TO_SECONDS(end_time) BETWEEN $end_sec_top AND $end_sec_bot";
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
    // Error on database retrieval
    assert(false);
}

function getEuclideanCorrectness($obs_id, $expert_id, $threshold = 95) {
    $event_query = "SELECT obs.video_id, obs.event_id, vid.duration_s, (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.id = $obs_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.start_time) < TO_SECONDS(i.end_time))";
    $event_result = query_wildlife_video_db($event_query);

    $num_match_events = 0;

    while ($event_row = $event_result->fetch_assoc()) {
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $video_duration = $event_row['duration_s'];
        $start_time = $event_row['start_time'];
        $end_time = $event_row['end_time'];
        $match_query = "SELECT (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time)";
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

function getSegmentedEuclideanCorrectness($obs_id, $expert_id, $threshold) {
    $event_query = "SELECT obs.video_id, obs.event_id, vid.duration_s, (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.id = $obs_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.user_id = $expert_id AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.start_time) < TO_SECONDS(i.end_time))";
    $event_result = query_wildlife_video_db($event_query);

    $num_match_events = 0;

    while ($event_row = $event_result->fetch_assoc()) {
        $video_id = $event_row['video_id'];
        $event_id = $event_row['event_id'];
        $video_duration = $event_row['duration_s'];
        $start_time = $event_row['start_time'];
        $end_time = $event_row['end_time'];
        $match_query = "SELECT (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE user_id = $expert_id AND video_id = $video_id AND event_id = $event_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time)";
        $match_result = query_wildlife_video_db($match_query);
        $start_times = array();
        $end_times = array();

        while ($row = $match_result->fetch_assoc()) {
            $start_times[] = $row['start_time'];
            $end_times[] = $row['end_time'];
        }

        $min_dist = -1;
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

function getEventWeight($obs_id) {
    $weight_query = "SELECT 1/(SELECT COUNT(*) FROM timed_observations AS obs WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id) AS weight FROM timed_observations AS t WHERE t.id = $obs_id";
    $weight_result = query_wildlife_video_db($weight_query);
        while ($weight_row = $weight_result->fetch_assoc()) {
            return $weight_row['weight'];
        }
    return 0;
}

function getEventScaledWeight($obs_id, $scale_factor) {
    $weight_query = "SELECT (v.duration_s/(TO_SECONDS(t.end_time) - TO_SECONDS(t.start_time) + v.duration_s*$scale_factor))/(SELECT SUM(vid.duration_s/(TO_SECONDS(obs.end_time) - TO_SECONDS(obs.start_time) + vid.duration_s*$scale_factor)) FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id GROUP BY obs.user_id) AS weight FROM timed_observations AS t JOIN video_2 AS v ON v.id = t.video_id WHERE t.id = $obs_id";
    $weight_result = query_wildlife_video_db($weight_query);
        while ($weight_row = $weight_result->fetch_assoc()) {
            return $weight_row['weight'];
        }
    return 0;
}

?>
