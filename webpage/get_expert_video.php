<?php

$video_id = mysql_real_escape_string($_POST['video_id']);
$video_file = mysql_real_escape_string($_POST['video_file']);

//echo "<p>Got this for video: $video_id, and file: $video_file</p>";

echo "
    <div class='row-fluid'>
        <div class='span6'>
            <video style='width:100%;' id='wildlife-video-$video_id' controls='controls' preload='auto'>
                <source src='http://wildlife.und.edu/$video_file' type='video/mp4'></source>
                <!-- <source src='http://wildlife.und.edu/$video_file.ogv' type='video/ogg'></source> -->
                This video requires a browser that supports HTML5 video.
            </video>
        </div>";

echo "  <div class='span6'>
            <div class='row-fluid'>";

echo "<table class='table table-striped table-bordered table-condensed'>
        <thead>
            <th>Event</th>
            <th>Time</th>
            <th>Comments</th>
        </thead>";



for ($i = 0; $i < 5; $i++) {
   echo "<tr> <td>Predator</td> <td>11:32:02</td> <td>Badger</td> <td style='padding-top:0px; padding-bottom:0px; width:25px;'> <button class='btn btn-small btn-danger pull-right' id='remove-observation-button-$i' style='margin-top:3px; margin-bottom:0px; padding-top:0px; padding-bottom:0px;'> - </button> </td> </tr>"; 
}

echo "</table>";

echo "      </div>

            <div class='row-fluid'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='event-button-$video_id' style='margin-top:-7px;'>
                        Event <span class='caret'></span>
                    </button>
                    <ul class='dropdown-menu' id='event-dropdown-$video_id'>
                        <li><a href='#' class='event-dropdown' event_id='0' video_id='$video_id' id='any-event-dropdown'>Any Event</a></li>
                        <li><a href='#' class='event-dropdown' event_id='1' video_id='$video_id' id='bird-leave-dropdown'>Bird Leave</a></li>
                        <li><a href='#' class='event-dropdown' event_id='2' video_id='$video_id' id='bird-return-dropdown'>Bird Return</a></li>
                        <li><a href='#' class='event-dropdown' event_id='3' video_id='$video_id' id='predator-dropdown'>Predator</a></li>
                        <li><a href='#' class='event-dropdown' event_id='4' video_id='$video_id' id='other-animal-dropdown'>Other Animal</a></li>
                        <li><a href='#' class='event-dropdown' event_id='5' video_id='$video_id' id='nest-defense-dropdown'>Nest Defense</a></li>
                        <li><a href='#' class='event-dropdown' event_id='6' video_id='$video_id' id='nest-success-dropdown'>Nest Success</a></li>
                    </ul>
                </div>

                <input type='text' id='event-time-$video_id' video_id='$video_id' class='event-time-textbox' style='width:15%; margin-top:0px; padding-bottom:0px; margin-left:2px; margin-right:10px' value='Event Time'></input>

                <button class='btn btn-small btn-primary pull-right' id='submit-observation-button' style='margin-right:4px;'> + </button>
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
