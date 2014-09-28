<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$user_result = query_boinc_db("SELECT id, name, email_addr, teamid, subset_sum_linked, subset_sum_userid, dna_linked, dna_userid FROM user WHERE subset_sum_linked = 1 OR dna_linked = 1");

while ($user_row = $user_result->fetch_assoc()) {
    $user_id = $user_row['id'];
    $user_name = $user_row['name'];
    $user_email = $user_row['email_addr'];
    $user_team = $user_row['teamid'];
    $sss_userid = $user_row['subset_sum_userid'];
    $dna_userid = $user_row['dna_userid'];

    if ($user_row['subset_sum_linked'] == 1) {
        $per_app_result = query_boinc_db("SELECT * FROM credit_user WHERE userid = $user_id AND appid = 15");
        $per_app_row = $per_app_result->fetch_assoc();
        if (!$per_app_row) {
            echo "SSS CREDIT_USER DOES NOT EXIST FOR: $user_id $user_name $user_email $user_team \n";

            $sss_user_result = query_subset_sum_db("SELECT id, name, total_credit FROM user WHERE id = $sss_userid");
            $sss_user_row = $sss_user_result->fetch_assoc();
            if ($sss_user_row) {
                $sss_id = $sss_user_row['id'];
                $sss_name = $sss_user_row['name'];
                $sss_total_credit = $sss_user_row['total_credit'];
                echo " -- matched $sss_id, $sss_name, $sss_total_credit\n";
            }
        }
    }

    if ($user_row['dna_linked'] == 1) {
        $per_app_result = query_boinc_db("SELECT * FROM credit_user WHERE userid = $user_id AND appid = 13");
        $per_app_row = $per_app_result->fetch_assoc();
        if (!$per_app_row) {
            echo "DNA CREDIT_USER DOES NOT EXIST FOR: $user_id $user_name $user_email $user_team \n";

            $dna_user_result = query_dna_db("SELECT id, name, total_credit FROM user WHERE id = $dna_userid");
            $dna_user_row = $dna_user_result->fetch_assoc();
            if ($dna_user_row) {
                $dna_id = $dna_user_row['id'];
                $dna_name = $dna_user_row['name'];
                $dna_total_credit = $dna_user_row['total_credit'];
                echo " -- matched $dna_id, $dna_name, $dna_total_credit\n";
            }
         }
    }

    /*
    $sss_user_result = query_subset_sum_db("SELECT id, name, total_credit FROM user WHERE id = $sss_userid");
    $sss_user_row = $sss_user_result->fetch_assoc();
    if ($sss_user_row) {
        $sss_id = $sss_user_row['id'];
        $sss_name = $sss_user_row['name'];
        $sss_total_credit = $sss_user_row['total_credit'];
        echo " -- matched $sss_id, $sss_name, $sss_total_credit\n";

        $per_app_result = query_boinc_db("SELECT * FROM credit_user WHERE userid = $user_id AND appid = 15");
        $per_app_row = $per_app_result->fetch_assoc();
        if ($per_app_row) {
            query_boinc_db("UPDATE credit_user SET total = total + $sss_total_credit WHERE userid = $user_id AND appid = 15");
        } else {
            query_boinc_db("INSERT INTO credit_user SET userid = $user_id, appid = 15, njobs = 0, total=$sss_total_credit, expavg=0, credit_type = 0");
        }

        if ($user_team > 0) {
            $team_result = query_boinc_db("SELECT * FROM credit_team WHERE teamid = $user_team AND appid = 15");
            $team_row = $team_result->fetch_assoc();

            if ($team_row) {
                query_boinc_db("UPDATE credit_team SET total = total + $sss_total_credit WHERE teamid = $user_team AND appid = 15");
            } else {
                query_boinc_db("INSERT INTO credit_team SET teamid = $user_team, appid = 15, njobs = 0, total= $sss_total_credit, expavg=0, credit_type = 0");
            }
        }       

    } else {
        die (" -- couldn't match an account!");
    }
     */
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
