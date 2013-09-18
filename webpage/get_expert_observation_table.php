<?php

require_once('/projects/wildlife/html/inc/util.inc');

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

    $observation_table_template= file_get_contents("/home/tdesell/wildlife_at_home/webpage/expert_observation_table_template.html");
    $mustache_engine = new Mustache_Engine;
    return $mustache_engine->render($observation_table_template, $observations);
}

?>
