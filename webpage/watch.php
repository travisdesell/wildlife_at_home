<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/display_badges.php');
require_once($cwd . '/navbar.php');
require_once($cwd . '/footer.php');
require_once($cwd . '/boinc_db.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');
require_once($cwd . '/watch_interface/observation_table.php');
require_once($cwd . '/watch_interface/event_instructions.php');

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");

$user = get_user();
$user_id = $user['id'];

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Watching Video</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='timed_observations.js'></script>

    <style>
    .tooltip > .tooltip-inner {
        background-color: #787878;
    }

    .tooltip > .tooltip-inner > p {
        margin: 2px 2px 2px 2px;
        padding: 2px 2x 2px 2px;
    }

    .modal.large {
        width: 80%; /* respsonsive width */
        margin-left:-40%; /* width/2) */
    }

    .modal .modal-body {
        height: 80%;
        overflow-y: auto;
    }


    body {
        padding-top: 60px;
    }
    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
    }

    .default_comments_text {
        color: rgba(0,0,0,0.25);
    }

    .default_time_text {
        color: rgba(0,0,0,0.25);
    }


        .well {
           position: relative;
           margin: 15px 5px;
           padding: 39px 19px 14px;
           *padding-top: 19px;
           border: 1px solid #ddd;
           -webkit-border-radius: 4px;
           -moz-border-radius: 4px;
           border-radius: 4px; 
        }

        .tab {
           position: absolute;
           top: -1px;
           left: -1px;
           padding: 3px 7px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 0 4px 0;
           -moz-border-radius: 4px 0 4px 0;
           border-radius: 4px 0 4px 0;
        }

        .tab-right {
           position: absolute;
           top: -1px;
           right: -1px;
           padding: 0px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 0 4px 0;
           -moz-border-radius: 4px 0 4px 0;
           border-radius: 4px 0 4px 0;
        }

        .tab-bottom-right {
           position: absolute;
           bottom: -1px;
           right: -1px;
           padding: 0px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 0 4px 0;
           -moz-border-radius: 4px 0 4px 0;
           border-radius: 4px 0 4px 0;
        }

        .tab-bottom-left{
           position: absolute;
           bottom: -1px;
           left: -1px;
           padding: 3px 7px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 4px 0 0;
           -moz-border-radius: 4px 4px 0 0;
           border-radius: 4px 4px 0px 0;
        }



        .title {
            text-align: center;
           position: absolute;
           top: -1px;
           left: -1px;
           width: 100%;
           padding: 3px 0px 0px 0px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 4px 0px 0px;
           -moz-border-radius: 4px 4px 0px 0px;
           border-radius: 4px 4px 0px 0px;
        }

        .label {
            cursor: pointer;
        }
    </style>
";


ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$prefs = simplexml_load_string($user['project_prefs']);
//print_r($prefs);

$min_video_time = 0;
if (array_key_exists('minimum_video_time', $prefs)) {
//    error_log("minimum video_time exists!");
    $min_video_time = $prefs->minimum_video_time * 60;
}

$max_video_time = 5 * 60 * 60;
if (array_key_exists('maximum_video_time', $prefs)) {
//    error_log("minimum video_time exists!");
    $max_video_time = $prefs->maximum_video_time * 60;
}

//error_log("MIN VIDEO TIME IS: $min_video_time AND MAX VIDEO TIME IS: $max_video_time");

$start_time = time();

$species_id = -1;
if (array_key_exists("species", $_GET)) {
    $species_id = mysql_real_escape_string($_GET['species']);
}

$location_id = -1;
if (array_key_exists("location", $_GET)) {
    $location_id = mysql_real_escape_string($_GET['location']);
}

echo "<script type='text/javascript'>
    var user_id = $user_id; 
    var start_time = $start_time;
    var species_id = $species_id;
    var location_id = $location_id;
</script>";

echo "
</head>
<body>
";

$active_items = array(
                    'home' => '',
                    'watch_video' => 'active',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'project_management' => '',
                    'community' => ''
                );

print_navbar($active_items);

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";

