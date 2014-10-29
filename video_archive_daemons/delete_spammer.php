<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

if (count($argv) != 2) {
    die("Error, invalid arguments. usage: php $argv[0] <user id to delete>\n");
}

$min_id = $argv[1];

$user_result = query_boinc_db("SELECT id, name, total_events, total_credit FROM user WHERE id = $min_id");

$user_row = $user_result->fetch_assoc();
$user_id = $user_row['id'];
$user_name = $user_row['name'];
$user_credit = $user_row['total_credit'];
$user_events = $user_row['total_events'];

if ($user_credit > 0 || $user_events > 0) continue;

echo "delete user '$user_name' - total_events: $user_events, total_credit: $user_credit, user_id: $user_id (y/n)? ";


while (FALSE !== ($line = fgets(STDIN))) {
    if ($line[0] == 'y' || $line[0] == 'Y') {
        echo "deleting user.\n";
        break;
    }
    else {
        die("not deleting user '$user_name'\n");
    }
}

//delete user from database
query_boinc_db("DELETE FROM user WHERE id = $user_id");

//delete user forum threads from database
query_boinc_db("DELETE FROM thread WHERE owner = $user_id");

//delete user forum posts from database
query_boinc_db("DELETE FROM post WHERE user = $user_id");


//update forum thread count
query_boinc_db("update forum as fo set threads = (SELECT count(*) FROM thread WHERE forum = fo.id)");

//update forum post count
query_boinc_db("update forum as fo set posts = (SELECT COUNT(*) FROM post as po, thread as tr WHERE po.thread = tr.id AND tr.forum = fo.id)");

?>
