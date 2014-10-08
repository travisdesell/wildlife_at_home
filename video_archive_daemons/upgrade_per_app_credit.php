<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$user_result = query_boinc_db("SELECT id, name, teamid, dna_total_credit FROM user where dna_linked = 0 AND dna_total_credit > 0");

while ($user_row = $user_result->fetch_assoc()) {
    $userid = $user_row['id'];
    $username = $user_row['name'];
    $teamid = $user_row['teamid'];

    $credit_to_add = $user_row['dna_total_credit'];
    $project = "dna";

    $appid = 13;

    if ($teamid > 0) {
        error_log("[$username - link accounts] had teamid: $teamid");

        $tu_result = query_boinc_db("SELECT total FROM credit_team WHERE teamid = $teamid AND appid = $appid");
        $tu_row = $tu_result->fetch_assoc();

        if ($tu_row) {
            error_log("[$username - link accounts] found credit_team row");
            query_boinc_db("UPDATE credit_team SET total = total + $credit_to_add WHERE teamid = $teamid AND appid = $appid");
        } else {
            error_log("[$username - link accounts] did not find credit_team row");
            query_boinc_db("INSERT INTO credit_team SET total = $credit_to_add, teamid = $teamid, appid = $appid, njobs = 0, expavg = 0, credit_type = 0");
        }

        query_boinc_db("UPDATE team SET total_credit = total_credit + $credit_to_add WHERE id = $teamid");
    } else {
        error_log("[$username - link accounts] Did not find team");
    }

    $cu_result = query_boinc_db("SELECT total FROM credit_user WHERE userid = $userid AND appid = $appid");
    $cu_row = $cu_result->fetch_assoc();

    if ($cu_row) {
        error_log("[$username - link accounts] found credit_user row");
        query_boinc_db("UPDATE credit_user SET total = total + $credit_to_add WHERE userid = $userid AND appid = $appid");
    } else {
        error_log("[$username - link accounts] did not find credit_user row");
        query_boinc_db("INSERT INTO credit_user SET total = $credit_to_add, userid = $userid, appid = $appid, njobs = 0, expavg = 0, credit_type = 0");
    }
    query_boinc_db("UPDATE user SET total_credit = total_credit + $credit_to_add, " . $project . "_linked = 1 WHERE id = $userid");
}


/*
$user_result = query_boinc_db("SELECT id, name, email_addr, teamid, dna_userid FROM user WHERE dna_linked = 1");

while ($user_row = $user_result->fetch_assoc()) {
    $user_id = $user_row['id'];
    if ($user_id != 149) continue;

    $user_name = $user_row['name'];
    $user_email = $user_row['email_addr'];
    $user_team = $user_row['teamid'];
    $dna_userid = $user_row['dna_userid'];

    echo "$user_id $user_name $user_email $user_team \n";

    $dna_user_result = query_dna_db("SELECT id, name, total_credit FROM user WHERE id = $dna_userid");
    $dna_user_row = $dna_user_result->fetch_assoc();
    if ($dna_user_row) {
        $dna_id = $dna_user_row['id'];
        $dna_name = $dna_user_row['name'];
        $dna_total_credit = $dna_user_row['total_credit'];
        echo " -- matched $dna_id, $dna_name, $dna_total_credit\n";

        $per_app_result = query_boinc_db("SELECT * FROM credit_user WHERE userid = $user_id AND appid = 13");
        $per_app_row = $per_app_result->fetch_assoc();
        if ($per_app_row) {
            query_boinc_db("UPDATE credit_user SET total = total + $dna_total_credit WHERE userid = $user_id AND appid = 13");
        } else {
            query_boinc_db("INSERT INTO credit_user SET userid = $user_id, appid = 13, njobs = 0, total=$dna_total_credit, expavg=0, credit_type = 0");
        }

        if ($user_team > 0) {
            $team_result = query_boinc_db("SELECT * FROM credit_team WHERE teamid = $user_team AND appid = 13");
            $team_row = $team_result->fetch_assoc();

            if ($team_row) {
                query_boinc_db("UPDATE credit_team SET total = total + $dna_total_credit WHERE teamid = $user_team AND appid = 13");
            } else {
                query_boinc_db("INSERT INTO credit_team SET teamid = $user_team, appid = 13, njobs = 0, total= $dna_total_credit, expavg=0, credit_type = 0");
            }
        }       

    } else {
        die (" -- couldn't match an account!");
    }
}
 */
?>
