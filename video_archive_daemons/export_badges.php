#!/usr/bin/env php

<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/display_badges.php');

echo "running export_badges.php at " . date('Y/m/d h:i:s a') . "\n";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$result = attempt_query_with_ping("SELECT id, name, email_addr, total_credit, bossa_total_credit, cross_project_id FROM user WHERE total_credit > 0 OR bossa_total_credit > 0", $boinc_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");

$file = fopen("/projects/wildlife/download/badges.xml", "w");

fwrite($file, "<users>\n");
while ( ($row = mysql_fetch_assoc($result)) != null) {
    $user->id = $row['id'];
    $user->bossa_total_credit = $row['bossa_total_credit'];
    $user->total_credit = $row['total_credit'];
    $user->cross_project_id = $row['cross_project_id'];
    $user->email_addr= $row['email_addr'];

    $cpid = md5($user->cross_project_id . $user->email_addr);

    fwrite($file, "<user>\n");
    fwrite($file, "\t<id>" . $user->id . "</id>\n");
    fwrite($file, "\t<cpid>" . $cpid . "</cpid>\n");
    fwrite($file, "\t<credit>" . $user->total_credit . "</credit>\n");
    fwrite($file, "\t<bossa_credit>" . ($user->bossa_total_credit + $user->bossa_credit_v2) . "</bossa_credit>\n");


    $credit_badge = get_credit_badge_str($user);
    $video_badge = get_bossa_badge_str($user);

    if ($credit_badge != "") {
        fwrite($file, "\t<credit_badge>" . $credit_badge . "</credit_badge>\n");
    }

    if ($video_badge != "") {
        fwrite($file, "\t<video_badge>" . $video_badge . "</video_badge>\n");
    }

    fwrite($file, "</user>\n");
}

fwrite($file, "</users>\n");

?>
