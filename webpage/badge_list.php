<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/navbar.php');
require_once($cwd . '/footer.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/display_badges.php');

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Badge Descriptions</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

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
    </style>
";


echo "
</head>
<body>";


$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => 'active'
                );

print_navbar($active_items);

echo "<div class='well well-small' style='padding-top:10px; padding-bottom:10px;'>";
echo "<div class='container'>";
echo "<div class='span12' style='margin-left:0px'>";

echo "<p style='margin-top:5px; margin-bottom:5px;'>You can earn different badges for watching video or volunteering your computer to crunch workunits.  This page describes the different badges and how you can unlock them.</p>";
echo "</div>";
echo "</div>";
echo "</div>";

print_badge_table();

print_footer();

echo "
</body>
</html>
";

?>
