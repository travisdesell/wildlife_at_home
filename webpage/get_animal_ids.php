<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$filter = '';

if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";

if (strlen($filter) > 5) $filter = "WHERE " . substr($filter, 5);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "SELECT DISTINCT animal_id FROM video_2 $filter";

//echo "<!-- $query -->\n";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

echo "<li><a href='#' class='animal-id-dropdown' animal_id='0' id='animal-id-dropdown-0'>Any Animal ID</a></li>";

while ($row = mysql_fetch_assoc($result)) {
    $animal_id = $row['animal_id'];
    echo "<li><a href='#' class='animal-id-dropdown' animal_id='$animal_id' id='animal-id-dropdown-$animal_id'>$animal_id</a></li>";
}

?>
