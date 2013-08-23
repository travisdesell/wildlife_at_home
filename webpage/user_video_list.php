<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

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

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$result = mysql_query("SELECT bossa_total_credit, valid_observations, invalid_observations FROM user WHERE id=$user_id", $boinc_db);
$row = mysql_fetch_assoc($result);

$bossa_total_credit = $row['bossa_total_credit'];
$valid_observations = $row['valid_observations'];
$invalid_observations = $row['invalid_observations'];

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$result = mysql_query("SELECT count(*) FROM observations WHERE user_id=$user_id", $wildlife_db);
$row = mysql_fetch_assoc($result);

$total_observations = $row['count(*)'];


function append_trinary_filter(&$filter_list, $id_name, $text_name) {
    $filter_list['filter_type'] [] = array(
            'dropdown_id' => "$id_name-filter-dropdown",
            'filter_name' => "$id_name",
            'default_text' => "$text_name - Any",
            'filter_option' => array(
                array( "dropdown_text" => "$text_name - Any",     "filter_value" => "null" ),
                array( "dropdown_text" => "$text_name - Yes",     "filter_value" => "1" ),
                array( "dropdown_text" => "$text_name - No",      "filter_value" => "-1" ),
                array( "dropdown_text" => "$text_name - Unsure",  "filter_value" => "0" )
            )
        );
}


$filter_list['filter_type'] = array();
$filter_list['filter_type'][] = array(
            'dropdown_id' => 'interesting-filter-dropdown',
            'filter_name' => 'interesting',
            'default_text' => 'Interesting - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Interesting - Any', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Interesting - Yes', 'filter_value' => '1' ),
                array( 'dropdown_text' => 'Interesting - No',  'filter_value' => '0' )
            )
        );

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'status-filter-dropdown',
            'filter_name' => 'status',
            'default_text' => 'Status - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Status - Any',         'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Status - Valid',       'filter_value' => 'VALID or CANONICAL' ),
                array( 'dropdown_text' => 'Status - Invalid',     'filter_value' => 'INVALID' ),
                array( 'dropdown_text' => 'Status - Unvalidated', 'filter_value' => 'UNVALIDATED' )
            )
        );

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'reported-filter-dropdown',
            'filter_name' => 'report_status',
            'default_text' => 'Reported - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Reported - Any', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Unreported',     'filter_value' => 'UNREPORTED' ),
                array( 'dropdown_text' => 'Reported',       'filter_value' => 'REPORTED' ),
                array( 'dropdown_text' => 'Reviewed',       'filter_value' => 'REVIEWED' )
            )
        );

$filter_list['filter_type'][] = array('divider' => true);

append_trinary_filter($filter_list, "bird_presence", "Bird Presence");
append_trinary_filter($filter_list, "bird_absence", "Bird Absence");
append_trinary_filter($filter_list, "bird_leave", "Bird Leave");
append_trinary_filter($filter_list, "bird_return", "Bird Return");
append_trinary_filter($filter_list, "predator_presence", "Predator");
append_trinary_filter($filter_list, "nest_defense", "Nest Defense");
append_trinary_filter($filter_list, "nest_success", "Nest Success");
append_trinary_filter($filter_list, "chick_presence", "Chicks");

$filter_list['filter_type'][] = array('divider' => true);

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'too-dark-filter-dropdown',
            'filter_name' => 'too_dark',
            'default_text' => 'Too Dark - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Too Dark - Any', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Too Dark - Yes', 'filter_value' => '1' ),
                array( 'dropdown_text' => 'Too Dark - No',  'filter_value' => '0' )
            )
        );

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'corrupt-filter-dropdown',
            'filter_name' => 'corrupt',
            'default_text' => 'Corrupt - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Corrupt - Any', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Corrupt - Yes', 'filter_value' => '1' ),
                array( 'dropdown_text' => 'Corrupt - No',  'filter_value' => '0' )
            )
        );

$filter_list['filter_type'][] = array('divider' => true);

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'location-dropdown',
            'filter_name' => 'location_id',
            'default_text' => 'Location - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Any Location', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Belden, ND', 'filter_value' => '1' ),
                array( 'dropdown_text' => 'Blaisdell, ND', 'filter_value' => '2' ),
                array( 'dropdown_text' => 'Lostwood, ND',  'filter_value' => '3' ),
                array( 'dropdown_text' => 'Missouri River, ND',  'filter_value' => '4' )
            )
        );

$filter_list['filter_type'][] = array(
            'dropdown_id' => 'species-dropdown',
            'filter_name' => 'species_id',
            'default_text' => 'Species - Any',
            'filter_option' => array(
                array( 'dropdown_text' => 'Any Species', 'filter_value' => 'null' ),
                array( 'dropdown_text' => 'Sharp-tailed Grouse', 'filter_value' => '1' ),
                array( 'dropdown_text' => 'Interior Least Tern', 'filter_value' => '2' ),
                array( 'dropdown_text' => 'Piping Plover',  'filter_value' => '3' )
            )
        );


$filter_list_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/filter_list_template.html");
$mustache_engine = new Mustache_Engine;

echo "
    <div class='row-fluid'>
        <div class='span2'>
            <div class='well well-large span2' style='padding-top: 10px; padding-bottom: 10px; margin-top: 3px; margin-bottom: 5px; position:fixed;'>";

echo $mustache_engine->render($filter_list_template, $filter_list);

echo "      </div>
        </div>";

echo "  <div class='span10' style='margin-left:5px;'>
            <div class='row-fluid'>";

echo "
<div class='well well-small' style='padding-top: 5px; padding-bottom: 0px; margin-top:3px; margin-bottom: 10px'>
    <div class='row-fluid'>
        <div class='span12'>
                <p>You have $bossa_total_credit credit from $total_observations observations. $valid_observations have been marked valid and $invalid_observations marked invalid (" . round((100 * $valid_observations / ($valid_observations + $invalid_observations)), 2) . "% accuracy). " . ($total_observations - ($valid_observations + $invalid_observations)) . " observations are awaiting validation. You have averaged " . round(($bossa_total_credit / $valid_observations), 2) . " credit per valid observation.</p>
        </div>
    </div>
</div>";


echo "<div id='videos-placeholder'></div>";
echo "<div id='videos-nav-placeholder'></div>";

echo "  </div>
      </div>";
echo "</div>";

print_footer();

echo "
</body>
</html>
";

?>
