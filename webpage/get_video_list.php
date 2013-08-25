<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/get_video_segment_query.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$sort_by = mysql_real_escape_string($_POST['sort_by']);
if ($sort_by != 'filename') {
    if ($_POST['all_users'] == 'true') {
        $sort_by = "(SELECT id FROM observations WHERE observations.video_segment_id = vs2.id AND observations.status = 'EXPERT') DESC";
    }
}

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = get_logged_in_user();
$user_id = $user->id;

$filters = array();
if (array_key_exists('filters', $_POST)) {
    $filters = $_POST['filters'];
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
if ($_POST['all_users'] == 'true') {
    if (strlen($filter) > 0) {
        $filter = substr($filter, 5);
        $query = "SELECT id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 WHERE vs2.crowd_obs_count > 0 AND $reported_filter EXISTS (SELECT id FROM observations WHERE $filter AND observations.status = 'EXPERT' AND observations.video_segment_id = vs2.id) ORDER BY $sort_by LIMIT $video_min, $video_count";
    } else {
        $reported_filter = substr($reported_filter, 0, -4);
        $query = "SELECT id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 WHERE vs2.crowd_obs_count > 0 AND $reported_filter ORDER BY $sort_by LIMIT $video_min, $video_count";
    }
} else {
    $query = "SELECT id, filename, crowd_obs_count, video_id, report_status FROM video_segment_2 vs2 WHERE vs2.crowd_obs_count > 0 AND $reported_filter EXISTS (SELECT id FROM observations WHERE user_id = $user_id $filter AND observations.video_segment_id = vs2.id) ORDER BY $sort_by LIMIT $video_min, $video_count";
}

error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

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
        if (!$report_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
        $report_row = mysql_fetch_assoc($report_result);

        $video_and_observations['reporter_name'] = $report_row['reporter_name'];
        $video_and_observations['report_comments'] = $report_row['report_comments'];
    } else if ($row['report_status'] == 'REVIEWED') {
        $video_and_observations['reviewed'] = true;

        $report_query = "SELECT report_comments, reporter_name, review_comments, reviewer_name FROM reported_video WHERE video_segment_id = " . $video_segment_2_id;
        $report_result = attempt_query_with_ping($report_query, $wildlife_db);
        if (!$report_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $report_query\n");
        $report_row = mysql_fetch_assoc($report_result);

        $video_and_observations['reporter_name'] = $report_row['reporter_name'];
        $video_and_observations['report_comments'] = $report_row['report_comments'];
        $video_and_observations['reviewer_name'] = $report_row['reviewer_name'];
        $video_and_observations['review_comments'] = $report_row['review_comments'];
    }

    $video2_query = "SELECT animal_id FROM video_2 WHERE id = " . $row['video_id'];
    $video2_result = attempt_query_with_ping($video2_query, $wildlife_db);
    if (!$video2_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video2_query\n");

    $video2_row = mysql_fetch_assoc($video2_result);

    $video_and_observations['animal_id'] = $video2_row['animal_id'];

    $video_and_observations['discuss_video_content']= "I would like to discuss this video:\n" . "[" . "video" . "]" . $segment_filename . "[/video" . "]";


    $observation_query = "SELECT id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, interesting, user_id, comments, status, corrupt, too_dark, chick_presence FROM observations WHERE video_segment_id = $video_segment_2_id";

    $observation_result = attempt_query_with_ping($observation_query, $wildlife_db);
    if (!$observation_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observation_query\n");

    /*
    $video_and_observations['names'] = array();
    $video_and_observations['interesting'] = array();
    $video_and_observations['bird_leave'] = array();
    $video_and_observations['bird_return'] = array();
    $video_and_observations['bird_presence'] = array();
    $video_and_observations['bird_absence'] = array();
    $video_and_observations['predator_presence'] = array();
    $video_and_observations['nest_defense'] = array();
    $video_and_observations['nest_success'] = array();
    $video_and_observations['chick_presence'] = array();
    $video_and_observations['too_dark'] = array();
    $video_and_observations['corrupt'] = array();
     */

    while ($observation_row = mysql_fetch_assoc($observation_result)) {
        set_marks($observation_row['interesting']);
        set_marks($observation_row['bird_leave']);
        set_marks($observation_row['bird_return']);
        set_marks($observation_row['bird_presence']);
        set_marks($observation_row['bird_absence']);
        set_marks($observation_row['predator_presence']);
        set_marks($observation_row['nest_defense']);
        set_marks($observation_row['nest_success']);
        set_marks($observation_row['chick_presence']);
        set_marks($observation_row['too_dark'], true);
        set_marks($observation_row['corrupt'], true);

        $observation_row['user_id'] = get_user_from_id($observation_row['user_id'])->name;
        $video_and_observations['observations'][] = $observation_row;

        /**
         *  Make the list of users by columns instead of by rows.
         */
        /*
        $video_and_observations['names'][]['name'] = $observation_row['user_id'];
        $video_and_observations['status'][]['status'] = $observation_row['status'];
        $video_and_observations['interesting'][]['interesting'] = $observation_row['interesting'];
        $video_and_observations['bird_leave'][]['bird_leave'] = $observation_row['bird_leave'];
        $video_and_observations['bird_return'][]['bird_return'] = $observation_row['bird_return'];
        $video_and_observations['bird_presence'][]['bird_presence'] = $observation_row['bird_presence'];
        $video_and_observations['bird_absence'][]['bird_absence'] = $observation_row['bird_absence'];
        $video_and_observations['predator_presence'][]['predator_presence'] = $observation_row['predator_presence'];
        $video_and_observations['nest_defense'][]['nest_defense'] = $observation_row['nest_defense'];
        $video_and_observations['nest_success'][]['nest_success'] = $observation_row['nest_success'];
        $video_and_observations['chick_presence'][]['chick_presence'] = $observation_row['chick_presence'];
        $video_and_observations['too_dark'][]['too_dark'] = $observation_row['too_dark'];
        $video_and_observations['corrupt'][]['corrupt'] = $observation_row['corrupt'];
         */
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
