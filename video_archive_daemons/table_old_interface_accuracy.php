<?php

require_once("wildlife_db.php");

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


$types = array("bird_leave", "bird_return", "bird_presence", "bird_absence", "predator_presence", "nest_defense", "nest_success", "chick_presence", "too_dark", "corrupt");

$has_pos = array();
$has_neg = array();
$has_inc = array();

$has_event          = array();
$has_inconclusive   = array();
$conflicts          = array();
$was_valid          = array();

$total                  = array();
$present_expert         = array();
$present_no_expert      = array();
$quorum_expert          = array();
$quorum_no_expert       = array();
$no_quorum_expert       = array();
$no_quorum_no_expert    = array();
$no_present_expert      = array();
$no_present_no_expert   = array();

$total[""] = 0;
foreach ($types as $type) {
    $has_event[$type] = 0;
    $has_inconclusive[$type] = 0;
    $conflicts[$type] = 0;
    $was_valid[$type] = 0;

    $total[$type] = 0;
    $present_expert[$type] = 0;
    $present_no_expert[$type] = 0;
    $quorum_expert[$type] = 0;
    $quorum_no_expert[$type] = 0;
    $no_quorum_expert[$type] = 0;
    $no_quorum_no_expert[$type] = 0;
    $no_present_expert[$type] = 0;
    $no_present_no_expert[$type] = 0;
}

$had_expert = 0;
$no_expert = 0;

$vs_results = query_video_db("SELECT id, video_id, start_time, duration_s FROM video_segment_2 WHERE crowd_status = 'VALIDATED' OR crowd_status = 'NO_CONSENSUS'"); 

while ($vs_row = $vs_results->fetch_assoc()) {
    echo $vs_row['id'] . "\n";
    $vs_start_time = $vs_row['start_time'];
    $vs_duration = $vs_row['duration_s'];

    $obs_results = query_video_db("SELECT * FROM observations WHERE video_segment_id = " . $vs_row['id']);

    foreach ($types as $type) {
        $has_pos[$type] = 0;
        $has_neg[$type] = 0;
        $has_inc[$type] = 0;
    }

    while($obs_row = $obs_results->fetch_assoc()) {
        foreach ($types as $type) {
            if ($type != "corrupt" && $type != "too_dark") {
                if (intval($obs_row[$type]) <  0)   $has_neg[$type]++;
                if (intval($obs_row[$type]) == 0)   $has_inc[$type]++;
                if (intval($obs_row[$type]) >  0)   $has_pos[$type]++;
            } else {
                if (intval($obs_row[$type]) == 0)   $has_neg[$type]++;
                if (intval($obs_row[$type]) >  0)   $has_pos[$type]++;
            }
        }
    }

    foreach ($types as $type) {
        if ($has_pos[$type] > 0) $has_event[$type]++;
        if ($has_inc[$type] > 0) $has_inconclusive[$type]++;
        if ($has_pos[$type] > 0 && $has_neg[$type] > 0) $conflicts[$type]++;
        if ($has_pos[$type] > 0 && $has_pos[$type] > $has_neg[$type]) $was_valid[$type]++;
    }

    //need to determine:
    //  event present and expert found event
    //  event present and expert did not find event
    //  event valid and expert found event
    //  event valid and expert did not find event
    //  event not valid and expert found event
    //  event not valid and expert did not find found event
    //  event not present and expert found event
    //  event not present and expert did not find found event


    $exp_results = query_video_db("SELECT event_id, start_time, end_time FROM timed_observations WHERE video_id = " . $vs_row['video_id'] . " AND expert = 1");

    if ($exp_results->num_rows == 0) {
        $no_expert++;
    } else {
        $had_expert++;

        while ($exp_row = $exp_results->fetch_assoc()) {
            $event_id = $exp_row['event_id'];
            $exp_start_time = $exp_row['start_time'];
            $exp_end_time = $exp_row['end_time'];

            $type = "";
            if ($event_id == 41 /*on nest*/) {
                $type  = "bird_presence";
            } else if ($event_id == 42 /*off nest*/) {
                $type  = "bird_leave";
            } else if ($event_id == 4 /*not in video*/) {
                $type  = "bird_absence";
            } else if ($event_id == 37 /*human*/ || $event_id == 31 /*non-predator animal*/ || $event_id == 30 /*predator*/) {
                $type  = "predator_presence";
            } else if ($event_id == 26 /*nest defense*/) {
                $type  = "nest_defense";
            } else if ($event_id == 18 /*chicks in video*/) {
                $type  = "chick_presence";
            }
            $total[$type]++;
            if ($type == "") continue;

            $user_present = $has_pos[$type];
            $user_quorum = $has_pos[$type] > 0 && $has_pos[$type] > $has_neg[$type];
            $expert_present = time_overlaps($exp_start_time, $exp_end_time, $vs_start_time, $vs_duration);

            if ($user_present) {
                if ($expert_present) {
                    $present_expert[$type]++;
                } else {
                    $present_no_expert[$type]++;
                }
            } else {
                if ($expert_present) {
                    $no_present_expert[$type]++;
                } else {
                    $no_present_no_expert[$type]++;
                }
            }

            if ($user_quorum) {
                if ($expert_present) {
                    $quorum_expert[$type]++;
                } else {
                    $quorum_no_expert[$type]++;
                }
            } else {
                if ($expert_present) {
                    $no_quorum_expert[$type]++;
                } else {
                    $no_quorum_no_expert[$type]++;
                }
            }
        }
    }

}


