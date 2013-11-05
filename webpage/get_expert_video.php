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

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT species_id FROM video_2 WHERE id = $video_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$species_id = $row['species_id'];

echo "      </div>

            <div class='row-fluid'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-small btn-default dropdown-toggle' data-toggle='dropdown' id='event-button-$video_id' style='margin-top:-7px;'>
                        Event <span class='caret'></span>
                    </button>";

if ($species_id == 1) {
    echo "          <ul class='dropdown-menu' id='event-dropdown-$video_id'>
                        <li class='nav-header'>Miscellaneous</li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='unspecified' video_id='$video_id' id='any-event-dropdown'>Unspecified</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='volunteer training' video_id='$video_id' id='volunteer-training-dropdown'>Volunteer Training</a></li>
                        <li class='nav-header'>Locomotion</li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='bird presence' video_id='$video_id' id='bird-leave-dropdown'>Bird Presence</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='bird absence' video_id='$video_id' id='bird-return-dropdown'>Bird Absence</a></li>
                        <li class='nav-header'>Territorial</li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='territorial - predator' video_id='$video_id' id='predator-dropdown'>Predator</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='territorial - other animal' video_id='$video_id' id='other-animal-dropdown'>Other Animal</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='territorial - nest defense' video_id='$video_id' id='nest-defense-dropdown'>Nest Defense</a></li>
                        <li class='nav-header'>Chick Behavior</li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='nest success' video_id='$video_id' id='nest-success-dropdown'>Nest Success</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='chick presence' video_id='$video_id' id='chick-presence-dropdown'>Chick Presence</a></li>
                        <li class='nav-header'>Camera Interaction</li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='camera iteraction - attack' video_id='$video_id' id='nest-success-dropdown'>Attack</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='camera iteraction - inspection' video_id='$video_id' id='nest-success-dropdown'>Physical Inspection</a></li>
                        <li><a href='javascript:;' class='event-dropdown' event_id='camera iteraction - observation' video_id='$video_id' id='nest-success-dropdown'>Observation</a></li>
                    </ul>";

} else if ($species_id == 2) {
    echo "          <ul class='dropdown-menu' id='event-dropdown-$video_id' style='width:350px;'>
                        <li class='column-menu span6 firstcolumn'>
                            <ul style='list-style-type: none; margin-left:10px;'>
                                <li class='nav-header'>Miscellaneous</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='unspecified' video_id='$video_id' id='any-event-dropdown'>Unspecified</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='volunteer training' video_id='$video_id' id='any-event-dropdown'>Volunteer Training</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='bird absence' video_id='$video_id' id='bird-leave-dropdown'>Bird Absence</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='bird presence' video_id='$video_id' id='bird-return-dropdown'>Bird Presence</a></li>
                                <li class='nav-header'>Self Directed</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - preen'  video_id='$video_id' id='preen-dropdown'>Preen</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - scratch'  video_id='$video_id' id='scratch-dropdown'>Scratch</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - shake'  video_id='$video_id' id='shake-dropdown'>Shake</a></li>
                                <li class='nav-header'>Territorial</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - chase'  video_id='$video_id' id='preen-dropdown'>Chase</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - crouch'  video_id='$video_id' id='preen-dropdown'>Crouch</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - submissive'  video_id='$video_id' id='preen-dropdown'>Submissive</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - predator' video_id='$video_id' id='predator-dropdown'>Predator</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - other animal' video_id='$video_id' id='other-animal-dropdown'>Other Animal</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - nest defense' video_id='$video_id' id='nest-defense-dropdown'>Nest Defense</a></li>
                            </ul>
                        </li>

                        <li class='column-menu span6'>
                            <ul style='list-style-type: none; margin-right:10px;'>
                                <li class='nav-header'>Foraging</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='foraging - on nest'  video_id='$video_id' id='foraging-on-dropdown'>On Nest</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='foraging - off nest' video_id='$video_id' id='foraging-off-dropdown'>Off Nest</a></li>
                                <li class='nav-header'>Locomotion</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='locomotion - walking'  video_id='$video_id' id='walking-dropdown'>Walking</a></li>
                                <li class='nav-header'>Parent Care</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - brood'  video_id='$video_id' id='preen-dropdown'>Brood</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - cool shade'  video_id='$video_id' id='preen-dropdown'>Cool Shade</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - nest exchange'  video_id='$video_id' id='preen-dropdown'>Nest Exchange</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - eggshell removal'  video_id='$video_id' id='preen-dropdown'>Eggshell Removal</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - feeding young'  video_id='$video_id' id='preen-dropdown'>Feeding Young</a></li>
                                <li class='nav-header'>Chick Behavior</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='nest success' video_id='$video_id' id='nest-success-dropdown'>Nest Success</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick presence' video_id='$video_id' id='preen-dropdown'>Chick Presence</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - walking'  video_id='$video_id' id='preen-dropdown'>Walking</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - foraging'  video_id='$video_id' id='preen-dropdown'>Foraging</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - submissive'  video_id='$video_id' id='preen-dropdown'>Submissive</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - running'  video_id='$video_id' id='preen-dropdown'>Running</a></li>
                            </ul>
                        </li>
                    </ul>";

} else if ($species_id == 3) {
    echo "          <ul class='dropdown-menu' id='event-dropdown-$video_id' style='width:350px;'>
                        <li class='column-menu span6 firstcolumn'>
                            <ul style='list-style-type: none; margin-left:10px;'>
                                <li class='nav-header'>Miscellaneous</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='unspecified' video_id='$video_id' id='any-event-dropdown'>Unspecified</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='volunteer training' video_id='$video_id' id='any-event-dropdown'>Volunteer Training</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='bird absence' video_id='$video_id' id='bird-leave-dropdown'>Bird Absence</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='bird presence' video_id='$video_id' id='bird-return-dropdown'>Bird Presence</a></li>
                                <li class='nav-header'>Self Directed</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - preen'  video_id='$video_id' id='preen-dropdown'>Preen</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - scratch'  video_id='$video_id' id='scratch-dropdown'>Scratch</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='self directed - shake'  video_id='$video_id' id='shake-dropdown'>Shake</a></li>
                                <li class='nav-header'>Territorial</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - chase'  video_id='$video_id' id='preen-dropdown'>Chase</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - crouch'  video_id='$video_id' id='preen-dropdown'>Crouch</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - submissive'  video_id='$video_id' id='preen-dropdown'>Submissive</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - predator' video_id='$video_id' id='predator-dropdown'>Predator</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - other animal' video_id='$video_id' id='other-animal-dropdown'>Other Animal</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='territorial - nest defense' video_id='$video_id' id='nest-defense-dropdown'>Nest Defense</a></li>
                            </ul>
                        </li>

                        <li class='column-menu span6'>
                            <ul style='list-style-type: none; margin-right:10px;'>
                                <li class='nav-header'>Foraging</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='foraging - on nest'  video_id='$video_id' id='foraging-on-dropdown'>On Nest</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='foraging - off nest' video_id='$video_id' id='foraging-off-dropdown'>Off Nest</a></li>
                                <li class='nav-header'>Locomotion</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='locomotion - walking'  video_id='$video_id' id='walking-dropdown'>Walking</a></li>
                                <li class='nav-header'>Parent Care</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - brood'  video_id='$video_id' id='preen-dropdown'>Brood</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - cool shade'  video_id='$video_id' id='preen-dropdown'>Cool Shade</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - nest exchange'  video_id='$video_id' id='preen-dropdown'>Nest Exchange</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='parent care - eggshell removal'  video_id='$video_id' id='preen-dropdown'>Eggshell Removal</a></li>
                                <li class='nav-header'>Chick Behavior</li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='nest success' video_id='$video_id' id='nest-success-dropdown'>Nest Success</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick presence' video_id='$video_id' id='preen-dropdown'>Chick Presence</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - walking'  video_id='$video_id' id='preen-dropdown'>Walking</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - foraging'  video_id='$video_id' id='preen-dropdown'>Foraging</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - submissive'  video_id='$video_id' id='preen-dropdown'>Submissive</a></li>
                                <li><a href='javascript:;' class='event-dropdown' event_id='chick behavior - running'  video_id='$video_id' id='preen-dropdown'>Running</a></li>
                            </ul>
                        </li>
                    </ul>";

}

echo "          </div>

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
