<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = dirname(readlink($cwd));
else $cwd = dirname($cwd);

require_once($cwd . '/boinc_db.php');
require_once($cwd . '/my_query.php');

$g_logged_in_user__fixme = null;
$got_logged_in_user__fixme = false;

function url_tokens__fixme($auth) {
    $now = time();
    $ttok = md5((string)$now.$auth);
    return "&amp;tnow=$now&amp;ttok=$ttok";
}

function get_user_from_id__fixme($id) {
    global $boinc_user, $boinc_passwd, $boinc_db;

    if ($boinc_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
        mysql_select_db("wildlife", $boinc_db);
    }

    $result = attempt_query_with_ping("SELECT * FROM user WHERE id = '$id'", $boinc_db);
    return mysql_fetch_assoc($result);
}

function get_user($must_be_logged_in = true) {
    global $g_logged_in_user__fixme, $got_logged_in_user__fixme, $boinc_user, $boinc_passwd, $boinc_db;

    if ($got_logged_in_user__fixme) return $g_logged_in_user__fixme;

    $authenticator = null;
    if (isset($_COOKIE['auth'])) $authenticator = $_COOKIE['auth'];

    if ($boinc_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
        mysql_select_db("wildlife", $boinc_db);
    }

    $authenticator = mysql_real_escape_string($authenticator);
    if ($authenticator) {
        $result = attempt_query_with_ping("SELECT * FROM user WHERE authenticator = '$authenticator'", $boinc_db);
        $g_logged_in_user__fixme = mysql_fetch_assoc($result);
    }

    if ($must_be_logged_in && !$g_logged_in_user__fixme) {
        $next_url = '';
        if (array_key_exists('REQUEST_URI', $_SERVER)) {
            $next_url = $_SERVER['REQUEST_URI'];
            $n = strrpos($next_url, "/");
            if ($n) {
                $next_url = substr($next_url, $n+1);
            }
        }

        $next_url = urlencode($next_url);
        Header("Location: login_form.php?next_url=$next_url");
        exit;
    }

    $got_logged_in_user__fixme = true;
    return $g_logged_in_user__fixme;
}


function is_special_user__fixme($user = null, $must_be_logged_in = true) {
    global $boinc_user, $boinc_passwd, $boinc_db;

    if ($user == null) {
        if ($must_be_logged_in) {
            $user = get_user($must_be_logged_in);
        } else {
            return 0;
        }
    }

    if ($boinc_db == null) {
        ini_set("mysql.connect_timeout", 300);
        ini_set("default_socket_timeout", 300);

        $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
        mysql_select_db("wildlife", $boinc_db);
    }

    $query = "SELECT special_user FROM forum_preferences WHERE userid=" . $user['id'];
    $result = attempt_query_with_ping($query, $boinc_db);
    if (!$result) {
        error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");
        die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");
    }

    $row = mysql_fetch_assoc($result);
    $special_user = $row['special_user'];

    if ($special_user == null) {
        return 0;
    } else if (strlen($special_user) <= 6) {
        return 0;
    } else if ($special_user{6} == 1) {
        return 1;
    } else {
        return 0;
    }
}

?>
