<?php

require_once("wildlife_db.php");

function events_match($e1, $e2, $buffer) {
    return $e1['event_id'] == $e2['event_id'] && abs($e1['start_time_s'] - $e2['start_time_s']) <= $buffer && abs($e1['end_time_s'] - $e2['end_time_s']) <= $buffer;
}

function time_overlaps($exp_start_time, $exp_end_time, $vs_start_time, $vs_duration) {
    //echo "exp start time: '$exp_start_time'\n";
    //echo "vs start time:  '$vs_start_time'\n";
    //echo "vs duration:    '$vs_duration'\n";

    $vs_start = new DateTime($vs_start_time);
    $vs_end = clone $vs_start;
    $vs_end->add(new DateInterval('PT' . $vs_duration . 'S'));

    $exp_start = new DateTime($exp_start_time);
    $exp_end = new DateTime($exp_end_time);

    /*
    echo "vs start: '" . $vs_start->format('Y-m-d H:i:s') . "'\n";
    echo "vs end: '" . $vs_end->format('Y-m-d H:i:s') . "'\n";

    echo "exp start: '" . $exp_start->format('Y-m-d H:i:s') . "'\n";
    echo "exp end: '" . $exp_end->format('Y-m-d H:i:s') . "'\n";
    */

    if ($vs_start < $exp_start) {
        if ($vs_end > $exp_start) return true;
        else return false;
    } else if ($exp_start < $vs_start) {
        if ($exp_end > $vs_start) return true;
        else return false;
    } else {
        return true;
    }
}


$event_ids = array(
                34, 36, 35, /* camera interaction - attack, observation, physical inspection */
                21, 17, 18, 22, /* chick behavior - submissive, eggs hatching, in video, foraging */
                38, 39, 40, /* error - too dark, video error, camera issue */
//                32, /* miscellaneous - unspecified */
                41, 42, 23, 4, 15, 13, /* parent behavior - on nest, off nest, adult-to-chick-feed, not in video, adult-to-adult feed, foraging */
                12, 9, 11, /* parent care - eggshell removal, brooding chicks, nest exchange */
//                2, 3, 1, /* self directed - scratch, shake, preen */
                37, 31, 30, 26 /* territorial - human, non-predator, predator, nest defense */
             );

$valids = array();
$invalids = array();
$inconclusives = array();
$expert_matches = array();
$totals = array();
$event_names = array();
$event_categories = array();
$match_count = array();
$mismatch_count = array();
$expert_match_count = array();
$expert_mismatch_count = array();
$observation_totals = array();

$expert_totals = array();
$expert_and_volunteer_totals = array();

$current = 0;
foreach ($event_ids as $event_id) {
    $ot_results = query_video_db("SELECT category, name FROM observation_types WHERE id = $event_id");
    $ot_row = $ot_results->fetch_assoc();
    $event_names[$event_id] = $ot_row['name'];
    $event_categories[$current] = $ot_row['category'];

    $totals[$event_id] = 0;
    $valids[$event_id] = 0;
    $invalids[$event_id] = 0;
    $inconclusives[$event_id] = 0;
    $expert_matches[$event_id] = 0;

    $match_count[$event_id] = array();
    $mismatch_count[$event_id] = array();
    for ($buffer = 5; $buffer <= 20; $buffer += 5) {
        $match_count[$event_id][$buffer] = 0;
        $mismatch_count[$event_id][$buffer] = 0;
        $expert_match_count[$event_id][$buffer] = 0;
        $expert_mismatch_count[$event_id][$buffer] = 0;
    }
    $observation_totals[$event_id] = 0;
    $current++;

    $expert_totals[$event_id] = 0;
    $expert_and_volunteer_totals[$event_id] = 0;
}

