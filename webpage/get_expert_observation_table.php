<?php

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

function get_expert_observation_table($video_id, &$observation_count) {
    global $wildlife_user, $wildlife_passwd, $boinc_user, $boinc_passwd;

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
    mysql_select_db("wildlife", $boinc_db);

    $query = "SELECT * FROM expert_observations WHERE video_id = $video_id ORDER BY start_time, end_time";
    $result = attempt_query_with_ping($query, $wildlife_db);

    //error_log("query: $query");

    $observations['video_id'] = $video_id;
    $observations['has_observations'] = false;

    while ($row = mysql_fetch_assoc($result)) {
        $observations['has_observations'] = true;

        $query = "SELECT name FROM user WHERE id = " . $row['user_id'];
        $user_result = attempt_query_with_ping($query, $boinc_db);
        $user_row = mysql_fetch_assoc($user_result);

        $row['user_name'] = $user_row['name'];

        $observations['observations'][] = $row;
    }
    $observation_count = count($observations['observations']);

    $observation_table_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/expert_observation_table_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($observation_table_template, $observations);
}

function get_timed_observation_table($video_segment_id, $user_id, &$observation_count) {
    global $wildlife_user, $wildlife_passwd, $boinc_user, $boinc_passwd;

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
    mysql_select_db("wildlife", $boinc_db);

    $query = "SELECT * FROM timed_observations WHERE video_segment_id = $video_segment_id AND user_id = $user_id ORDER BY start_time, end_time";
    $result = attempt_query_with_ping($query, $wildlife_db);

    //error_log("query: $query");

    $observations['video_id'] = $video_segment_id;
    $observations['has_observations'] = false;

    while ($row = mysql_fetch_assoc($result)) {
        $observations['has_observations'] = true;

        $query = "SELECT name FROM user WHERE id = " . $row['user_id'];
        $user_result = attempt_query_with_ping($query, $boinc_db);
        $user_row = mysql_fetch_assoc($user_result);

        $row['user_name'] = $user_row['name'];

        $query = "SELECT category, name FROM observation_types WHERE id = " . $row['event_id'];
        $type_result = attempt_query_with_ping($query, $wildlife_db);
        $type_row = mysql_fetch_assoc($type_result);
        $row['event_type'] = $type_row['category'] . " - " . $type_row['name'];

        $observations['observations'][] = $row;
    }
    $observation_count = count($observations['observations']);

    $observation_table_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/expert_observation_table_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($observation_table_template, $observations);
}


function get_watch_video_interface($wildlife_db, $species_id, $video_id, $video_segment_id, $video_file, $start_time, $expert_only) {
    if ($video_segment_id >= 0) {
        $watch_info['video_id'] = $video_segment_id;
    } else {
        $watch_info['video_id'] = $video_id;
    }
    $watch_info['video_file'] = $video_file;
    $watch_info['start_time'] = $start_time;

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

    $watch_info['event_list'] = array();
    $prev_category = ''; 
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['category'] != $prev_category) $row['new_category'] = true;

        $row['event_id'] = $row['id'];

        $watch_info['event_list'][] = $row;
    }   

    $prev_category = $watch_info['event_list'][0]['category'];
    $prev_category_key = 0;
    $event_count = 1;
    for ($i = 1; $i < count($watch_info['event_list']); $i++) {
//        error_log("prev category: '$prev_category', current: '". $watch_info['event_list'][$i]['category'] . "'");

        if (0 != strcmp($watch_info['event_list'][$i]['category'], $prev_category) ) { 
//            error_log("    different, event_count is: $event_count");

            $watch_info['event_list'][$prev_category_key]['event_count'] = $event_count;
            $watch_info['event_list'][$prev_category_key]['new_category'] = true;

            $prev_category = $watch_info['event_list'][$i]['category'];
            $prev_category_key = $i; 
            $event_count = 0;
        }   
        $watch_info['event_list'][$i]['new_category'] = false;
        $watch_info['event_list'][$i]['new_column'] = false;

        if ($i == (count($watch_info['event_list']) / 2) - 1) {
            $watch_info['event_list'][$i]['new_column'] = true;
        }   

        $event_count++;
    }   
    $watch_info['event_list'][$prev_category_key]['event_count'] = $event_count;
    $watch_info['event_list'][$prev_category_key]['new_category'] = true;


    $watch_interface_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/watch_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($watch_interface_template, $watch_info);
}

?>
