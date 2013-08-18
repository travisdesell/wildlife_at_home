<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/generate_count_nav.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$filters = $_POST['filters'];

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

$filter = $new_filter;

if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";

$display_nav_numbers = true;
if (strlen($filter) < 5) {
    $display_nav_numbers = false;
}


//error_log("the filter is: " . $filter);


if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;


$user = get_logged_in_user();
$user_id = $user->id;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "SELECT count(id) FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id $filter AND observations.video_segment_id = vs2.id)";

//echo "<!-- $query -->\n";


$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

$max_items = $row['count(id)'];

generate_count_nav($max_items, $video_min, $video_count, $display_nav_numbers);

?>
