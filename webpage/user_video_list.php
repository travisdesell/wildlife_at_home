<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Watched Videos</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='user_video_list.js'></script>

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

.bottom-up {top: auto; bottom: 100%; }
.dropdown-menu.bottom-up:before { border-bottom: 0px solid transparent !important; border-top: 7px solid rgba(0, 0, 0, 0.2); top: auto !important; bottom: -7px; }
.dropdown-menu.bottom-up:after  { border-bottom: 0px solid transparent !important; border-top: 6px solid white;              top: auto !important; bottom: -6px; }
    </style>
";

$user = get_logged_in_user();
$user_id = $user->id;

echo "<script type='text/javascript'>
    var user_id = $user_id; 
    var filter = '$filter';
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

echo "
    <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
        <div class='row-fluid'>
            <div class='container'>
                <div class='span12'>
                <ul class='nav nav-pills' style='margin-top:0px; margin-bottom: 0px; padding-top: 0px; padding-bottom: 8px'>
                        <li class='nav-li' id='invalid-nav-pill'><a href='#'>Invalid</a></li>
                        <li class='nav-li' id='interesting-nav-pill'><a href='#'>Interesting</a></li>
                        <li class='nav-li' id='bird-presence-nav-pill'><a href='#'>Bird Presence</a></li>
                        <li class='nav-li' id='chick-presence-nav-pill'><a href='#'>Chick Presence</a></li>
                        <li class='nav-li' id='predator-presence-nav-pill'><a href='#'>Predator Presence</a></li>
                        <li class='nav-li' id='nest-defense-nav-pill'><a href='#'>Nest Defense</a></li>
                        <li class='nav-li' id='nest-success-nav-pill'><a href='#'>Nest Success</a></li>
                        <li class='nav-li' id='bird-leave-nav-pill'><a href='#'>Bird Leave</a></li>
                        <li class='nav-li' id='bird-return-nav-pill'><a href='#'>Bird Return</a></li>
                    </ul>
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
