<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$year = mysql_real_escape_string($_POST['year']);
$video_status = mysql_real_escape_string($_POST['video_status']);

$filter = '';

if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";
if ($year !== '') $filter .= " AND DATE_FORMAT(start_time, '%Y') = $year";
if ($video_status !== '') $filter .= " AND expert_finished = '$video_status'";

if (strlen($filter) > 5) $filter = "WHERE " . substr($filter, 5);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "SELECT DISTINCT animal_id FROM video_2 $filter ORDER BY animal_id";

//echo "<!-- $query -->\n";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

echo "<li><a href='javascript:;' class='animal-id-dropdown' animal_id='0' id='animal-id-dropdown-0'>Any Animal ID</a></li>";

while ($row = mysql_fetch_assoc($result)) {
    $animal_id = $row['animal_id'];
    echo "<li><a href='javascript:;' class='animal-id-dropdown' animal_id='$animal_id' id='animal-id-dropdown-$animal_id'>$animal_id</a></li>";
}

?>
