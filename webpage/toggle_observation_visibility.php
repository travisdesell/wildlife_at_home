<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

$observation_id = mysql_real_escape_string($_POST['observation_id']);
$user = get_user();
$user_id = $user['id'];

//error_log("TOGGLING OBSERVATION -- observation_id: $observation_id, user_id: $user_id");

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "UPDATE observations SET hidden = !hidden WHERE id = $observation_id AND user_id = $user_id";
//error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$query = "SELECT hidden FROM observations WHERE id = $observation_id AND user_id = $user_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

echo json_encode(mysql_fetch_assoc($result));


?>
