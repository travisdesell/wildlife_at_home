<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

function get_tag_dropdowns() {
    global $wildlife_user, $wildlife_passwd, $wildlife_db, $cwd;

    if ($wildlife_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
        mysql_select_db("wildlife_video", $wildlife_db);
    }

    $query = "SELECT id, category, name, possible_tags FROM observation_types WHERE possible_tags IS NOT NULL AND possible_tags != ''";
    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    }

    $tag_dropdowns = array();
    while ( $row = mysql_fetch_assoc($result) ) {
        $row['event_id'] = $row['id'];

        $row['observation_id'] = -1;
        if ($row['possible_tags'] != null && $row['possible_tags'] != '') {
            $row['has_tags'] = true;

            $tag_id = 0;
            $possible_tags = explode(", ", $row['possible_tags']);
            $tags = array();
            foreach ($possible_tags as $tag) {
                $current_tag['tag_name'] = $tag;
                $current_tag['tag_id'] = $tag_id;

                $tags[] = $current_tag;
                $tag_id++;
            }

            $row['possible_tags'] = $tags;

            $watch_interface_template = file_get_contents($cwd . "/tag_row_template.html");
            $mustache_engine = new Mustache_Engine;
            $tag_dropdowns[$row['id']] = $mustache_engine->render($watch_interface_template, $row);

//            echo ( $mustache_engine->render($watch_interface_template, $row) );
        }
    }

    return $tag_dropdowns;
}

echo json_encode( get_tag_dropdowns() );

?>
