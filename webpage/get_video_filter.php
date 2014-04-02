<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/user.php');

function create_filter($video_filter_text, $event_filter_text, &$query, &$has_observation_filter) {
    $query = "";

    $user = get_user();
    $usr_str = "";
    if (!is_special_user__fixme($user, true)) {
        $usr_str = "AND obs.user_id = " . $user['id'];
    }
            
    error_log("video filter text: '$video_filter_text'");
    error_log("event filter text: '$event_filter_text'");

    $event_filters = explode("##", $event_filter_text);
    $with = true;
    $has_observation_filter = false;

//    error_log("event_filters count: " . count($event_filters));

    foreach ($event_filters as $f) {
//        error_log("   filter: '$f'");

        if (0 == strcmp($f, "with") || 0 == strcmp($f, "from")) {
            $with = true;
        } else if (0 == strcmp($f, "without") || 0 == strcmp($f, "not from")) {
            $with = false;
        } else if (0 == strcmp($f, "and")) {
            $query .= " AND ";
        } else if (0 == strcmp($f, "or")) {
            $query .= " OR ";
        } else if (0 == strcmp($f, "")) {
            break;
        } else {
            $parts = explode(" ", $f);

            $eq = "=";
            $ex = "";
            if (!$with) {
                $eq = "!=";
                $ex = "NOT";
            }

            if (0 == strcmp($parts[0], "event")) {
                $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.event_id = " . $parts[1] . ")";
                $has_observation_filter = true;
            } else if (0 == strcmp($parts[0], "other")) {
                if ($parts[1] == 'converted_events') {
                    $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.auto_generated = 1)";
                } else if ($parts[1] == 'invalid_times') {
                    $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.start_time_s > obs.end_time_s)";
                } else if ($parts[1] == 'unreported_events') {
                    $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.report_status = 'UNREPORTED')";
                } else if ($parts[1] == 'reported_events') {
                    $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.report_status = 'REPORTED')";
                } else if ($parts[1] == 'responded_events') {
                    $query .= "$ex EXISTS (SELECT obs.id FROM timed_observations AS obs WHERE v2.id = obs.video_id $usr_str AND obs.report_status = 'RESPONDED')";
                }
            }

            /*
            if (0 == strcmp($parts[0], "event")) {
                $query .= "(v2.id = obs.video_id $usr_str AND obs.event_id $eq " . $parts[1] . ")";
                $has_observation_filter = true;
            } else if (0 == strcmp($parts[0], "other")) {
                if ($parts[1] == 'converted_events') {
                    $query .= "(v2.id = obs.video_id $usr_str AND obs.auto_generated $eq 1)";
                } else if ($parts[1] == 'invalid_times') {
                    $query .= "(v2.id = obs.video_id $usr_str AND obs.start_time_s > obs.end_time_s)";
                } else if ($parts[1] == 'unreported_events') {
                    $query .= "(v2.id = obs.video_id $usr_str AND obs.report_status $eq 'UNREPORTED')";
                } else if ($parts[1] == 'reported_events') {
                    $query .= "(v2.id = obs.video_id $usr_str AND obs.report_status $eq 'REPORTED')";
                } else if ($parts[1] == 'responded_events') {
                    $query .= "(v2.id = obs.video_id $usr_str AND obs.report_status $eq 'RESPONDED')";
                }
            }
             */
        }
    }

    $video_filters = explode("##", $video_filter_text);
    $with = true;
    $has_observation_filter = false;

    if (count($video_filters) > 1 && count($event_filters) > 1) {
        $query .= " AND ";
    }

    foreach ($video_filters as $f) {
//        error_log("   filter: '$f'");

        if (0 == strcmp($f, "with") || 0 == strcmp($f, "from")) {
            $with = true;
        } else if (0 == strcmp($f, "without") || 0 == strcmp($f, "not from")) {
            $with = false;
        } else if (0 == strcmp($f, "and")) {
            $query .= " AND ";
        } else if (0 == strcmp($f, "or")) {
            $query .= " OR ";
        } else if (0 == strcmp($f, "")) {
            break;
        } else {
            $parts = explode(" ", $f);

            $eq = "=";
            if (!$with) $eq = "!=";

            if (0 == strcmp($parts[0], "animal_id")) {
                $query .= "v2.animal_id $eq '" . $parts[1] . "'";
            } else if (0 == strcmp($parts[0], "year")) {
                $query .= "DATE_FORMAT(v2.start_time, '%Y') $eq " . $parts[1];
            } else if (0 == strcmp($parts[0], "location")) {
                $query .= "v2.location_id $eq " . $parts[1];
            } else if (0 == strcmp($parts[0], "species")) {
                $query .= "v2.species_id $eq " . $parts[1];
            } else if (0 == strcmp($parts[0], "other")) {
                if ($parts[1] == 'public') {
                    $query .= "v2.release_to_public $eq 1";
                } else if ($parts[1] == 'private') {
                    $query .= "v2.release_to_public $eq 0";
                } else if ($parts[1] == 'unwatched') {
                    $query .= "v2.expert_finished $eq 'UNWATCHED'";
                } else if ($parts[1] == 'watched') {
                    $query .= "v2.expert_finished $eq 'WATCHED'";
                } else if ($parts[1] == 'finished') {
                    $query .= "v2.expert_finished $eq 'FINISHED'";
                }
            }
        }
    }
}

?>
