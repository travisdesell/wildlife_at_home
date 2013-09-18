<?php

require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/get_expert_observation_table.php');

require_once('/projects/wildlife/html/inc/util.inc');


$video_id = mysql_real_escape_string($_POST['video_id']);
$video_file = mysql_real_escape_string($_POST['video_file']);
$video_converted = mysql_real_escape_string($_POST['video_converted']);

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";

if ($video_converted == 'true') {
    echo "
        <div class='row-fluid'>
            <div class='span6' id='wildlife-video-span-$video_id'>
                <div class='row-fluid'>
                    <video style='width:100%;' id='wildlife-video-$video_id' controls='controls' preload='auto'>
                        <source src=\"http://wildlife.und.edu/$video_file\" type=\"video/mp4\">
                        <source src=\"http://wildlife.und.edu/$video_file.ogv\" type=\"video/ogg\">
                        This video requires a browser that supports HTML5 video.
                    </video>
                </div>

                <div class='row-fluid' id='wildlife-video-buttons-$video_id'>
                    <button class='btn btn-primary span5 pull-left fast-backward-button' style='margin-top:0px;' video_id='$video_id'>fast backward</button>

                    <div class='span2'>
                        <input style='width:100%; padding:3px; margin:1px;' type='text' id='speed-textbox-$video_id' value='speed: 1' readonly='readonly'></input>
                    </div>

                    <button class='btn btn-primary span5 pull-right fast-forward-button' style='margin-top:0px;' video_id='$video_id'>fast forward</button>
                </div>

            </div>"; 
} else {
    echo "
        <div class='row-fluid'>
            <div class='span6' id='wildlife-video-span-$video_id'>
                <p>This video has not yet been converted to a format where it can be streamed on the expert video classification webpage.</p>
            </div>"; 
}

echo "  <div class='span6'>
            <div class='row-fluid'>";

echo "<div class='observations-table-div' id='observations-table-div-$video_id'>";
echo get_expert_observation_table($video_id, $observation_count);
echo "</div>";

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
