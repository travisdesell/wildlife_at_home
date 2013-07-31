<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$video_min = mysql_real_escape_string($_POST['video_min']);
$video_count = mysql_real_escape_string($_POST['video_count']);

$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);

$filters = $_POST['filters'];

$new_filter = '';

if ($filters['interesting'] == 'true')        $new_filter .= " AND observations.interesting > 0";
if ($filters['invalid'] == 'true')            $new_filter .= " AND observations.status = 'INVALID'";
if ($filters['bird_presence'] == 'true')      $new_filter .= " AND observations.bird_presence > 0";
if ($filters['bird_absence'] == 'true')      $new_filter .= " AND observations.bird_absence > 0";
if ($filters['chick_presence'] == 'true')     $new_filter .= " AND observations.chick_presence > 0";
if ($filters['predator_presence'] == 'true')  $new_filter .= " AND observations.predator_presence > 0";
if ($filters['nest_defense'] == 'true')       $new_filter .= " AND observations.nest_defense > 0";
if ($filters['nest_success'] == 'true')       $new_filter .= " AND observations.nest_success> 0";
if ($filters['bird_return'] == 'true')        $new_filter .= " AND observations.bird_leave > 0";
if ($filters['bird_leave'] == 'true')         $new_filter .= " AND observations.bird_return > 0";
if ($filters['corrupt'] == 'true')            $new_filter .= " AND observations.corrupt > 0";
if ($filters['too_dark'] == 'true')            $new_filter .= " AND observations.too_dark > 0";

$display_nav_numbers = true;
if (strlen($new_filter) > 5) $new_filter = substr($new_filter, 5);
else {
    $display_nav_numbers = false;
}

$filter = $new_filter;

if ($species_id > 0) $filter .= " AND species_id = $species_id";
if ($location_id > 0) $filter .= " AND location_id = $location_id";


//error_log("the filter is: " . $filter);


if ($video_min == NULL) $video_min = 0;
if ($video_count == NULL) $video_count = 5;

echo "
    <div class='well well-large' style='padding-top: 10px; padding-bottom: 5px; margin-top: 3px; margin-bottom: 5px'> 
        <div class='row-fluid'>
            <div class='container'>
                <div class='span12'>";


if ($display_nav_numbers ) {
    $user = get_logged_in_user();
    $user_id = $user->id;

    ini_set("mysql.connect_timeout", 300);
    ini_set("default_socket_timeout", 300);

    $wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);


    $query = "SELECT count(id) FROM video_segment_2 vs2 WHERE EXISTS (SELECT id FROM observations WHERE user_id = $user_id AND $filter AND observations.video_segment_id = vs2.id)";

    //echo "<!-- $query -->\n";


    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($result);

    $max_items = $row['count(id)'];

    if ($video_min > 0) {
        $new_min = $video_min - $video_count;
        if ($new_min < 0) $new_min = 0;

        echo "<a class='video-nav-list' id = 'video-list-$new_min' href='#'>&lt;&lt;</a> &nbsp;&nbsp;";
    }

    $count = 0;

    $current = $video_min - (5 * $video_count);
    if ($current < 0) $current = 0;

    while ($current < $max_items && $count < 10) {
        $next = ($current + $video_count);
        if ($next > $max_items) $next = $max_items;
        $next--;

        if ($current == $video_min) {
            if ($current == $next) {
                echo "<a class='video-nav-list' id='video-list-$current' href='#'><b>$current</b></a> &nbsp;&nbsp;";
            } else {
                echo "<a class='video-nav-list' id='video-list-$current' href='#'><b>$current..$next</b></a> &nbsp;&nbsp;";
            }
        } else {
            if ($current == $next) {
                echo "<a class='video-nav-list' id='video-list-$current' href='#'>$current</a> &nbsp;&nbsp;";
            } else {
                echo "<a class='video-nav-list' id='video-list-$current' href='#'>$current..$next</a> &nbsp;&nbsp;";
            }
        }

        $current += $video_count;

        $count++;
    }

    if ($video_min + $video_count < $max_items) {
        $new_min = $video_min + $video_count;

        echo "<a class='video-nav-list' id='video-list-$new_min' href='#'>&gt;&gt;</a> &nbsp;&nbsp;";
    }
}

echo "
                    <div class='btn-group pull-right'>
                        <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='display-videos-button'>
                        Display $video_count videos <span class='caret'></span>
                        </button>
                        <ul class='dropdown-menu bottom-up'>
                            <li><a href='#' id='display-5-dropdown'>Display  5 videos</a></li>
                            <li><a href='#' id='display-10-dropdown'>Display 10 videos</a></li>
                            <li><a href='#' id='display-20-dropdown'>Display 20 videos</a></li>
                        </ul>
                    </div>

                    <input class='pull-right' style='width:30px; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:10px' type='text' id='go-to-textbox' value=''>
                    <button class='pull-right btn btn-small btn-default' id='go-to-button'>Go to: </button>

                </div>
            </div>
        </div>
    </div>
";

//error_log("video_min: $video_min, video_count: $video_count, user_id: $user_id, filter: $filter");
//error_log("completed get_video_count_nav.php");

?>
