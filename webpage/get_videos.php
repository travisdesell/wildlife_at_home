<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once($cwd . '/navbar.php');
require_once($cwd . '/footer.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/boinc_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/get_video_filter.php');
require_once($cwd . '/user.php');


$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$video_filter_text = mysql_real_escape_string($_POST['video_filter_text']);
$event_filter_text = mysql_real_escape_string($_POST['event_filter_text']);
$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);
$showing_all_videos = mysql_real_escape_string($_POST['showing_all_videos']);

error_log("SHOWING ALL VIDEOS: $showing_all_videos\n");

$user = get_user();
$query = "";

//if not expert, add flag so that videos are only the users own videos
if ($video_filter_text != '' || $event_filter_text != '') {
    create_filter($video_filter_text, $event_filter_text, $filter_query, $has_observation_query);

    if (is_special_user__fixme($user, true) && $showing_all_videos == 'true') {
        $query = "SELECT v2.id, v2.processing_status, v2.watermarked_filename, v2.timed_obs_count, v2.expert_finished, v2.release_to_public, v2.start_time, v2.animal_id, v2.rivermile FROM video_2 AS v2 WHERE " . $filter_query;
    } else {
        $query = "SELECT v2.id, v2.processing_status, v2.watermarked_filename, v2.timed_obs_count, v2.expert_finished, v2.release_to_public, v2.start_time, v2.animal_id, v2.rivermile FROM video_2 AS v2 INNER JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE " . $filter_query;
    }
    
    $query .= " ORDER BY animal_id, start_time LIMIT $video_min, $video_count";
    error_log("QUERY: $query");
} else {
    if (is_special_user__fixme($user, true) && $showing_all_videos == 'true') {
        $query = "SELECT id, processing_status, watermarked_filename, timed_obs_count, expert_finished, release_to_public, start_time, animal_id, rivermile FROM video_2 ORDER BY animal_id, start_time LIMIT $video_min, $video_count";
    } else {
        $query = "SELECT v2.id, v2.processing_status, v2.watermarked_filename, v2.timed_obs_count, v2.expert_finished, v2.release_to_public, v2.start_time, v2.animal_id, v2.rivermile FROM video_2 as v2 RIGHT JOIN watched_videos AS wv ON (v2.id = wv.video_id AND wv.user_id = " . $user['id'] . ") WHERE v2.timed_obs_count > 0 ORDER BY animal_id, start_time LIMIT $video_min, $video_count";
        error_log($query);
    }
}

$result = attempt_query_with_ping($query, $wildlife_db);


$found = false;
while ($row = mysql_fetch_assoc($result)) {
    $found = true;

    $row['check_button_type'] = '';

    if ($row['release_to_public'] == false) {
        $row['private'] = true;
    }

    if ($row['processing_status'] != 'WATERMARKED' && $row['processing_status'] != 'SPLIT') {
        $row['not_ready'] = true;
    }

    if ($row['expert_finished'] == 'FINISHED') {
        $row['check_button_type'] = 'btn-success';
    } else if ($row['expert_finished'] == 'WATCHED') {
        $row['check_button_type'] = 'btn-primary';
    }

    if ($row['timed_obs_count'] == 1) {
        $row['timed_obs_count'] .= " recorded event&nbsp;";
    } else {
        $row['timed_obs_count'] .= " recorded events";
    }


    if (is_special_user__fixme($user, true)) {
        $row['special_user'] = true;
    }

    $wf = $row['watermarked_filename'];
    $row['cleaned_filename'] = basename($wf);
    $video_list['video_list'][] = $row;
}

if ($found) {
    $video_list_template = file_get_contents($cwd . "/templates/expert_list_template.html");
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


