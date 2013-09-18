<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/get_video_segment_query.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$filters = array();
if (array_key_exists('filters', $_POST)) {
    $filters = $_POST['filters'];
}


$sort_by = mysql_real_escape_string($_POST['sort_by']);
if ($sort_by != 'filename') {
    if ($filters['instructional'] == 'true') {
        $sort_by = "(SELECT id FROM observations WHERE observations.video_segment_id = vs2.id AND observations.status = 'EXPERT') DESC";
    }
}

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

if (array_key_exists('instructional', $filters) && $filters['instructional'] == 'true') {
    $user = get_logged_in_user(false);
    $user_id = $user->id;
} else if ($_POST['all_users'] == 'true') {
    $user = get_logged_in_user();
    $user_id = $user->id;

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
    mysql_select_db("wildlife", $boinc_db);

    if (!is_special_user($user_id, $boinc_db)) {
        echo "<div class='well well-large' style='padding-top:15px; padding-bottom:5px'>";
        echo "<div class='row-fluid>";
        echo "<div class='span12' style='margin-left:0px;'>";
        echo "<p>Only project scientists can display videos from all users.</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        die();
    }

} else {
    $user = get_logged_in_user();
    $user_id = $user->id;
}

create_filter($filters, $filter, $reported_filter);
//
//error_log("filter string: '$filter'");

if (empty($filters)) {
    echo "<div class='well well-large' style='padding-top:15px; padding-bottom:5px'>";
    echo "<div class='row-fluid>";
    echo "<div class='span12' style='margin-left:0px;'>";
    echo "<p>You can select how to filter your watched videos using the dropdowns to the left. Selecting these will show videos you've watched which match all of the highlighted filters. So if you have selected 'Interesting - Yes' and 'Predator Presence - Yes' and 'Nest Defense - Unsure', the page will display all videos you've watched where you reported that it was interesting, there was a predator, and that you were unsure of nest defense.</p>\n";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    die();
}

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "";

if (strlen($reported_filter) > 0) $reported_filter = " AND " . $reported_filter;
if (strlen($filter) > 0) $filter = " AND " . $filter;

if (array_key_exists('instructional', $filters) && $filters['instructional'] == 'true') {
    if (strlen($filter) > 0) {
        $query = "SELECT vs2.id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 RIGHT JOIN observations o ON (o.status = 'EXPERT' AND o.video_segment_id = vs2.id $filter) WHERE vs2.crowd_obs_count > 0 $reported_filter ORDER BY $sort_by LIMIT $video_min, $video_count";
    } else {
        $query = "SELECT vs2.id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 WHERE vs2.crowd_obs_count > 0 $reported_filter ORDER BY $sort_by LIMIT $video_min, $video_count";
    }
} else if ($_POST['all_users'] == 'true') {
    $query = "SELECT vs2.id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 RIGHT JOIN observations o ON (o.video_segment_id = vs2.id $filter) WHERE vs2.crowd_obs_count > 0 $reported_filter ORDER BY $sort_by LIMIT $video_min, $video_count";
} else {
    $query = "SELECT vs2.id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 RIGHT JOIN observations o ON (o.user_id = $user_id AND o.video_segment_id = vs2.id $filter) WHERE vs2.crowd_obs_count > 0 $reported_filter ORDER BY $sort_by LIMIT $video_min, $video_count";
}

error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$video_list = array();

