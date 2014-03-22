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

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Watched Videos</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <link rel='stylesheet' type='text/css' href='custom.css'>

    <script type='text/javascript' src='timed_observations.js'></script>
    <script type='text/javascript' src='expert_classify_video.js'></script>

    <script type='text/javascript'>
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

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

/**
 *  Prints the filters on the left side
 */
echo "
    <div class='row-fluid'>
        <div class='span2' style='z-index:1001;'>
            <div class='well well-large span2' style='padding-top: 10px; padding-bottom: 10px; margin-top: 5px; margin-bottom: 5px; position:fixed; z-index:1001;'>";

//echo $mustache_engine->render($filter_list_template, $filter_list);

if (is_special_user__fixme($user, true)) {
    echo "
    <div class='btn-group' style='width:100%; margin-left:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Year Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu'>
            <li><a href='javascript:;' class='filter-dropdown year-filter'>2011</a></li>
            <li><a href='javascript:;' class='filter-dropdown year-filter'>2012</a></li>
            <li><a href='javascript:;' class='filter-dropdown year-filter'>2013</a></li>
        </ul>
    </div> <!--button group-->";

    echo "
    <div class='btn-group' style='width:100%; margin-left:5px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Species Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu'>
            <li><a href='javascript:;' class='filter-dropdown'>Sharp-tailed Grouse</a></li>
            <li><a href='javascript:;' class='filter-dropdown'>Piping Plover</a></li>
            <li><a href='javascript:;' class='filter-dropdown'>Interior Least Tern</a></li>
        </ul>
    </div> <!--button group-->";

    echo "
    <div class='btn-group' style='width:100%; margin-left:5px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Location Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu'>
            <li><a href='javascript:;' class='filter-dropdown location-filter'>Belden</a></li>
            <li><a href='javascript:;' class='filter-dropdown location-filter'>Blaisdell</a></li>
            <li><a href='javascript:;' class='filter-dropdown location-filter'>Lostwood</a></li>
            <li><a href='javascript:;' class='filter-dropdown location-filter'>Missouri River</a></li>
        </ul>
    </div> <!--button group-->";

    echo "
    <div class='btn-group' style='width:100%; margin-left:5px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Animal ID Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu' style='width:500px;'>";

    $query = "SELECT distinct(animal_id) FROM video_2 ORDER BY animal_id";
    $result = mysql_query($query, $wildlife_db);

    echo "
            <li class='column-menu span2 firstcolumn' style='margin-left:10px;'>
                <ul style='list-style-type: none; margin-left:5px; margin-right:5px;'>";

    $num_rows = mysql_num_rows($result);
    $count = 0;
    while ($row = mysql_fetch_assoc($result)) {
        if ($count == floor($num_rows / 6) || $count == floor($num_rows * 2 / 6) || $count == floor($num_rows * 3 / 6) || $count == floor($num_rows * 4 / 6) || $count == floor($num_rows * 5 / 6)) {
            echo "
                </ul>   
                </li>

                <li class='column-menu span2' style='margin-left:10px;'>
                <ul style='list-style-type: none; margin-right:5px; margin-left:5px;'>";
        }
        echo "  <li style='padding-left:0px; padding-right:0px;'><a href='javascript:;' class='filter-dropdown animal-id-filter' style='padding-left:0px; padding-right:0px;'>" . $row['animal_id'] . "</a></li>";

        $prev_row = $row;
        $count++;
    }
    echo "      </ul>   
            </li>
        <ul>
    </div>  <!-- button group -->";



    echo "
    <div class='btn-group' style='width:100%; margin-left:5px; margin-top:5px;'>
        <button type='button' class='btn btn-small dropdown-toggle' data-toggle='dropdown' style='width:100%; text-align:right;'>Add Event Filter <span class='caret'></span> </button>

        <ul class='dropdown-menu' style='width:525px;'>";

    $query = "SELECT category, name, id FROM observation_types ORDER BY category, name";
    $result = mysql_query($query, $wildlife_db);

    echo "
                <li class='column-menu span4 firstcolumn'>
                <ul style='list-style-type: none; margin-left:5px; margin-right:5px;'>";

    $num_rows = mysql_num_rows($result);
    $prev_row = mysql_fetch_assoc($result);
    echo "<li class='nav-header'>" . $prev_row['category'] . "</li>";
    echo "<li><a href='javascript:;' class='filter-dropdown' event_id='" . $prev_row['id'] . "'>" . $prev_row['name'] . "</a></li>";

    $count = 1;
    while ($row = mysql_fetch_assoc($result)) {
        if ($count == floor($num_rows / 3) || $count == floor($num_rows * 2 / 3)) {
            echo "
                </ul>   
                </li>

                <li class='column-menu span4'>
                <ul style='list-style-type: none; margin-right:5px; margin-left:5px;'>";
        }

        if (0 != strcmp($prev_row['category'], $row['category'])) {
            echo "<li class='nav-header'>" . $row['category'] . "</li>";

            if ($row['category'] == "Miscellaneous") {
                echo "  <li><a href='javascript:;' class='filter-dropdown' event_id='converted_events'>Converted Events</a></li>";
                echo "  <li><a href='javascript:;' class='filter-dropdown' event_id='invalid_times'>Invalid Times</a></li>";
            }
        }
        echo "  <li><a href='javascript:;' class='filter-dropdown' event_id='" . $row['id'] . "'>" . $row['name'] . "</a></li>";

        $prev_row = $row;
        $count++;
    }
    echo "      </ul>   
                </li>";

    echo "
        <ul>
    </div>  <!-- button group -->";

    echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
    echo "<b style='float:right;' id='display-videos-text'>Displaying All Videos</b>";
    echo "<div id='filter-list' style='display:table; width:100%;'></div>";

    echo "<hr style='margin-top:5px; margin-bottom:5px;'>";
    /*
    echo "<button type='button' class='btn btn-small btn-default btn-block' id='show-converted-videos-button' data-toggle='button' style='padding-left:5px;'>Converted Event Videos</button>";

    echo "<button type='button' class='btn btn-small btn-default btn-block' id='show-bad-event-videos-button' data-toggle='button' style='padding-left:5px;'>Bad Time Event Videos</button>";
     */

    echo "<button type='button' class='btn btn-small btn-default btn-block' id='apply-filter-button' style='padding-left:5px;'>Apply Filter</button>";
    echo "<button type='button' class='btn btn-small btn-default btn-block' id='clear-filter-button' style='padding-left:5px;'>Clear Filter</button>";
}
echo "      </div>";
echo "  </div>";

/**
 *  Prints the list of videos
 */

echo "  <div class='span10' style='margin-left:5px;'>
            <div class='row-fluid'>";

if (is_special_user__fixme($user, true)) {
    echo "<div id='video-list-placeholder'></div>";
    echo "<div id='videos-nav-placeholder'></div>";
}

echo "  </div>
      </div>";
echo "</div>";

print_footer();

echo "
</body>
</html>
";

?>
