<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));


require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$species_id = $boinc_db->real_escape_string($_POST['species_id']);
$location_id = $boinc_db->real_escape_string($_POST['location_id']);
$year = $boinc_db->real_escape_string($_POST['year']);
$video_status = $boinc_db->real_escape_string($_POST['video_status']);

$filter = '';

if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";
if ($year !== '') $filter .= " AND DATE_FORMAT(start_time, '%Y') = $year";
if ($video_status !== '') $filter .= " AND expert_finished = '$video_status'";

if (strlen($filter) > 5) $filter = "WHERE " . substr($filter, 5);


$query = "SELECT DISTINCT animal_id FROM video_2 $filter ORDER BY animal_id";

//echo "<!-- $query -->\n";

$result = query_wildlie_video_db($query);

echo "<li><a href='javascript:;' class='animal-id-dropdown' animal_id='0' id='animal-id-dropdown-0'>Any Animal ID</a></li>";

while ($row = $result->fetch_assoc()) {
    $animal_id = $row['animal_id'];
    echo "<li><a href='javascript:;' class='animal-id-dropdown' animal_id='$animal_id' id='animal-id-dropdown-$animal_id'>$animal_id</a></li>";
}

?>