function set_marks(&$value, $bool = false) {
    if ($value == 1) {
        $value = '&#x2713;';
    } else if ($value == 0) {
        if ($bool) {
            $value = 'x';
        } else {
            $value = '?';
        }
    } else if ($value == -1) {
        $value = 'x';
    }
}
$found = false;
while ($row = mysql_fetch_assoc($result)) {
    $found = true;

    $video_segment_2_id = $row['id'];
    $segment_filename = $row['filename'];

    $video_and_observations = array();
    $video_and_observations['video_segment_2_id'] = $video_segment_2_id;
    $video_and_observations['segment_filename'] = $segment_filename;
    $video_and_observations['video_name'] = trim(substr($segment_filename, strrpos($segment_filename, '/') + 1));
    $video_and_observations['crowd_obs_count'] = $row['crowd_obs_count'];

    if ($row['report_status'] == 'UNREPORTED') {
        $video_and_observations['unreported'] = true;
    } else if ($row['report_status'] == 'REPORTED') {
        $video_and_observations['reported'] = true;

        $report_query = "SELECT report_comments, reporter_name FROM reported_video WHERE video_segment_id = " . $video_segment_2_id;
        $report_result = attempt_query_with_ping($report_query, $wildlife_db);
        if (!$report_result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
        }
        $report_row = mysql_fetch_assoc($report_result);

        $video_and_observations['reporter_name'] = $report_row['reporter_name'];
        $video_and_observations['report_comments'] = $report_row['report_comments'];
    } else if ($row['report_status'] == 'REVIEWED') {
        $video_and_observations['reviewed'] = true;

        $report_query = "SELECT report_comments, reporter_name, review_comments, reviewer_name FROM reported_video WHERE video_segment_id = " . $video_segment_2_id;
        $report_result = attempt_query_with_ping($report_query, $wildlife_db);
        if (!$report_result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
        }
        $report_row = mysql_fetch_assoc($report_result);

        $video_and_observations['reporter_name'] = $report_row['reporter_name'];
        $video_and_observations['report_comments'] = $report_row['report_comments'];
        $video_and_observations['reviewer_name'] = $report_row['reviewer_name'];
        $video_and_observations['review_comments'] = $report_row['review_comments'];
    }

    $video2_query = "SELECT animal_id FROM video_2 WHERE id = " . $row['video_id'];
    $video2_result = attempt_query_with_ping($video2_query, $wildlife_db);
    if (!$video2_result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video2_query\n");
        die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video2_query\n");
    }


    $video2_row = mysql_fetch_assoc($video2_result);

    $video_and_observations['animal_id'] = $video2_row['animal_id'];

    $video_and_observations['discuss_video_content']= "I would like to discuss this video:\n" . "[" . "video" . "]" . $segment_filename . "[/video" . "]";


    $observation_query = "SELECT id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, interesting, user_id, comments, status, video_issue, chick_presence, awarded_credit FROM observations WHERE video_segment_id = $video_segment_2_id";

    $observation_result = attempt_query_with_ping($observation_query, $wildlife_db);
    if (!$observation_result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observation_query\n");
        die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observation_query\n");
    }

    while ($observation_row = mysql_fetch_assoc($observation_result)) {
        if ($observation_row['status'] == 'CANONICAL') $video_and_observations['has_canonical'] = true;
        if ($observation_row['status'] == 'EXPERT') $video_and_observations['has_canonical'] = true;

        set_marks($observation_row['interesting']);
        set_marks($observation_row['bird_leave']);
        set_marks($observation_row['bird_return']);
        set_marks($observation_row['bird_presence']);
        set_marks($observation_row['bird_absence']);
        set_marks($observation_row['predator_presence']);
        set_marks($observation_row['nest_defense']);
        set_marks($observation_row['nest_success']);
        set_marks($observation_row['chick_presence']);
        set_marks($observation_row['video_issue'], true);

        $observation_row['user_id'] = get_user_from_id($observation_row['user_id'])->name;
        $video_and_observations['observations'][] = $observation_row;
    }

    $video_list['video_list'][] = $video_and_observations;

}

if ($found) {
    $video_list_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/video_list_template.html");
    $mustache_engine = new Mustache_Engine;
    echo $mustache_engine->render($video_list_template, $video_list);

} else {
    /**
     *  Fix the error message according to what type of video is being looked for.
     */
    echo "<div class='well well-large' style='padding-top:15px'>";
    echo "<div class='span12'>";
    echo "<p>Could not find any watched videos that match all the selected types.<p>\n";
    echo "</div>";
    echo "</div>";
}

?>