echo "video segments with    expert observation: $had_expert\n";
echo "video segments without expert observation: $no_expert\n\n\n";

echo "PRESENCE:\n";
echo "\\begin{tabular}{|l|r|r|r|r|r|r|\n";
echo "\\hline\n";
echo "event type      & total & TP & TN & FP & FN & \\% accuracy \\\\\n";
echo "\\hline\n";
echo "\\hline\n";

foreach ($types as $type) {
    echo str_replace("_", " ", str_pad($type, 25, " ", STR_PAD_RIGHT))
         . " &" . str_pad($total[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($present_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($no_present_no_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($present_no_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($no_present_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad( number_format((($present_expert[$type] + $no_present_no_expert[$type]) / $total[$type]), 2), 15, " ", STR_PAD_LEFT)
         . "\\\\\n";

    echo "\\hline\n";
}
echo "\\end{tabular}\n\n\n";

echo "QUORUMS:\n";
echo "\\begin{tabular}{|l|r|r|r|r|r|r|r|}\n";
echo "\\hline\n";
echo "event type      & total & TP & TN & FP & FN & \\% accuracy \\\\\n";
echo "\\hline\n";
echo "\\hline\n";

foreach ($types as $type) {
    echo str_replace("_", " ", str_pad($type, 25, " ", STR_PAD_RIGHT))
         . " &" . str_pad($total[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($quorum_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($no_quorum_no_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($quorum_no_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($no_quorum_expert[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad( number_format((($quorum_expert[$type] + $no_quorum_no_expert[$type]) / $total[$type]), 2), 15, " ", STR_PAD_LEFT)
         . "\\\\\n";

    echo "\\hline\n";
}
echo "\\end{tabular}\n\n\n";



echo "\\begin{tabular}{|l|r|r|r|r|}\n";
echo "\\hline\n";
echo "event type      & total with event & had quorum & inconclusives & conflicts \\\\\n";
echo "\\hline\n";
echo "\\hline\n";

foreach ($types as $type) {
    echo str_replace("_", " ", str_pad($type, 25, " ", STR_PAD_RIGHT))
         . " &" . str_pad($has_event[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($was_valid[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($has_inconclusive[$type], 15, " ", STR_PAD_LEFT)
         . " &" . str_pad($conflicts[$type], 15, " ", STR_PAD_LEFT)
         . "\\\\\n";

    echo "\\hline\n";
}
echo "\\end{tabular}\n";


?>
