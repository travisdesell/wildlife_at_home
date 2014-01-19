<?php

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');

function set_time_text($time_s) {
    if ($time_s == -1) return -1;
    else {
        return str_pad(floor($time_s / 3600), 2, '0', STR_PAD_LEFT) . ":" . str_pad(floor(($time_s % 3600) / 60), 2, '0', STR_PAD_LEFT) . ":" . str_pad(($time_s % 60), 2, '0', STR_PAD_LEFT);
    }
}


function get_observations($row_only, $video_id, $user_id, $observation_id, $species_id, $expert_only) {
    global $wildlife_user, $wildlife_passwd, $boinc_user, $boinc_passwd;

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
    mysql_select_db("wildlife", $boinc_db);

    $query = "";
    $result = null;
    if ($row_only) {
        $query = "SELECT * FROM timed_observations WHERE id = $observation_id";
        $result = attempt_query_with_ping($query, $wildlife_db);
    } else {
        $query = "SELECT * FROM timed_observations WHERE video_id = $video_id AND user_id = $user_id ORDER BY start_time, end_time";
        $result = attempt_query_with_ping($query, $wildlife_db);
    }

    //error_log("query: $query");

    $observations['has_observations'] = false;

    while ($row = mysql_fetch_assoc($result)) {
        $observations['has_observations'] = true;

        $row['start_time_text'] = set_time_text($row['start_time_s']);
        $row['end_time_text'] = set_time_text($row['end_time_s']);

        $query = "SELECT name FROM user WHERE id = " . $row['user_id'];
        $user_result = attempt_query_with_ping($query, $boinc_db);
        $user_row = mysql_fetch_assoc($user_result);

        $row['user_name'] = $user_row['name'];

        $selected_tags = explode("#", $row['tags']);
        $row['tags'] = array();
        foreach ($selected_tags as $tag) {
            $current_tag['tag_text'] = $tag;
            $row['tags'][] = $current_tag;
        }

        $query = "SELECT category, name, possible_tags FROM observation_types WHERE id = " . $row['event_id'];
        $type_result = attempt_query_with_ping($query, $wildlife_db);
        $type_row = mysql_fetch_assoc($type_result);
        $row['event_type'] = $type_row['category'] . " - " . $type_row['name'];

        $event_info = $row;

        if ($type_row['possible_tags'] != '') {
            $event_info['has_tags'] = true;

            $tag_id = 0;
            $possible_tags = explode(", ", $type_row['possible_tags']);
            $tags = array();
            foreach ($possible_tags as $tag) {
                $current_tag['tag_name'] = $tag;
                $current_tag['tag_id'] = $tag_id;

                $tags[] = $current_tag;
                $tag_id++;
            }

            $event_info['possible_tags'] = $tags;
        }

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

        $dropdown_result = attempt_query_with_ping($query, $wildlife_db);
        if (!$dropdown_result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        }

        $event_info['event_list'] = array();

        if ($row['event_id'] == 0) {
            $event_info['dropdown_text'] = "Select Event <span class='caret'></span>";
        } else {
            $event_info['dropdown_text'] = $type_row['category'] . " - " . $type_row['name'] . " <span class='caret'></span>";
        }

        $prev_category = ''; 
        while ($dropdown_row = mysql_fetch_assoc($dropdown_result)) {
            if ($dropdown_row['category'] != $prev_category) $dropdown_row['new_category'] = true;

            $dropdown_row['event_id'] = $dropdown_row['id'];
            unset($dropdown_row['id']);

            $event_info['event_list'][] = $dropdown_row;
        }   

        $prev_category = $event_info['event_list'][0]['category'];
        $prev_category_key = 0;
        $event_count = 1;
        for ($i = 1; $i < count($event_info['event_list']); $i++) {
//            error_log("prev category: '$prev_category', current: '". $event_info['event_list'][$i]['category'] . "'");

            if (0 != strcmp($event_info['event_list'][$i]['category'], $prev_category) ) { 
//                error_log("    different, event_count is: $event_count");

                $event_info['event_list'][$prev_category_key]['event_count'] = $event_count;
                $event_info['event_list'][$prev_category_key]['new_category'] = true;

                $prev_category = $event_info['event_list'][$i]['category'];
                $prev_category_key = $i; 
                $event_count = 0;
            }   
            $event_info['event_list'][$i]['new_category'] = false;
            $event_info['event_list'][$i]['new_column'] = false;

//            error_log("  checking: $i == " . floor(count($event_info['event_list']) / 2) );
            if ($i == floor(count($event_info['event_list']) / 2)) {
                $event_info['event_list'][$i]['new_column'] = true;
//                error_log("    SETTING NEW COLUMN");
            }   

            $event_count++;
        }   
        $event_info['event_list'][$prev_category_key]['event_count'] = $event_count;
        $event_info['event_list'][$prev_category_key]['new_category'] = true;

        $observations['observations'][] = $event_info;
    }

    return $observations;
}

function get_timed_observation_table($video_id, $user_id, &$observation_count, $species_id, $expert_only) {
    $observations = get_observations(false, $video_id, $user_id, null, $species_id, $expert_only);
    $observation_count = count($observations['observations']);

    $observation_table_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/observation_table_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($observation_table_template, $observations);
}

function get_timed_observation_row($observation_id, $species_id, $expert_only) {
    $observations = get_observations(true, null, null, $observation_id, $species_id, $expert_only);
    $observations['row_only'] = true;

    $observation_table_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/observation_table_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($observation_table_template, $observations);
}

function get_watch_video_interface($species_id, $video_id, $video_file, $animal_id, $user, $start_time) {
    $watch_info['video_id'] = $video_id;
    $watch_info['video_file'] = $video_file;
    $watch_info['start_time'] = $start_time;
    $watch_info['animal_id'] = $animal_id;
    $watch_info['trimmed_filename'] = trim(substr($video_file, strrpos($video_file, '/') + 1));
    $watch_info['bossa_total_credit'] = $user['bossa_total_credit'];
    $watch_info['total_events'] = $user['total_events'];
    $watch_info['valid_events'] = $user['valid_events'];
    $watch_info['invalid_events'] = $user['invalid_events'];
    $watch_info['missed_events'] = $user['missed_events'];


    $watch_interface_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/templates/watch_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($watch_interface_template, $watch_info);
}

?>
