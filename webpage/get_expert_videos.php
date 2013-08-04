<?php

require_once('/projects/wildlife/html/inc/util.inc');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');


$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$animal_id = mysql_real_escape_string($_POST['animal_id']);

$filter = '';
if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";
if ($animal_id !== '-1' && $animal_id !== '0') $filter .= " AND animal_id = '$animal_id'";

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

//fix query to filter by ids

$query = "SELECT id, watermarked_filename, expert_obs_count, expert_finished from video_2 WHERE processing_status != 'UNWATERMARKED' $filter LIMIT $video_min, $video_count";
$result = attempt_query_with_ping($query, $wildlife_db);

$found = false;
while ($row = mysql_fetch_assoc($result)) {
    $found = true;

    $row['check_button_type'] = '';
    if ($row['expert_obs_count'] > 0) {
        $row['check_button_type'] = 'btn-primary';
    }
    if ($row['expert_finished'] == true) {
        $row['check_button_type'] = 'btn-success';
    }

    $wf = $row['watermarked_filename'];

    for ($i = 0; $i < 7; $i++) {
        $wf = substr($wf, strpos($wf, '/') + 1);
    }
    $wf = str_replace("/", " - ", $wf);

    $row['cleaned_filename'] = $wf;
    $video_list['video_list'][] = $row;
}

if ($found) {
    $video_list_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/expert_list_template.html");
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


