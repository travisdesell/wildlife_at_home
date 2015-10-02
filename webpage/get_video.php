<?php


$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/watch_interface/observation_table.php');

require $cwd[__FILE__] . '/../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$video_id = $boinc_db->real_escape_string($_POST['video_id']);
$video_file = $boinc_db->real_escape_string($_POST['video_file']);
$video_converted = $boinc_db->real_escape_string($_POST['video_converted']);

$user = csg_get_user(true);

$query = "SELECT species_id, watermarked_filename, animal_id, processing_status, start_time, needs_revalidation FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);
$row = $result->fetch_assoc();

$species_id = $row['species_id'];
$animal_id = $row['animal_id'];
$video_file = $row['watermarked_filename'];
$start_time = $row['start_time'];
$needs_revalidation = $row['needs_revalidation'];

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";
if ($row['processing_status'] == 'UNWATERMARKED' || $row['processing_status'] == 'WATERMARKING') {
    echo "
        <div class='row'>
            <div class='col-sm-6' id='wildlife-video-span-$video_id'>
                <p>This video has not yet been converted to a format where it can be streamed on the expert video classification webpage.</p>
            </div>
            <div class='col-sm-6'>
            </div>
        </div>";
} else {
    echo get_expert_video_row($species_id, $video_id, $video_file, $animal_id, $start_time, $needs_revalidation, $user);
}

?>
