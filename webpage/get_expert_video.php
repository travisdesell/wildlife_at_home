<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

require_once('/projects/wildlife/html/inc/util.inc');


$video_id = mysql_real_escape_string($_POST['video_id']);
$video_file = mysql_real_escape_string($_POST['video_file']);

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";

echo "
    <div class='row-fluid'>
        <div class='span6'>
            <div class='row-fluid'>
                <video style='width:100%;' id='wildlife-video-$video_id' controls='controls' preload='auto'>
                    <source src=\"http://wildlife.und.edu/$video_file\" type=\"video/mp4\">
                    <source src=\"http://wildlife.und.edu/$video_file.ogv\" type=\"video/ogg\">
                    This video requires a browser that supports HTML5 video.
                </video>
            </div>

            <div class='row-fluid'>
                <button class='btn btn-primary span5 pull-left fast-backward-button' style='margin-top:0px;' video_id='$video_id'>fast backward</button>

                <div class='span2'>
                    <input style='width:100%; padding:3px; margin:1px;' type='text' id='speed-textbox-$video_id' value='speed: 1' readonly='readonly'></input>
                </div>

                <button class='btn btn-primary span5 pull-right fast-forward-button' style='margin-top:0px;' video_id='$video_id'>fast forward</button>
            </div>

        </div>"; 

echo "  <div class='span6'>
            <div class='row-fluid'>";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT * FROM expert_observations WHERE video_id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");


$printed_header = false;

while ($row = mysql_fetch_assoc($result)) {
    if (!$printed_header) {
        echo "<div class='observations-table-div' id='observations-table-div-$video_id'>";
        echo "<table class='table table-striped table-bordered table-condensed observations-table' video_id='$video_id' id='observations-table-$video_id'>
                <thead>
                    <th>User</th>
                    <th>Event</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Comments</th>
                </thead><tbody>";
        $printed_header = true;
    }

    echo "<tr observation_id='" . $row['id'] . "' id='observation-row-" . $row['id'] . "'> ";

    echo "<td> " . get_user_from_id($row['user_id'])->name . " </td>";
   
    echo " <td>" . $row['event_type'] . "</td> <td>" . $row['start_time'] . "</td> <td>" . $row['end_time'] . "</td> <td>" . $row['comments'] . "</td> <td style='padding-top:0px; padding-bottom:0px; width:25px;'> <button class='btn btn-small btn-danger pull-right remove-observation-button' id='remove-observation-button-" . $row['id'] . "' observation_id='" . $row['id'] . "' style='margin-top:3px; margin-bottom:0px; padding-top:0px; padding-bottom:0px;'> - </button> </td> </tr>"; 
}

if ($printed_header) {
    echo "</tbody></table></div>";
} else {
    echo "<div class='observations-table-div' id='observations-table-div-$video_id'></div>";
}

echo "      </div>

            <div class='row-fluid'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='event-button-$video_id' style='margin-top:-7px;'>
                        Event <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu' id='event-dropdown-$video_id'>
                        <li><a href='javascript:;' class='event-dropdown' event_id='0' video_id='$video_id' id='any-event-dropdown'>Unspecified</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='1' video_id='$video_id' id='bird-leave-dropdown'>Bird Presence</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='2' video_id='$video_id' id='bird-return-dropdown'>Bird Absence</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='3' video_id='$video_id' id='predator-dropdown'>Predator</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='4' video_id='$video_id' id='other-animal-dropdown'>Other Animal</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='5' video_id='$video_id' id='nest-defense-dropdown'>Nest Defense</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='6' video_id='$video_id' id='nest-success-dropdown'>Nest Success</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='7' video_id='$video_id' id='nest-success-dropdown'>Chick Presence</a></li>
                    </ul>
                </div>

                <input type='text' id='event-start-time-$video_id' video_id='$video_id' class='event-start-time-textbox' style='width:15%; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:0px' value='Start Time'></input>
                <input type='text' id='event-end-time-$video_id' video_id='$video_id' class='event-end-time-textbox' style='width:15%; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:10px' value='End Time'></input>

                <button class='btn btn-small btn-primary pull-right submit-observation-button' id='submit-observation-button-$video_id' video_id='$video_id' style='margin-right:4px;'> + </button>
            </div>

            <div class='row-fluid'>
                <div class='span12'>
                    <p style='padding-top:8px; margin-bottom:0px'>
                    Comments:
                    </p>
                </div>
            </div>

            <div class='row-fluid' style='padding-top:0px margin-top:0px;'>
                <textarea class='span12' name='comments' id='comments-$video_id'/>
            </div>
        </div>
    </div>";

?>
