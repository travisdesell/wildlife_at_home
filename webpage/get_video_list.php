<?php

require_once('/projects/wildlife/html/inc/util.inc');

require('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require('/home/tdesell/wildlife_at_home/webpage/my_query.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);
$filter = mysql_real_escape_string($_POST['filter']);

if ($filter == 'interesting-nav-pill') {
    $filter = "observations.interesting > 0";
} else if ($filter == 'invalid-nav-pill') {
    $filter = "observations.status = 'INVALID'";
} else if ($filter == 'bird-presence-nav-pill') {
    $filter = "observations.bird_presence > 0";
} else if ($filter == 'chick-presence-nav-pill') {
    $filter = "observations.chick_presence > 0";
} else if ($filter == 'predator-presence-nav-pill') {
    $filter = "observations.predator_presence > 0";
} else if ($filter == 'nest-defense-nav-pill') {
    $filter = "observations.nest_defense > 0";
} else if ($filter == 'nest-success-nav-pill') {
    $filter = "observations.nest_success > 0";
} else if ($filter == 'bird-leave-nav-pill') {
    $filter = "observations.bird_leave > 0";
} else if ($filter == 'bird-return-nav-pill') {
    $filter = "observations.bird_return > 0";
} else {
    echo "";
    die();
}

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = get_logged_in_user();
$user_id = $user->id;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "SELECT id, filename, crowd_obs_count FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id AND $filter AND observations.video_segment_id = vs2.id) LIMIT $video_min, $video_count";

//echo "<!-- $query -->\n";

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

    $video_and_observations['discuss_video_content']= "I would like to discuss this video:\n" . "[" . "video" . "]" . $segment_filename . "[/video" . "]";


    $observation_query = "SELECT id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, interesting, user_id, comments, status, corrupt, too_dark, chick_presence FROM observations WHERE video_segment_id = $video_segment_2_id";

    $observation_result = attempt_query_with_ping($observation_query, $wildlife_db);
    if (!$observation_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observation_query\n");

    while ($observation_row = mysql_fetch_assoc($observation_result)) {
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
    echo "<p>No videos of this type have been watched.<p>\n";
    echo "</div>";
    echo "</div>";
}

?>
