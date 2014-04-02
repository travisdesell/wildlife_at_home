<?php

require_once("../webpage/wildlife_db.php");
require_once("../webpage/boinc_db.php");

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);


$query = "SELECT id, name FROM user";
$results = mysql_query($query, $boinc_db);
if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

while ($row = mysql_fetch_assoc($results)) {
    $user_id = $row['id'];
    $user_name = $row['name'];


    $obs_query = "SELECT count(*) FROM timed_observations WHERE user_id = $user_id";
    $obs_results = mysql_query($obs_query, $wildlife_db);
    if (!$obs_results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $obs_query\n");
    $obs_row = mysql_fetch_assoc($obs_results);

    echo "$user_id - $user_name - " . $obs_row['count(*)'] . "\n";

    $user_query = "UPDATE user SET total_events = " . $obs_row['count(*)'] . " WHERE id = $user_id";
    $user_results = mysql_query($user_query, $boinc_db);
    if (!$user_results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $user_query\n");
}

?>
