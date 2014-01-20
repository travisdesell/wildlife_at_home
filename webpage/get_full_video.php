<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');
require_once($cwd . '/get_expert_observation_table.php');
require_once($cwd . '/user.php');

if (!is_special_user__fixme()) {
    error_log("non project scientists cannot run this script.");
    die();
}

$video_id = mysql_real_escape_string($_POST['video_id']);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "SELECT watermarked_filename FROM video_2 WHERE id = $video_id";
error_log($query);

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$video_file = $row['watermarked_filename'];

echo "
    <div class='row-fluid'>
        <div class='span6' id='wildlife-video-span-$video_id'>
            <div class='row-fluid'>
                <video style='width:100%;' id='wildlife-video-$video_id' controls='controls' preload='auto'>
                    <source src=\"http://wildlife.und.edu/$video_file.mp4\" type=\"video/mp4\">
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
        </div>
    </div>"; 
?>
