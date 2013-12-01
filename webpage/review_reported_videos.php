<?php

require_once('/home/tdesell/wildlife_at_home/webpage/display_badges.php');
require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Reviewing Reported Video</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript'>
        var reviewing_reported = true;
    </script>
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

        .tab-right {
           position: absolute;
           top: -1px;
           right: -1px;
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

    </style>
";

$user = get_user();
$user_id = $user['id'];
$user_name = $user['name'];

$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'project_management' => 'active',
                    'community' => ''
                );

print_navbar($active_items);

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

if (!is_special_user__fixme($user)) {
    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>
                        <p> Sorry, this page is only accessible for project scientists.</p>
                    </div>
                </div>
            </div>
        </div>";

    print_footer();
    echo "</body></html>";
    die();
}

$species_id = $_GET['species_id'];

if ($species_id != '1' && $species_id != '2' && $species_id != '3') {
    $species_filter = "";
} else {
    $species_filter = " AND species_id = $species_id";
}

$video_segment_id = -1;
if (array_key_exists('video_segment_id', $_GET)) {
    $species_filter = "";
    $video_segment_id = $_GET['video_segment_id'];
}

//echo "<p>species filter: '$species_filter', species_id: '$species_id'</p>";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query ="";

if ($video_segment_id >= 0) {
    $query = "SELECT id, filename, duration_s, video_id, species_id, location_id FROM video_segment_2 vs2 WHERE id = $video_segment_id";
} else {
    $query = "SELECT id, filename, duration_s, video_id, species_id, location_id FROM video_segment_2 vs2 WHERE report_status = 'REPORTED' $species_filter ORDER BY RAND() limit 1";
}

//echo "<!-- $query -->\n";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

$found = true;
if (!$row) {
   $found = false;

    echo "
        <div class='well well-large' style='padding-top: 10px; padding-bottom: 0px; margin-top: 3px; margin-bottom: 5px'> 
            <div class='row-fluid'>
                <div class='container'>
                    <div class='span12'>";

   if ($species_id == '1') {
       echo "<p>There are no unreviewed reported videos for the Sharptailed Grouse.</p>";
   } else if ($species_id == '2') {
       echo "<p>There are no unreviewed reported videos for the Interior Least Tern.</p>";
   } else if ($species_id == '2') {
       echo "<p>There are no unreviewed reported videos for the Piping Plover.</p>";
   } else if ($species_id == '2') {
       echo "<p>There are no unreviewed reported videos.</p>";
   }

    echo "           </div>
                </div>
            </div>
        </div>";

    print_footer();
    echo "</body></html>";

   die();
}

$video_id = $row['video_id'];
$segment_filename = $row['filename'];
$duration_s = $row['duration_s'];
$species_id = $row['species_id'];
$location_id = $row['location_id'];
$video_segment_id = $row['id'];

$start_time = time();

echo "<script type='text/javascript'>
    var user_id = $user_id; 
    var species_id = $species_id;
    var location_id = $location_id;
    var video_segment_id = " . $row['id'] . ";
    var start_time = $start_time;
    var duration_s = $duration_s;
</script>";

