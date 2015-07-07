<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../../citizen_science_grid/my_query.php");

//require $cwd[__FILE__] . '/../mustache.php/src/Mustache/Autoloader.php';
//Mustache_Autoloader::register();

//$species_id = $boinc_db->real_escape_string($_POST['species_id']);
//$expert_only = $boinc_db->real_escape_string($_POST['expert_only']);

function get_event_instructions_html($species_id, $expert_only, $modal = 1) {
    global $cwd;

    if ($species_id > 4)  {
        echo "<p>Species unknown for video. No instructions available.</p>";
        die();
    }

    $query ="";
    if ($species_id < 1) {
        $query = "SELECT id, category, name, instructions FROM observation_types WHERE expert_only = $expert_only ORDER BY category, id";
    } else {
        $query = "SELECT id, category, name, instructions FROM observation_types WHERE expert_only = $expert_only AND ";
        if ($species_id == 1) { //sharptailed grouse
            $query .= "sharptailed_grouse = 1";
        } else if ($species_id == 2) { //least tern
            $query .= "least_tern = 1";
        } else if ($species_id == 3) { //piping plover
            $query .= "piping_plover = 1";
        } else if ($species_id == 4) { //blue winged teal
            $query .= "sharptailed_grouse = 1";
        } else {
            return;
        }
        $query .= " ORDER BY category, id";
    }

    $result = query_wildlife_video_db($query);

    $event_list['event_list'] = array();
    $prev_category = '';
    while ($row = $result->fetch_assoc()) {
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

    $event_list['modal'] = $modal;

    $instructions_template = file_get_contents($cwd[__FILE__] . "/../templates/event_instructions_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($instructions_template, $event_list);
}

?>

