<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');

function attempt_query_with_ping($sql, &$db) {
    if (!mysql_ping($db)) {
        if (!$db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd) ) {
            trigger_error("Database not available: " . mysql_error($db));
            return FALSE;
        }
    }

    $result = mysql_query($sql, $db);
    return $result;
}
?>