if ($found) {
    $video2_query = "SELECT animal_id FROM video_2 WHERE id = " . $row['video_id'];
    $video2_result = attempt_query_with_ping($video2_query, $wildlife_db);
    if (!$video2_result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $video2_query\n");

    $video2_row = mysql_fetch_assoc($video2_result);
    $animal_id = $video2_row['animal_id'];
}

echo "
</head>
<body>
";

//
//echo "file: $segment_filename\n";
//echo "species_id: $species_id\n";
//echo "location_id: $location_id\n";

$query = "SELECT long_name FROM locations WHERE id = $location_id\n";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

if (!$row) $location_name = 'unknown location';
else $location_name = $row['long_name'];

$query = "SELECT name FROM species WHERE id = $species_id\n";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

if (!$row) $species_name = 'unknown species';
else $species_name = $row['name'];

$query = "SELECT count(*) FROM video_segment_2 WHERE report_status = 'REPORTED'";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$awaiting_review = $row['count(*)'];


echo"
    <div class='well well-small' style='margin-top:0px;'>
        <div class='tab'>$animal_id - " . trim(substr($segment_filename, strrpos($segment_filename, '/') + 1)) . "</div>
        <div class='tab-right'>$awaiting_review videos are awaiting review</div>

        <div class='row-fluid'>
            <div class='container'>
                <div class='span6'>";

if ($found) {
    echo "
                        <div class='row-fluid'>
                            <video style='width:100%;' id='wildlife_video' controls='controls' preload='auto'>
                                <source src='http://wildlife.und.edu/$segment_filename.mp4' type='video/mp4'></source>
                                <source src='http://wildlife.und.edu/$segment_filename.ogv' type='video/ogg'></source>
                                This video requires a browser that supports HTML5 video.
                            </video>
                        </div>  <!-- row-fluid -->

                        <div class='row-fluid'>
                            <a class='btn btn-primary span5 pull-left' style='margin-top:0px;' id='fast_backward_button' value='fast backward'>fast backward</a>

                            <div class='span2'>
                            <input style='width:100%; padding:3px; margin:1px;' type='text' id='speed_textbox' value='speed: 1' readonly='readonly'>
                            </div>

                            <a class='btn btn-primary span5 pull-right' style='margin-top:0px;' id='fast_forward_button' value='fast forward'>fast forward</a>
                        </div>

    ";

} else {
    echo "<p>No unvalidated videos of " . $species_name . " currently available at $location_name.<p>\n";
    echo "<p>Please go to the <a href = 'video_selector.php'>video selection webpage</a> to select another species and site.</p>";
}

echo "
                </div>  <!-- span6 -->
                <div class='span6'>";

function print_selection_row($text, $id) {
    echo "<div class='row-fluid'>";
    echo "  <div class='span4'>";
    echo "    <div class ='btn-group'>";
    echo "      <button class='btn' id='" . $id . "_yes'>yes</button>";
    echo "      <button class='btn' id='" . $id . "_no'>no</button>";
    if ($id != "interesting") {
        echo "      <button class='btn' id='" . $id . "_unsure'>unsure</button>";
    }
    echo "    </div>";
    echo "  </div>";
    echo "  <div class='span7'> <p style='margin-top:6px; margin-bottom-2px;'> $text </p> </div>";
    echo "  <div class='span1'> <span class='badge badge-info pull-left' style='margin-top:8px' id='" .  $id . "_help'>?</span> </div>";
    echo " </div>";
}

print_selection_row("Parent leaves the nest.", "bird_leave");
print_selection_row("Parent returns to the nest.", "bird_return");
print_selection_row("Parent present at the nest.", "bird_presence");
print_selection_row("Parent absent from the nest.", "bird_absence");
print_selection_row("Predator at the nest.", "predator_presence");
print_selection_row("Nest defense.", "nest_defense");
print_selection_row("Nest success (eggs hatching).", "nest_success");
print_selection_row("Chicks present at the nest.", "chick_presence");
print_selection_row("Display this on our instructional training page?", "interesting");


$discuss_video_content = "I would like to discuss this video:\n" . "[" . "video" . "]" . $segment_filename . "[/video" . "]";

//I would like to discuss this video:\n \[video\]$segment_filename\[/video\]\"></input>

echo "
                    <div class='row-fluid'>
                        <div class='span12'>
                            <p style='padding-top:8px;'>
                            Enter a descriptive explanation of the correct markings:
                            </p>
                        </div>
                    </div>

                    <div class='row-fluid'>
                        <input class='span12' type='text' name='comments' id='comments' style='margin-top:-4px; margin-bottom:5px;'/>
                    </div>

                    <div class='row-fluid pull-down'>
                        <a class='btn btn-primary pull-right disabled' style='margin-top:0px;' id='submit_button' value='submit' 'data-toggle='modal'>submit</a>

                        <a class='btn btn-success pull-right span3' style='margin-top:0px; margin-right:5px;' id='valid-report-button'>valid report</a>
                    </div>

                    <div id = 'submit-modal' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='submit-modal-label'>
                        <div class='modal-header'>
                        </div>

                        <div class='modal-body' id='submit-modal-body'>
                            <p>This is the content of the modal!</p>
                        </div>

                        <div class='modal-footer'>
                            <form id='discuss-video-form' action='forum_post.php?id=8' method='post'>
                            <input type='hidden' name='content' value=\"$discuss_video_content\">
                            </form>

                            <button class= 'btn pull-left' data-dismiss='modal' aria-hidden='true' id='discuss-video-button'>Discuss This Video</button>
                            <button class ='btn btn-primary pull-right' data-dismiss='modal' aria-hidden='true' id='another-video-button'>Next Video</button>
                        </div>
                    </div>

                </div> <!-- span6 -->
            </div>  <!-- container -->
        </div> <!-- row-fluid -->
    </div>  <!-- well -->";

/**
 *  Display the comments for why the video was incorreclty marked.
 */
$query = "SELECT report_comments, reporter_id, reporter_name FROM reported_video WHERE video_segment_id = $video_segment_id";
$result = mysql_query($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
$row = mysql_fetch_assoc($result);

$reporter_name = $row['reporter_name'];
$reporter_id = $row['reporter_id'];
$report_comments = $row['report_comments'];

echo "
<div class='well well-small' style='padding-top:10px; padding-bottom:10px;'>
<div class='row' style='margin-left:0px;'>

<div class='accordion' id='parent-video-accordion'>
    <div class='accordion-group'>
        <div class='accordion-heading'>
            <a class='accordion-toggle' data-toggle='collapse' data-parent='#parent-video-accordion' href='#parent_video_collapse' video_id='$video_id'>Show Parent Video</a>
        </div>

        <div id='parent_video_collapse' class='accordion-body collapse'>
            <div class='accordion-inner' id='parent_video_collapse_inner'>
                uninitialized
            </div>
        </div>
    </div>
</div>

</div>
</div>
    ";

echo "
    <div class='well well-large' style='padding-top:5px; padding-bottom:0px;'>
        <p>This video was reported by $reporter_name with the following description:</p> 
        <div class='row-fluid'>
        <textarea readonly style='width:97%;' rows=2 class='report-comments' id='report-comments-$video_segment_id' video_segment_id=$video_segment_id> $report_comments </textarea>
        </div>
    </div>
    ";

/**
 *  Display all the user observations in a table.
 */
$query = "SELECT * FROM observations WHERE video_segment_id = $video_segment_id";
$result = mysql_query($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

function set_marks(&$value, $bool = false) {
    if ($value == 1) {
        $value = '&#x2713;';
    } else if ($value == 0) {
        if ($bool) {
            $value = 'x';
        } else {
            $value = '?';
        }
    } else if ($value == -1) {
        $value = 'x';
    }
}

$observations['observations'] = array();

while ($observation_row = mysql_fetch_assoc($result)) {
    set_marks($observation_row['interesting']);
    set_marks($observation_row['bird_leave']);
    set_marks($observation_row['bird_return']);
    set_marks($observation_row['bird_presence']);
    set_marks($observation_row['bird_absence']);
    set_marks($observation_row['predator_presence']);
    set_marks($observation_row['nest_defense']);
    set_marks($observation_row['nest_success']);
    set_marks($observation_row['chick_presence']);
    set_marks($observation_row['video_issue'], true);

    $observation_row['user_id'] = get_user_from_id($observation_row['user_id'])->name;
    $observations['observations'][] = $observation_row;
}

$observation_table_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/observation_table_template.html");
$mustache_engine = new Mustache_Engine;


echo "
    <div class='well well-small' style='padding-top:10px; padding-bottom:0px;'>";
echo $mustache_engine->render($observation_table_template, $observations);
echo "
    </div>
    ";



print_footer();

echo "
</body>
</html>
";


?>
