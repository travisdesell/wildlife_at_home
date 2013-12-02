<?php

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');

$species_id = mysql_real_escape_string($_POST['species_id']);
$video_id = mysql_real_escape_string($_POST['video_id']);
$expert_only = false;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$event_info['video_id'] = $video_id;

$query = "SELECT id, category, name, instructions FROM observation_types WHERE expert_only = $expert_only AND ";
if ($species_id == 1) { //sharptailed grouse
    $query .= "sharptailed_grouse = 1";
} else if ($species_id == 2) { //least tern
    $query .= "least_tern = 1";
} else if ($species_id == 3) { //piping plover
    $query .= "piping_plover = 1";
} else {
    return;
}   
$query .= " ORDER BY category, id";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$event_info['event_list'] = array();
$prev_category = ''; 
while ($row = mysql_fetch_assoc($result)) {
    if ($row['category'] != $prev_category) $row['new_category'] = true;

    $row['event_id'] = $row['id'];

    $event_info['event_list'][] = $row;
}   

$prev_category = $event_info['event_list'][0]['category'];
$prev_category_key = 0;
$event_count = 1;
for ($i = 1; $i < count($event_info['event_list']); $i++) {
    //        error_log("prev category: '$prev_category', current: '". $event_info['event_list'][$i]['category'] . "'");

    if (0 != strcmp($event_info['event_list'][$i]['category'], $prev_category) ) { 
        //            error_log("    different, event_count is: $event_count");

        $event_info['event_list'][$prev_category_key]['event_count'] = $event_count;
        $event_info['event_list'][$prev_category_key]['new_category'] = true;

        $prev_category = $event_info['event_list'][$i]['category'];
        $prev_category_key = $i; 
        $event_count = 0;
    }   
    $event_info['event_list'][$i]['new_category'] = false;
    $event_info['event_list'][$i]['new_column'] = false;

    if ($i == (count($event_info['event_list']) / 2) - 1) {
        $event_info['event_list'][$i]['new_column'] = true;
    }   

    $event_count++;
}   
$event_info['event_list'][$prev_category_key]['event_count'] = $event_count;
$event_info['event_list'][$prev_category_key]['new_category'] = true;

$event_dropdown_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/event_dropdown_template.html");
$mustache_engine = new Mustache_Engine;
response['html'] = $mustache_engine->render($event_dropdown_template, $event_info);


echo json_encode($response);
?>
