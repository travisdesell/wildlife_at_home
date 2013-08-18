<?php

require_once('/projects/wildlife/html/inc/util_basic.inc');

function bossa_award_credit($user_id, $duration_s, $start_time, $current_time) {

    $config = get_config();

    $name = parse_config($config, '<db_name>');
    $user = parse_config($config, '<db_user>');
    $passwd = parse_config($config, '<db_passwd>');
    $host = parse_config($config, '<db_host>');

//    echo "$name\n";
//    echo "$user\n";
//    echo "$passwd\n";
//    echo "$host\n";

    $volunteer_db = mysql_connect($host, $user, $passwd);
    mysql_select_db($name, $volunteer_db);

    $query = "UPDATE user SET bossa_total_credit = bossa_total_credit + " . $duration_s . " WHERE id = $user_id";
    error_log($query);
    $result = mysql_query($query, $volunteer_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($volunteer_db) . "): " . mysql_error($volunteer_db) . "\nquery: $query\n");
}

?>
