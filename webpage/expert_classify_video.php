<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/special_user.php');

$user = get_logged_in_user();
$user_id = $user->id;
$user_name = $user->name;

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Expert Video Classification</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='expert_classify_video.js'></script>

    <style>
    body {
        padding-top: 60px;
    }

    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
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

.accordion-body.in { overflow:visible; }

.bottom-up {top: auto; bottom: 100%; }
.dropdown-menu.bottom-up:before { border-bottom: 0px solid transparent !important; border-top: 7px solid rgba(0, 0, 0, 0.2); top: auto !important; bottom: -7px; }
.dropdown-menu.bottom-up:after  { border-bottom: 0px solid transparent !important; border-top: 6px solid white;              top: auto !important; bottom: -6px; }

.navbar .dropdown-menu [class*='span'] {
    padding-top: 10px;
}
.navbar .dropdown-menu > li.column-menu ul {
    display: inline-block;
    list-style: none outside none;
    list-style-type: none outside none;
    margin: 0 0 16px;
    width: 100%;
}
.navbar .dropdown-menu > li.column-menu li {
    display: inline;
    list-style-type: none outside none;
    float: left;
    width: 100%;
}
.navbar .dropdown-menu > li.column-menu.firstcolumn {
    margin-left: 0;
    padding-left: 0;
}

    </style>
";

echo "<script type='text/javascript'>
    var user_id = $user_id; 
    var user_name = '$user_name';
</script>";

echo "
</head>
<body>";


$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'project_management' => 'active',
                    'community' => ''
                );



print_navbar($active_items);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$result = mysql_query("SELECT special_user FROM forum_preferences WHERE userid=$user_id", $boinc_db);
$row = mysql_fetch_assoc($result);

$special_user = $row['special_user'];

