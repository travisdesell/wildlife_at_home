<?php

require_once("wildlife_db.php");

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


$v_results = query_video_db("SELECT distinct(id) FROM video_2 AS v2 WHERE EXISTS(SELECT * FROM timed_observations AS tobs WHERE v2.id = tobs.video_id AND tobs.expert = 0)");

$matches = 0;

$expert_misses['easy'] = 0;
$expert_misses['medium'] = 0;
$expert_misses['hard'] = 0;

$expert_same_time['easy'] = 0;
$expert_same_time['medium'] = 0;
$expert_same_time['hard'] = 0;

$expert_matches['easy'] = 0;
$expert_matches['medium'] = 0;
$expert_matches['hard'] = 0;

$event_same_time = array();
$event_misses = array();
$event_matches = array();

for ($i = 0; $i < 50; $i++) {
    $event_same_time[$i] = 0;
    $event_misses[$i] = 0;
    $event_matches[$i] = 0;
}

$buffer = 10;

$total_observations = 0;
while ($v_row = $v_results->fetch_assoc()) {
    $video_id = $v_row['id'];

    $expert_results = query_video_db("SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id");
    if ($expert_results->num_rows == 0) {
        continue;
    }
    //get count of videos with both expert and volunteer observations
    $matches++;

    $volunteer_results = query_video_db("SELECT * FROM timed_observations WHERE expert = 0 AND video_id = $video_id");
    while ($volunteer_row = $volunteer_results->fetch_assoc()) {
        $start_time_s = $volunteer_row['start_time_s'];
        $end_time_s = $volunteer_row['end_time_s'];
        $event_id = $volunteer_row['event_id'];

        if (!in_array($event_id, $event_ids)) continue;
        $total_observations++;


        $exact_match = query_video_db("SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND ABS(start_time_s - $start_time_s) <= $buffer AND ABS(end_time_s - $end_time_s) <= $buffer AND event_id = $event_id");
        if ($exact_match->num_rows > 0) {
            $event_matches[$event_id]++;

            //had an exact expert match
            $diff_results = query_video_db("SELECT difficulty FROM watched_videos WHERE video_id = $video_id");

            while ($diff_row = $diff_results->fetch_assoc()) {
                if ($diff_row['difficulty'] == 'easy') $expert_matches['easy']++;
                if ($diff_row['difficulty'] == 'medium') $expert_matches['medium']++;
                if ($diff_row['difficulty'] == 'hard') $expert_matches['hard']++;
            }
        } else {
            $same_times = query_video_db("SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND ABS(start_time_s - $start_time_s) <= $buffer AND ABS(end_time_s - $end_time_s) <= $buffer");
            if ($same_times->num_rows > 0) {
                //had an expert match with the same times and different event ids
                $diff_results = query_video_db("SELECT difficulty FROM watched_videos WHERE video_id = $video_id");

                $event_same_time[$event_id]++;

                while ($diff_row = $diff_results->fetch_assoc()) {
                    if ($diff_row['difficulty'] == 'easy') $expert_same_time['easy']++;
                    if ($diff_row['difficulty'] == 'medium') $expert_same_time['medium']++;
                    if ($diff_row['difficulty'] == 'hard') $expert_same_time['hard']++;
                }
            } else {
                //was a complete miss
                $diff_results = query_video_db("SELECT difficulty FROM watched_videos WHERE video_id = $video_id");

                $event_misses[$event_id]++;

                while ($diff_row = $diff_results->fetch_assoc()) {
                    if ($diff_row['difficulty'] == 'easy') $expert_misses['easy']++;
                    if ($diff_row['difficulty'] == 'medium') $expert_misses['medium']++;
                    if ($diff_row['difficulty'] == 'hard') $expert_misses['hard']++;
                }
            }
        }
    }

}

print "videos with expert and volunteer observations: $matches\n";

/*
$expert_same_time['easy'] -= $expert_matches['easy'];
$expert_same_time['medium'] -= $expert_matches['medium'];
$expert_same_time['hard'] -= $expert_matches['hard'];
 */

$totals['easy'] = $expert_misses['easy'] + $expert_same_time['easy'] + $expert_matches['easy'];
$totals['medium'] = $expert_misses['medium'] + $expert_same_time['medium'] + $expert_matches['medium'];
$totals['hard'] = $expert_misses['hard'] + $expert_same_time['hard'] + $expert_matches['hard'];

print "total volunteer observations: $total_observations\n";
print "expert_misses['easy']:        " . str_pad($expert_misses['easy'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_misses['easy'] / $totals['easy'], 2) . ")\n";
print "expert_misses['medium']:      " . str_pad($expert_misses['medium'], 10, " ", STR_PAD_LEFT)   . "  (" . number_format($expert_misses['medium'] / $totals['medium'], 2) . ")\n";
print "expert_misses['hard']:        " . str_pad($expert_misses['hard'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_misses['hard'] / $totals['hard'], 2) . ")\n";

print "expert_same_time['easy']:     " . str_pad($expert_same_time['easy'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_same_time['easy'] / $totals['easy'], 2) . ")\n";
print "expert_same_time['medium']:   " . str_pad($expert_same_time['medium'], 10, " ", STR_PAD_LEFT)   . "  (" . number_format($expert_same_time['medium'] / $totals['medium'], 2) . ")\n";
print "expert_same_time['hard']:     " . str_pad($expert_same_time['hard'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_same_time['hard'] / $totals['hard'], 2) . ")\n";

print "expert_matches['easy']:       " . str_pad($expert_matches['easy'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_matches['easy'] / $totals['easy'], 2) . ")\n";
print "expert_matches['medium']:     " . str_pad($expert_matches['medium'], 10, " ", STR_PAD_LEFT)   . "  (" . number_format($expert_matches['medium'] / $totals['medium'], 2) . ")\n";
print "expert_matches['hard']:       " . str_pad($expert_matches['hard'], 10, " ", STR_PAD_LEFT)     . "  (" . number_format($expert_matches['hard'] / $totals['hard'], 2) . ")\n";

$by_event_info = array();
for ($i = 0; $i < 50; $i++) {
    if (!in_array($i, $event_ids)) continue;

    $res = query_video_db("SELECT category, name FROM observation_types WHERE id = $i");
    $row = $res->fetch_assoc();
    $event_name = $row['category'] . " - " . $row['name'];


    $total = $event_misses[$i] + $event_same_time[$i] + $event_matches[$i];
    $by_event_info[] = array( 
        "id" => $i, 
        "event_name" => $event_name, 
        "misses" => $event_misses[$i], 
        "miss_perc" => ($event_misses[$i] / $total),
        "same_times" => $event_same_time[$i], 
        "same_time_perc" => ($event_same_time[$i] / $total),
        "matches" => $event_matches[$i],
        "match_perc" => ($event_matches[$i] / $total)
    );
}

print "event & full misses & event type mismatch & matches \\\\\n";
print "\\hline\n";
foreach ($by_event_info as $e) {
    if ($e['misses'] + $e['same_times'] + $e['matches'] < 10) continue;

    print "\\hline\n";
    print str_pad($e['event_name'], 40, " ", STR_PAD_RIGHT) . " & "
        . $e['misses'] . " (" . number_format($e['miss_perc'],2) . ") & "
        . $e['same_times']  . " (" . number_format($e['same_time_perc'],2) . ") & " 
        . $e['matches']     . " (" . number_format($e['match_perc'],2) . ") \\\\\n";
}
print "\\hline\n";

?>
