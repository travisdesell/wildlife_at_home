<?php

require_once('/projects/wildlife/html/inc/util.inc');

require('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require('/home/tdesell/wildlife_at_home/webpage/my_query.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$filters = $_POST['filters'];

//error_log("filters: " . json_encode($filters));

$new_filter = '';

if ($filters['interesting'] == 'yes')           $new_filter .= " AND observations.interesting > 0";
if ($filters['corrupt'] == 'yes')               $new_filter .= " AND observations.corrupt > 0";
if ($filters['too_dark'] == 'yes')              $new_filter .= " AND observations.too_dark > 0";

if ($filters['invalid'] == 'yes')               $new_filter .= " AND observations.status = 'INVALID'";

if ($filters['bird_presence'] == 'yes')      $new_filter .= " AND observations.bird_presence > 0";
if ($filters['bird_absence'] == 'yes')      $new_filter .= " AND observations.bird_absence > 0";
if ($filters['chick_presence'] == 'yes')     $new_filter .= " AND observations.chick_presence > 0";
if ($filters['predator_presence'] == 'yes')  $new_filter .= " AND observations.predator_presence > 0";
if ($filters['nest_defense'] == 'yes')       $new_filter .= " AND observations.nest_defense > 0";
if ($filters['nest_success'] == 'yes')       $new_filter .= " AND observations.nest_success > 0";
if ($filters['bird_return'] == 'yes')        $new_filter .= " AND observations.bird_return > 0";
if ($filters['bird_leave'] == 'yes')         $new_filter .= " AND observations.bird_leave > 0";

if ($filters['invalid'] == 'unsure')               $new_filter .= " AND observations.status = 'UNVALIDATED'";
if ($filters['bird_presence'] == 'unsure')      $new_filter .= " AND observations.bird_presence = 0";
if ($filters['bird_absence'] == 'unsure')      $new_filter .= " AND observations.bird_absence = 0";
if ($filters['chick_presence'] == 'unsure')     $new_filter .= " AND observations.chick_presence = 0";
if ($filters['predator_presence'] == 'unsure')  $new_filter .= " AND observations.predator_presence = 0";
if ($filters['nest_defense'] == 'unsure')       $new_filter .= " AND observations.nest_defense = 0";
if ($filters['nest_success'] == 'unsure')       $new_filter .= " AND observations.nest_success = 0";
if ($filters['bird_return'] == 'unsure')        $new_filter .= " AND observations.bird_return = 0";
if ($filters['bird_leave'] == 'unsure')         $new_filter .= " AND observations.bird_leave = 0";

if ($filters['invalid'] == 'no')               $new_filter .= " AND (observations.status = 'VALID' || observations.status = 'CANONICAL')";
if ($filters['bird_presence'] == 'no')      $new_filter .= " AND observations.bird_presence < 0";
if ($filters['bird_absence'] == 'no')      $new_filter .= " AND observations.bird_absence < 0";
if ($filters['chick_presence'] == 'no')     $new_filter .= " AND observations.chick_presence < 0";
if ($filters['predator_presence'] == 'no')  $new_filter .= " AND observations.predator_presence < 0";
if ($filters['nest_defense'] == 'no')       $new_filter .= " AND observations.nest_defense < 0";
if ($filters['nest_success'] == 'no')       $new_filter .= " AND observations.nest_success < 0";
if ($filters['bird_return'] == 'no')        $new_filter .= " AND observations.bird_return < 0";
if ($filters['bird_leave'] == 'no')         $new_filter .= " AND observations.bird_leave < 0";



if (strlen($new_filter) > 5) $new_filter = substr($new_filter, 5);
else {
    echo "<div class='well well-large' style='padding-top:15px; padding-bottom:5px'>";
    echo "<div class='container'>";
    echo "<div class='span12' style='margin-left:0px;'>";
    echo "<p>Click on the above observation types to display videos. Many will toggle bewteen yes, no, unsure, and unselected. Selecting observation types will show videos you've watched which have all of the highlighted types. So if you have selected <span class='label label-info'>interesting</span> and <span class='label label-info'>predator presence - yes</span> and <span class='label label-info'>nest defense - unsure</span>, the page will display all videos you've watched where you reported that it was interesting, there was a predator, and that you were unsure of nest defense.</p>\n";
    echo "</div>";
    echo "</div>";
    echo "</div>";
    die();
}

$filter = $new_filter;

if ($species_id > 0)  $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = get_logged_in_user();
$user_id = $user->id;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "SELECT id, filename, crowd_obs_count, video_id FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id AND $filter AND observations.video_segment_id = vs2.id) ORDER BY filename LIMIT $video_min, $video_count";

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
