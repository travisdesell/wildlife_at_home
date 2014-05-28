<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');
require_once($cwd . '/watch_interface/observation_table.php');


$video_id = mysql_real_escape_string($_POST['video_id']);
$video_file = mysql_real_escape_string($_POST['video_file']);
$video_converted = mysql_real_escape_string($_POST['video_converted']);

$user = get_user(true);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT species_id, watermarked_filename, animal_id, processing_status, start_time, needs_revalidation FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$species_id = $row['species_id'];
$animal_id = $row['animal_id'];
$video_file = $row['watermarked_filename'];
$start_time = $row['start_time'];
$needs_revalidation = $row['needs_revalidation'];

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";
if ($row['processing_status'] == 'UNWATERMARKED') {
    echo "
        <div class='row-fluid'>
            <div class='span6' id='wildlife-video-span-$video_id'>
                <p>This video has not yet been converted to a format where it can be streamed on the expert video classification webpage.</p>
            </div>
            <div class='span6'>
            </div>
        </div>"; 
} else {
    echo get_expert_video_row($species_id, $video_id, $video_file, $animal_id, $start_time, $needs_revalidation, $user);
}

?>
