<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Review Reported Videos</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='review_reported_videos.js'></script>

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

.bottom-up {top: auto; bottom: 100%; }
.dropdown-menu.bottom-up:before { border-bottom: 0px solid transparent !important; border-top: 7px solid rgba(0, 0, 0, 0.2); top: auto !important; bottom: -7px; }
.dropdown-menu.bottom-up:after  { border-bottom: 0px solid transparent !important; border-top: 6px solid white;              top: auto !important; bottom: -6px; }
    </style>
";

$user = get_logged_in_user();
$user_id = $user->id;
$user_name = $user->name;

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
                    'preferences' => 'active',
                    'about_wildlife' => '',
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

if (strlen($special_user) > 0 && $special_user{6} == 1) {
    /*
    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 5px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>";

    echo "<p> SPECIAL user: " . $row['special_user'] . "</p>";

    echo "          </div>
                </div>
            </div>
        </div>";
     */
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

    print_footer();
    echo "</body></html>";
    die();
}


$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$result = mysql_query("SELECT count(*) FROM video_segment_2 WHERE report_status = 'REPORTED'", $wildlife_db);
$row = mysql_fetch_assoc($result);
$reported_count = $row['count(*)'];

$result = mysql_query("SELECT count(*) FROM video_segment_2 WHERE report_status = 'REVIEWED'", $wildlife_db);
$row = mysql_fetch_assoc($result);
$reviewed_count = $row['count(*)'];

echo "
<div class='row-fluid'>
    <div class='well well-small' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 0px;'>
        <div class='container'>
            <div class='span12' style='margin-left: 0px;'>";

//echo "<div class='span5'>";
echo "<p> $reviewed_count total videos segments have been reviewed. $reported_count video segments are waiting for review. </p>";
//echo "</div>";
//echo "<div class='span5'>";
//echo "<div class='progress'> <div class='bar bar-success' style='width:" .floor(100.0 * $reported_count / ($reported_count + $reviewed_count)) . "%;'> </div> </div>";
//echo "</div>";

echo "
            </div>
        </div>
    </div>
</div>";

echo "<div id='videos-placeholder'></div>";
echo "<div id='videos-nav-placeholder'></div>";

print_footer();

echo "
</body>
</html>
";

?>