if ($species_id < 0 || $species_id > 3) {
    echo "
        <div class='well well-large' style='margin-top:5px; margin-bottom:5px; padding-top:15px; padding-bottom:15px;'>
        <div class='container'>
        <div class='row'>

        <p>No species identifier, or a wrong species identifier was given.  Cannot display video.</p>

        </div> <!--row-->
        </div> <!--container-->
        </div> <!--well-->";

} else if ($location_id < 0 || $location_id > 4) {
    echo "
        <div class='well well-large' style='margin-top:5px; margin-bottom:5px; padding-top:15px; padding-bottom:15px;'>
        <div class='container'>
        <div class='row'>

        <p>No location identifier, or a wrong location identifier was given.  Cannot display video.</p>

        </div> <!--row-->
        </div> <!--container-->
        </div> <!--well-->";

} else {
    $active_video_id = json_decode( $user['active_video_id'], true );

    //get a simple hash for the location and species id, so all combinations are unique
    //this is good unless we get over 100 locations (which won't happen for awhile, if ever)
    $species_location_hash = ($location_id * 100) + $species_id;

    $new_video = true;

    if (array_key_exists($species_location_hash, $active_video_id) && $active_video_id[$species_location_hash] != 'NULL') {
        $query = "SELECT id, animal_id, watermarked_filename, start_time FROM video_2 v2 WHERE v2.id = " . $active_video_id[$species_location_hash]['video_id'];

        $result = attempt_query_with_ping($query, $wildlife_db);
        if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

        $row = mysql_fetch_assoc($result);
        $found = true;
        $new_video = false;

    } else {
        //try to get a video that has already been watched
        $query = "SELECT id, animal_id, watermarked_filename, start_time FROM video_2 v2 WHERE v2.watch_count > 0 AND v2.watch_count < v2.required_views AND v2.release_to_public = true AND v2.processing_status != 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id AND duration_s >= $min_video_time AND duration_s <= $max_video_time AND NOT EXISTS (SELECT * FROM watched_videos wv WHERE wv.video_id = v2.id AND wv.user_id = $user_id) ORDER BY RAND() limit 1";
        //echo "<!-- $query -->\n";

        $result = attempt_query_with_ping($query, $wildlife_db);
        if (!$result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        }

        $row = mysql_fetch_assoc($result);

        $found = true;
        if (!$row) {    //try again with any video (not just watched videos)
            $found = true;

            $query = "SELECT id, animal_id, watermarked_filename, start_time FROM video_2 v2 WHERE v2.watch_count < v2.required_views AND v2.release_to_public = true AND v2.processing_status != 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id AND duration_s >= $min_video_time AND duration_s <= $max_video_time AND NOT EXISTS (SELECT * FROM watched_videos wv WHERE wv.video_id = v2.id AND wv.user_id = $user_id) ORDER BY RAND() limit 1";
            //    echo "<!-- $query -->\n";

            $result = attempt_query_with_ping($query, $wildlife_db);
            if (!$result) {
                error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
                die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
            }

            $row = mysql_fetch_assoc($result);

            if (!$row) {    //try again with any video time
                $query = "SELECT id, animal_id, watermarked_filename, start_time FROM video_2 v2 WHERE v2.watch_count < v2.required_views AND v2.release_to_public = true AND v2.processing_status != 'UNWATERMARKED' AND species_id = $species_id AND location_id = $location_id AND NOT EXISTS (SELECT * FROM watched_videos wv WHERE wv.video_id = v2.id AND wv.user_id = $user_id) ORDER BY RAND() limit 1";
                //    echo "<!-- $query -->\n";

                $result = attempt_query_with_ping($query, $wildlife_db);
                if (!$result) {
                    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
                    die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
                }

                $row = mysql_fetch_assoc($result);

                if (!$row) {
                    $found = false;
                    //error_log("did not find a watched video segment 2 on second try");
                }   
            }   
        }
    }

    $video_id = $row['id'];
    $video_filename = $row['watermarked_filename'];
    $start_time = $row['start_time'];
    $animal_id = $row['animal_id'];

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
    mysql_select_db("wildlife", $boinc_db);

    if ($found && $new_video) {
        $is_special_user = is_special_user__fixme($user, true);
        $query = "INSERT INTO timed_observations SET user_id = $user_id, start_time = '', end_time = '', event_id ='', comments = '', video_id = '$video_id', species_id = $species_id, location_id = $location_id, expert = $is_special_user";
        $result = attempt_query_with_ping($query, $wildlife_db);
        if (!$result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
            die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        }

        //we added an observation for the user so increment their total events
        $user_query = "UPDATE user SET total_events = total_events + 1 WHERE id = $user_id";
        $user_result = attempt_query_with_ping($user_query, $boinc_db);
        if (!$user_result) {
            error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
            die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
        }
    }

    if ($found) {
        $active_video_id[$species_location_hash]['video_id'] = $video_id;
        if ($active_video_id[$species_location_hash]['difficulty'] == '') {
            $active_video_id[$species_location_hash]['difficulty'] = 'easy';
        }
        $active_video_id[$species_location_hash]['start_time'] = date('Y-m-d H:i:s', time());

        $user_query = "UPDATE user SET active_video_id = '" . json_encode($active_video_id) . "' WHERE id = $user_id";
        $user_result = attempt_query_with_ping($user_query, $boinc_db);
        if (!$user_result) {
            error_log("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
            die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $user_query\n");
        }
    }

    //The help accordion
    echo get_event_instructions_html($species_id, 0);

    echo "
        <div id = 'finished-modal' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='finished-modal-label'></div>

        <div class='well well-large' style='margin-top:5px; margin-bottom:5px; padding-top:40px; padding-bottom:40px;'>
        <div class='container'>
        <div class='row'>";

    if ($found) {
        echo get_watch_video_interface($species_id, $video_id, $video_filename, $animal_id, $user, $start_time, $active_video_id[$species_location_hash]['difficulty']);
    } else {
        echo "<p>No videos are currently available for the specified species and location.  You may want to adjust your settings for video lengths.</p>";
    }

    echo "
        </div> <!--row-->
        </div> <!--container-->
        </div> <!--well-->";
}

print_footer();

echo "
</body>
</html>
";

?>
