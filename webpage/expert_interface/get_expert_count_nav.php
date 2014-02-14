<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/generate_count_nav.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$animal_id = mysql_real_escape_string($_POST['animal_id']);
$year = mysql_real_escape_string($_POST['year']);
$video_status = mysql_real_escape_string($_POST['video_status']);
$video_release = mysql_real_escape_string($_POST['video_release']);

$filter = '';
if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";
if ($animal_id !== '-1' && $animal_id !== '0') $filter .= " AND animal_id = '$animal_id'";
if ($year !== '') $filter .= " AND DATE_FORMAT(start_time, '%Y') = $year";
if ($video_status !== '') $filter .= " AND expert_finished = '$video_status'";
if ($video_release !== '') $filter .= " AND release_to_public = $video_release";

if (strlen($filter) > 4) $filter = substr($filter, 4);

$display_nav_numbers = true;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = '';
if ($filter == '') {
    $query = "SELECT count(id) FROM video_2";
} else {
    $query = "SELECT count(id) FROM video_2 vs2 WHERE $filter";
}

//error_log("$query");

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

$max_items = $row['count(id)'];

generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers);

?>