if (is_special_user($user_id, $boinc_db)) {
    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 5px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>";

//    echo "<p> SPECIAL user: " . $row['special_user'] . "</p>";

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    $result = attempt_query_with_ping("SELECT count(*) FROM video_2", $wildlife_db);
    $row = mysql_fetch_assoc($result);
    $video_count = $row['count(*)'];

    /*
    $result = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE ogv_generated = true", $wildlife_db);
    $row = mysql_fetch_assoc($result);
    $ogv_generated_count = $row['count(*)'];
     */

    $result = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE processing_status = 'WATERMARKED'", $wildlife_db);
    $row = mysql_fetch_assoc($result);
    $watermarked_video_count = $row['count(*)'];

    $result = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE processing_status = 'SPLIT'", $wildlife_db);
    $row = mysql_fetch_assoc($result);
    $split_video_count = $row['count(*)'];

    $result = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE expert_finished = 'FINISHED'", $wildlife_db);
    $row = mysql_fetch_assoc($result);
    $finished_video_count = $row['count(*)'];

    echo "<div class='span4'>";
    echo "<p> " . $finished_video_count . " of $video_count videos with completed expert observation.</p>";
    echo "<div class='progress'> <div class='bar bar-success' style='width:" .floor(100.0 * $finished_video_count / $video_count) . "%;'> </div> </div>";
    echo "</div>";

    /*
    echo "<div class='span3'>";
    echo "<p> " . $ogv_generated_count . " of " . ($watermarked_video_count + $split_video_count) . " watermarked videos have ogv generated for firefox.</p>";
    echo "<div class='progress'> <div class='bar bar-success' style='width:" .floor(100.0 * $ogv_generated_count / ($watermarked_video_count + $split_video_count)) . "%;'> </div> </div>";
    echo "</div>";
     */

    echo "<div class='span4'>";
    echo "<p> " . ($watermarked_video_count + $split_video_count) . " of $video_count videos availble for expert observation.</p>";
    echo "<div class='progress'> <div class='bar bar-warning' style='width:" .floor(100.0 * ($watermarked_video_count + $split_video_count) / $video_count) . "%;'> </div> </div>";
    echo "</div>";

    echo "<div class='span4'>";
    echo "<p> $split_video_count of $video_count videos availble for volunteer observation.</p>";
    echo "<div class='progress'> <div class='bar bar-info' style='width:" .floor(100.0 * $split_video_count / $video_count) . "%;'> </div> </div>";
    echo "</div>";

    echo "
                    </div> <!--span12 -->
                </div> <!--container-->
            </div> <!--row-fluid-->
        </div> <!--well-->";

    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 5px; margin-top: 5px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>";

    echo "
            <div class='btn-group'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='year-button'>
                    Year <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li><a href='javascript:;' class='year-dropdown' year='' id='any-year-dropdown'>Any Year</a></li>
                    <li><a href='javascript:;' class='year-dropdown' year='2011' id='year-2011-dropdown'>2011</a></li>
                    <li><a href='javascript:;' class='year-dropdown' year='2012' id='year-2012-dropdown'>2012</a></li>
                    <li><a href='javascript:;' class='year-dropdown' year='2013' id='year-2013-dropdown'>2013</a></li>
                </ul>
            </div>

            <div class='btn-group'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='species-button'>
                    Species <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li><a href='javascript:;' class='species-dropdown' species_id='0' id='any-species-dropdown'>Any Species</a></li>
                    <li><a href='javascript:;' class='species-dropdown' species_id='1' id='grouse-dropdown'>Sharp-tailed Grouse</a></li>
                    <li><a href='javascript:;' class='species-dropdown' species_id='2' id='tern-dropdown'>Interior Least Tern</a></li>
                    <li><a href='javascript:;' class='species-dropdown' species_id='3' id='plover-dropdown'>Piping Plover</a></li>
                </ul>
            </div>

            <div class='btn-group'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='location-button'>
                    Location <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li><a href='javascript:;' class='location-dropdown' location_id='0' id='any-location-dropdown'>Any Location</a></li>
                    <li><a href='javascript:;' class='location-dropdown' location_id='1' id='belden-dropdown'>Belden, ND</a></li>
                    <li><a href='javascript:;' class='location-dropdown' location_id='2' id='blaisdell-dropdown'>Blaisdell, ND</a></li>
                    <li><a href='javascript:;' class='location-dropdown' location_id='3' id='lostwood-dropdown'>Lostwood Wildlife Refuge, ND</a></li>
                    <li><a href='javascript:;' class='location-dropdown' location_id='4' id='missouri-river-dropdown'>Missouri River, ND</a></li>
                </ul>
            </div>

            <div class='btn-group'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='animal-id-button'>
                    Animal ID <span class='caret'></span>
                </button>
                <ul class='dropdown-menu' id='animal-id-dropdown-menu'>
                </ul>
            </div>

            <div class='btn-group pull-right'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='status-button'>
                    Status <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li><a href='javascript:;' class='status-dropdown' video_status='' id='any-status-dropdown'>Any Status</a></li>
                    <li><a href='javascript:;' class='status-dropdown' video_status='UNWATCHED' id='unwatched-dropdown'><button class='btn btn-mini pull-right'>&#x2713;</button> Unwatched</a></li>
                    <li><a href='javascript:;' class='status-dropdown' video_status='WATCHED' id='watched-dropdown'><button class='btn btn-mini btn-primary pull-right'>&#x2713;</button> Watched</a></li>
                    <li><a href='javascript:;' class='status-dropdown' video_status='FINISHED' id='finished-dropdown'><button class='btn btn-mini btn-success pull-right'>&#x2713;</button> Finished</a></li>
                </ul>
            </div>

            <div class='btn-group pull-right'>
                <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='release-button'>
                    Released <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                    <li><a href='javascript:;' class='release-dropdown' release_to_public='' id='any-release-dropdown'>Any</a></li>
                    <li><a href='javascript:;' class='release-dropdown' release_to_public='false' id='private-release-dropdown'>Private</a></li>
                    <li><a href='javascript:;' class='release-dropdown' release_to_public='true' id='public-release-dropdown'>Public</a></li>
                </ul>
            </div>


        ";

    echo "
                    </div> <!--span12 -->
                </div> <!--container-->
            </div> <!--row-fluid-->
        </div> <!--well-->";

    echo "<div id='video-list-placeholder'></div>";
    echo "<div id='videos-nav-placeholder'></div>";

} else {
    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>
                        <p> Sorry, this page is only accessible for project scientists.</p>
                    </div>
                </div>
            </div>
        </div>";
}


print_footer();

echo "
</body>
</html>
";

?>
