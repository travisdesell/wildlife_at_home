<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname(dirname($cwd));

//require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
//Mustache_Autoloader::register();

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

$observation_id = mysql_real_escape_string($_POST['observation_id']);
$response_comments = mysql_real_escape_string($_POST['response_comments']);
$status = mysql_real_escape_string($_POST['validation_status']);

$user = get_user();
$responder_id = $user['id'];
$responder_name = $user['name'];

if (!is_special_user__fixme($user, true)) return;

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);


/**
 *  If event swapped between invalid, unvalidated, valid then change valid event count for user
 */

$query = "UPDATE timed_observations SET response_comments = '$response_comments', report_status = 'RESPONDED', responder_id = $responder_id, responder_name = '$responder_name', status = '$status' WHERE id = $observation_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

?>
