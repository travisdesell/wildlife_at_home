<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

function get_event_list($wildlife_db, $species_id, $expert_only) {

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

    $event_list['event_list'] = array();
    $prev_category = '';
    while ($row = mysql_fetch_assoc($result)) {
        if ($row['category'] != $prev_category) $row['new_category'] = true;

        $event_list['event_list'][] = $row;
    }

    $prev_category = $event_list['event_list'][0]['category'];
    $prev_category_key = 0;
    $event_count = 1;
    for ($i = 1; $i < count($event_list['event_list']); $i++) {
//        error_log("prev category: '$prev_category', current: '". $event_list['event_list'][$i]['category'] . "'");

        if (0 != strcmp($event_list['event_list'][$i]['category'], $prev_category) ) {
//            error_log("    different, event_count is: $event_count");

            $event_list['event_list'][$prev_category_key]['event_count'] = $event_count;
            $event_list['event_list'][$prev_category_key]['new_category'] = true;

            $prev_category = $event_list['event_list'][$i]['category'];
            $prev_category_key = $i;
            $event_count = 0;
        }
        $event_list['event_list'][$i]['new_category'] = false;
        $event_list['event_list'][$i]['new_column'] = false;

        if ($i == (count($event_list['event_list']) / 2) - 1) {
            $event_list['event_list'][$i]['new_column'] = true;
        }

        $event_count++;
    }
    $event_list['event_list'][$prev_category_key]['event_count'] = $event_count;
    $event_list['event_list'][$prev_category_key]['new_category'] = true;


    return $event_list;
}

function get_event_instructions_html($wildlife_db, $species_id, $expert_only) {
    if ($species_id > 3)  return "<p>Species unknown for video. No instructions available.</p>";

    $event_list = get_event_list($wildlife_db, $species_id, $expert_only);

    $instructions_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/event_instructions_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($instructions_template, $event_list);
}


function get_event_dropdown_html($wildlife_db, $species_id, $expert_only, $video_id) {
    if ($species_id > 3) return "<p>Species unknown for video. No events available.</p>";

    $event_list = get_event_list($wildlife_db, $species_id, $expert_only);

    $event_list['video_id'] = $video_id;
    $event_dropdown_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/event_dropdown_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($event_dropdown_template, $event_list);
}

?>

