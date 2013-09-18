<?php

require_once('/projects/wildlife/html/inc/util.inc');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');

function is_special_user($user_id = null, $boinc_db = null) {
    global $boinc_user, $boinc_passwd;
    if ($user_id == null) {
        $user = get_logged_in_user();
        $user_id = $user->id;
        $user_name = $user->name;
    }

    if ($boinc_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
        mysql_select_db("wildlife", $boinc_db);
    }

    $result = mysql_query("SELECT special_user FROM forum_preferences WHERE userid=$user_id", $boinc_db);
    $row = mysql_fetch_assoc($result);

    $special_user = $row['special_user'];

    return (strlen($special_user) > 6 && $special_user{6} == 1);
}
?>
