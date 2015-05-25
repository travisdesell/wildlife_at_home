<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

//require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
//Mustache_Autoloader::register();

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

$observation_id = $boinc_db->real_escape_string($_POST['observation_id']);
$comments = $boinc_db->real_escape_string($_POST['comments']);

$user = get_user();
$reporter_id = $user['id'];
$reporter_name = $user['name'];


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


$query = "UPDATE timed_observations SET report_comments = '$comments', report_status = 'REPORTED', reporter_id = $reporter_id, reporter_name = '$reporter_name' WHERE id = $observation_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

/*
$response = "USER $reporter_id REPORTING OBSERVATION $observation_id WITH COMMENTS '$comments'";
error_log($response);

echo $response;
 */
?>
