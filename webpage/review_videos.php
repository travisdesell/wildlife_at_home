<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/navbar.php');
require_once($cwd . '/footer.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/user.php');

require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");

$user = get_user();
$user_id = $user['id'];
$user_name = $user['name'];

$allow_add_removal = is_special_user__fixme($user, true);

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Reviewing Videos</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <link rel='stylesheet' type='text/css' href='custom.css'>

    <script type='text/javascript' src='timed_observations.js'></script>
    <script type='text/javascript' src='js/user_review.js'></script>
    <script type='text/javascript' src='js/expert_review.js'></script>
    <script type='text/javascript' src='js/review_videos.js'></script>

    <script type='text/javascript'>
        var user_id = $user_id; 
        var user_name = '$user_name'; 
        var allow_add_removal = $allow_add_removal;
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

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

/**
 *  Prints the filters on the left side
 */
echo "
    <div class='row-fluid'>
        <div class='span2' style='z-index:1001;' id='filter-sidebar'>
        <div class='well well-large span2' style='padding-top: 10px; padding-bottom: 10px; margin-top: 5px; margin-bottom: 5px; position:fixed; z-index:1001;'>";

//echo $mustache_engine->render($filter_list_template, $filter_list);

//if (is_special_user__fixme($user, true)) { //per susan, only special users can see the animal ids
    echo "<input type='text' id='video-id-textarea' placeholder='Filter by Video ID' style='width:90%; height:22px; padding: 4px 6px 4px 6px; display: block; margin-left: auto; margin-right: auto;'>";

    echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
//}

echo "
<div class='btn-group' style='width:100%; margin-left:0px;'>
    <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Year Filter <span class='caret'></span> </button>

    <ul class='dropdown-menu'>
        <li><a href='javascript:;' class='video-filter-dropdown year-filter' year=2011>2011</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown year-filter' year=2012>2012</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown year-filter' year=2013>2013</a></li>
    </ul>
</div> <!--button group-->";

echo "
<div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
    <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Species Filter <span class='caret'></span> </button>

    <ul class='dropdown-menu'>
        <li><a href='javascript:;' class='video-filter-dropdown species-filter' species_id=1>Sharp-tailed Grouse</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown species-filter' species_id=2>Interior Least Tern</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown species-filter' species_id=3>Piping Plover</a></li>
    </ul>
</div> <!--button group-->";

echo "
<div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
    <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Location Filter <span class='caret'></span> </button>

    <ul class='dropdown-menu'>
        <li><a href='javascript:;' class='video-filter-dropdown location-filter' location_id=1>Belden</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown location-filter' location_id=2>Blaisdell</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown location-filter' location_id=3>Lostwood</a></li>
        <li><a href='javascript:;' class='video-filter-dropdown location-filter' location_id=4>Missouri River</a></li>
    </ul>
</div> <!--button group-->";

if (is_special_user__fixme($user, true)) { //per susan, only special users can see the animal ids
    echo "
    <div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Animal ID Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu scrollable-dropdown-menu' style='width:500px;'>";

    $query = "SELECT distinct(animal_id) FROM video_2 ORDER BY animal_id";
    $result = mysql_query($query, $wildlife_db);

    echo "
            <li class='column-menu span2 firstcolumn' style='margin-left:10px;'>
                <ul style='list-style-type: none; margin-left:0px; margin-right:5px;'>";

    $num_rows = mysql_num_rows($result);
    $count = 0;
    while ($row = mysql_fetch_assoc($result)) {
        if ($count == floor($num_rows / 6) || $count == floor($num_rows * 2 / 6) || $count == floor($num_rows * 3 / 6) || $count == floor($num_rows * 4 / 6) || $count == floor($num_rows * 5 / 6)) {
            echo "
                </ul>   
                </li>

                <li class='column-menu span2' style='margin-left:10px;'>
                <ul style='list-style-type: none; margin-right:5px; margin-left:0px;'>";
        }
        echo "  <li style='padding-left:0px; padding-right:0px;'><a href='javascript:;' class='video-filter-dropdown animal-id-filter' animal_id='" . $row['animal_id'] . "' style='padding-left:0px; padding-right:0px;'>" . $row['animal_id'] . "</a></li>";

        $prev_row = $row;
        $count++;
    }
    echo "      </ul>   
            </li>
        <ul>
    </div>  <!-- button group -->";
}   //per susan, only special users can see the animal ids

