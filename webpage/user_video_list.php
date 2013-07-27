<?php

require_once('../inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
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
    <title>Wildlife@Home: Watching Video</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='watch.js'></script>

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
           background-color: #eee;
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


$video_min = mysql_real_escape_string($_GET['video_min']);
$video_count = mysql_real_escape_string($_GET['video_count']);

if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

$user = get_logged_in_user();
$user_id = $user->id;

/*
 * This is a little convoluted, but it will quickly select a random video_segment which has
 * been processed.
 *
 * select one that has been processed, not validated, and has no observation by the user already.
 *
 * first select one that has observations by OTHER users, then select one with no observations.
 */

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$filter = "observations.interesting > 0";

$query = "SELECT id, filename FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id AND $filter AND observations.video_segment_id = vs2.id) LIMIT $video_min, $video_count";

//echo "<!-- $query -->\n";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$video_list = array();

function set_marks(&$value) {
    if ($value == 1) {
        $value = '&#x2713;';
    } else if ($value == 0) {
        $value = '?';
    } else if ($value == -1) {
        $value = 'x';
    }
}

$found = false;
while ($row = mysql_fetch_assoc($result)) {
    $found = true;

    $video_segment_2_id = $row['id'];
    $segment_filename = $row['filename'];

    $video_and_observations = array();
    $video_and_observations['video_segment_2_id'] = $video_segment_2_id;
    $video_and_observations['segment_filename'] = $segment_filename;
    $video_and_observations['video_name'] = trim(substr($segment_filename, strrpos($segment_filename, '/') + 1));

    $observation_query = "SELECT id, bird_leave, bird_return, bird_presence, bird_absence, predator_presence, nest_defense, nest_success, interesting, user_id, comments, status, corrupt, too_dark, chick_presence FROM observations WHERE video_segment_id = $video_segment_2_id";

    $observation_result = attempt_query_with_ping($observation_query, $wildlife_db);
    if (!$observation_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $observation_query\n");

    while ($observation_row = mysql_fetch_assoc($observation_result)) {
        set_marks($observation_row['bird_leave']);
        set_marks($observation_row['bird_return']);
        set_marks($observation_row['bird_presence']);
        set_marks($observation_row['bird_absence']);
        set_marks($observation_row['predator_presence']);
        set_marks($observation_row['nest_defense']);
        set_marks($observation_row['nest_success']);
        set_marks($observation_row['chick_presence']);
        set_marks($observation_row['too_dark']);
        set_marks($observation_row['corrupt']);

        $video_and_observations['observations'][] = $observation_row;
    }

    $video_list['video_list'][] = $video_and_observations;

}

/*
    echo "<script type='text/javascript'>
        var user_id = $user_id; 
        var video_segment_id = " . $row['id'] . ";
        var duration_s = $duration_s;
        var video_list = " . json_encode($video_list) . ";
    </script>";
*/

echo "
</head>
<body>

    <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
        <div class='row-fluid'>
            <div class='container'>
                <div class='span12'>
                    <p>Display videos:
                    <span class='label'>Invalid</span>
                    <span class='label label-info'>Interesting</span>
                    <span class='label'>Bird Presence</span>
                    <span class='label'>Chick Presence</span>
                    <span class='label'>Predator Presence</span>
                    <span class='label'>Nest Defense</span>
                    <span class='label'>Nest Success</span>
                    <span class='label'>Bird Leave</span>
                    <span class='label'>Bird Return</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
        <div class='row-fluid'>
            <div class='container'>
                <div class='span12'>";


$query = "SELECT count(id) FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id AND $filter AND observations.video_segment_id = vs2.id)";

//echo "<!-- $query -->\n";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

$max_items = $row['count(id)'];

$current = 0;

while ($current < $max_items) {
    $next = ($current + $video_count);
    if ($next > $max_items) $next = $max_items;

    echo "<a href='./user_video_list.php?video_min=$current&video_count=$video_count'>$current..$next</a> &nbsp;";
    $current += $video_count;
}

echo "
                </div>
            </div>
        </div>
    </div>
";

$active_items = array(
                    'home' => '',
                    'watch_video' => 'active',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);


if ($found) {
    $video_list_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/video_list_template.html");
    $mustache_engine = new Mustache_Engine;
    echo $mustache_engine->render($video_list_template, $video_list);

} else {
    /**
     *  Fix the error message according to what type of video is being looked for.
     */
    echo "<p>No videos of " . $species_name . " currently available at $location_name.<p>\n";
    echo "<p>Please go to the <a href = 'video_selector.php'>video selection webpage</a> to select another specices and site.</p>";
}


print_footer();

echo "
</body>
</html>
";

?>
