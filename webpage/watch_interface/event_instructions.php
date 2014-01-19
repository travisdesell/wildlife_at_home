<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');

//require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
//Mustache_Autoloader::register();

//$species_id = mysql_real_escape_string($_POST['species_id']);
//$expert_only = mysql_real_escape_string($_POST['expert_only']);

function get_event_instructions_html($species_id, $expert_only) {
    global $wildlife_user, $wildlife_passwd, $wildlife_db, $cwd;

    if ($species_id > 3)  {
        echo "<p>Species unknown for video. No instructions available.</p>";
        die();
    }

    if ($wildlife_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
        mysql_select_db("wildlife_video", $wildlife_db);
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

    $instructions_template = file_get_contents($cwd . "/templates/event_instructions_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($instructions_template, $event_list);
}

?>