$current = 0;
foreach ($event_ids as $event_id) {
    $v_results = query_video_db("SELECT distinct(id) FROM video_2 AS v2 WHERE EXISTS(SELECT * FROM timed_observations AS tobs WHERE v2.id = tobs.video_id AND tobs.event_id = $event_id AND tobs.expert = 0)");

    while ($v_row = $v_results->fetch_assoc()) {
        $video_id = $v_row['id'];
//        echo $video_id . "\n";
        $totals[$event_id]++;

        $had_valid = false;
        $had_invalid = false;
        $had_inconclusive = false;
        $to_results = query_video_db("SELECT * FROM timed_observations WHERE expert = 0 AND video_id = $video_id AND event_id = $event_id");
        $to_rows = array();

        while ($to_row = $to_results->fetch_assoc()) {
            $status = $to_row['status'];
            if ($status == 'UNVALIDATED') $had_inconclusive = true;
            else if ($status == 'VALID') $had_valid = true;
            else if ($status = 'INVALID') $had_invalid = true;

            $to_rows[] = $to_row;
            $observation_totals[$event_id]++;
        }

        if ($had_inconclusive) $inconclusives[$event_id]++;
        if ($had_valid) $valids[$event_id]++;
        if ($had_invalid) $invalids[$event_id]++;

        for ($buffer = 5; $buffer <= 20; $buffer += 5) {
            $matches = array();
            for ($i = 0; $i < count($to_rows); $i++) {
                $matches[$i] = false;
            }

            for ($i = 0; $i < count($to_rows); $i++) {
                if ($matches[$i]) continue;

                for ($j = $i + 1; $j < count($to_rows); $j++) {
                    if (events_match($to_rows[$i], $to_rows[$j], $buffer)) {
                        $matches[$i] = true;
                        $matches[$j] = true;
                    }
                }
            }

            for ($i = 0; $i < count($matches); $i++) {
                if ($matches[$i]) $match_count[$event_id][$buffer]++;
                else $mismatch_count[$event_id][$buffer]++;
            }
         }

        $expert_results = query_video_db("SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id");
        if ($expert_results->num_rows == 0) {
            continue;
        }
        $expert_totals[$event_id]++;
        $expert_and_volunteer_totals[$event_id] += count($to_rows);

        $expert_rows = array();
        while ($expert_row = $expert_results->fetch_assoc()) {
            $expert_rows[] = $expert_row;
        }

        for ($buffer = 5; $buffer <= 20; $buffer += 5) {
            $matches = array();
            for ($i = 0; $i < count($to_rows); $i++) {
                $matches[$i] = false;
            }

            for ($i = 0; $i < count($to_rows); $i++) {
                for ($j = 0; $j < count($expert_rows); $j++) {
                    if (events_match($to_rows[$i], $expert_rows[$j], $buffer)) {
                        $matches[$i] = true;
                    }
                }
            }

            for ($i = 0; $i < count($matches); $i++) {
                if ($matches[$i]) $expert_match_count[$event_id][$buffer]++;
                else $expert_mismatch_count[$event_id][$buffer]++;
            }
         }


    }

    if ($current == 0 || ($current > 0 && $event_categories[$current - 1] != $event_categories[$current])) {
        print "\\hline\n";
        print str_pad($event_categories[$current], 50, " ", STR_PAD_RIGHT) . " & & & & & & & & & & \\\\\n";
    }

    print str_pad($event_names[$event_id], 50, " ", STR_PAD_RIGHT)
        . " & " . str_pad($totals[$event_id], 10, " ", STR_PAD_LEFT)
//        . " & " . str_pad($valids[$event_id], 10, " ", STR_PAD_LEFT)
//        . " & " . str_pad($invalids[$event_id], 10, " ", STR_PAD_LEFT)
//        . " & " . str_pad($inconclusives[$event_id], 10, " ", STR_PAD_LEFT)
        . " & " . str_pad($observation_totals[$event_id], 10, " ", STR_PAD_LEFT);

    for ($buffer = 5; $buffer <= 20; $buffer += 5) {
        print
              " & " . str_pad($match_count[$event_id][$buffer], 10, " ", STR_PAD_LEFT)
            . " & " . str_pad($mismatch_count[$event_id][$buffer], 10, " ", STR_PAD_LEFT);
    }
    print "\\\\\n";
    print "\\hline\n";

    $current++;
}

print "\n\nVS EXPERTS:\n";

$current = 0;
foreach ($event_ids as $event_id) {

    if ($current == 0 || ($current > 0 && $event_categories[$current - 1] != $event_categories[$current])) {
        print "\\hline\n";
        print str_pad($event_categories[$current], 50, " ", STR_PAD_RIGHT) . " & & & & & & & & & & \\\\\n";
        print "\\hline\n";
    }

    print str_pad($event_names[$event_id], 50, " ", STR_PAD_RIGHT)
        . " & " . str_pad($expert_totals[$event_id], 10, " ", STR_PAD_LEFT)
        . " & " . str_pad($expert_and_volunteer_totals[$event_id], 10, " ", STR_PAD_LEFT);

    for ($buffer = 5; $buffer <= 20; $buffer += 5) {
        print
              " & " . str_pad($expert_match_count[$event_id][$buffer], 10, " ", STR_PAD_LEFT)
            . " & " . str_pad($expert_mismatch_count[$event_id][$buffer], 10, " ", STR_PAD_LEFT);
    }
    print "\\\\\n";
    print "\\hline\n";

    $current++;

}

?>