if (is_special_user__fixme($user, true)) { //other video filters only apply to special users
    echo "
    <div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Other Video Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu'>
            <li><a href='javascript:;' class='video-filter-dropdown other-video-filter' other_id='private'>Private</a></li>
            <li><a href='javascript:;' class='video-filter-dropdown other-video-filter' other_id='public'>Public</a></li>
            <li><a href='javascript:;' class='video-filter-dropdown other-video-filter' other_id='unwatched'>Unwatched</a></li>
            <li><a href='javascript:;' class='video-filter-dropdown other-video-filter' other_id='watched'>Watched</a></li>
            <li><a href='javascript:;' class='video-filter-dropdown other-video-filter' other_id='finished'>Finished</a></li>
        </ul>
    </div> <!--button group-->";    //end button group for other video filters
} //other filters really only apply to the expert interface

echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
echo "<b style='float:right;' id='display-videos-text'>Displaying All Videos</b>";
echo "<div id='video-filter-list' style='display:table; width:100%;'></div>";
echo "<hr style='margin-top:5px; margin-bottom:5px;'>";


echo "
<div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
    <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Event Type Filter <span class='caret'></span> </button>

    <ul class='dropdown-menu' style='width:525px;'>";

$query = "SELECT category, name, id FROM observation_types WHERE sharptailed_grouse + piping_plover + least_tern > 0 ";
if (!is_special_user__fixme($user, true)) { //don't show expert only events
    $query .= " AND expert_only = 0";
}
$query .= " ORDER BY category, name";
$result = mysql_query($query, $wildlife_db);

echo "
            <li class='column-menu span4 firstcolumn'>
            <ul style='list-style-type: none; margin-left:0px; margin-right:5px;'>";

$num_rows = mysql_num_rows($result);
$prev_row = mysql_fetch_assoc($result);
echo "<li class='nav-header'>" . $prev_row['category'] . "</li>";
echo "<li><a href='javascript:;' class='event-filter-dropdown' event_id='" . $prev_row['id'] . "'>" . $prev_row['name'] . "</a></li>";

$count = 1;
while ($row = mysql_fetch_assoc($result)) {
    if ($count == floor($num_rows / 3) || $count == floor($num_rows * 2 / 3)) {
        echo "
            </ul>   
            </li>

            <li class='column-menu span4'>
            <ul style='list-style-type: none; margin-right:5px; margin-left:0px;'>";
    }

    if (0 != strcmp($prev_row['category'], $row['category'])) {
        echo "<li class='nav-header'>" . $row['category'] . "</li>";
    }
    echo "  <li><a href='javascript:;' class='event-filter-dropdown event-filter' event_id='" . $row['id'] . "'>" . $row['name'] . "</a></li>";

    $prev_row = $row;
    $count++;
}
echo "      </ul>   
        </li>
    <ul>
</div>  <!-- button group -->"; //end the button group for events

echo "
<div class='btn-group' style='width:100%; margin-left:0px; margin-top:5px;'>
    <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Other Event Filter <span class='caret'></span> </button>

    <ul class='dropdown-menu'>";
if (is_special_user__fixme($user, true)) { //other event filters only apply to special users
    echo "  <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='converted_events'>Converted</a></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='invalid_times'>Invalid Times</a></li>
            <li class='divider'></li>";
} //other filters really only apply to the expert interface

echo "      <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='unreported_events'>Unreported</a></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='reported_events'>Reported</a></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='responded_events'>Responded</a></li>
            <li class='divider'></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='unvalidated'>Unvalidated</a></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='valid'>Valid</a></li>
            <li><a href='javascript:;' class='event-filter-dropdown other-event-filter' other_id='invalid'>Invalid</a></li>
        </ul>
    </div> <!--button group-->";    //end button group for other event filters

echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
echo "<b style='float:right;' id='display-events-text'>With Any Events</b>";
echo "<div id='event-filter-list' style='display:table; width:100%;'></div>";
echo "<hr style='margin-top:5px; margin-bottom:5px;'>";

echo "<button type='button' class='btn btn-small btn-default btn-block' id='apply-filter-button' style='padding-left:5px;'>Apply Filter</button>";
echo "<button type='button' class='btn btn-small btn-default btn-block' id='clear-filter-button' style='padding-left:5px;'>Clear Filter</button>";

if (is_special_user__fixme($user, true)) { //other event filters only apply to special users
    echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
    echo "<button type='button' class='btn btn-small btn-default btn-block' id='all-videos-button' style='padding-left:5px;'>Showing All Videos</button>";
}

echo "      </div>";
echo "  </div>";

/**
 *  Prints the list of videos
 */

echo "  <div class='span10' style='margin-left:5px;' id='video-list-body'>
            <div class='row-fluid'>
                <div id='video-list-placeholder'></div>
                <div id='videos-nav-placeholder'></div>
            </div>
      </div>";
echo "</div>";

print_footer();

echo "
</body>
</html>
";

?>
