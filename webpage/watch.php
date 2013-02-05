<?php

require_once('../inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: Watching Video</title>

    <!-- For bootstrap -->

    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='description' content=''>
    <meta name='author' content=''>

    <!-- Le styles -->
    <link href='assets/css/bootstrap.css' rel='stylesheet'>
    <link href='assets/css/bootstrap-responsive.css' rel='stylesheet'>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src='http://html5shim.googlecode.com/svn/trunk/html5.js'></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel='apple-touch-icon-precomposed' sizes='144x144' href='assets/ico/apple-touch-icon-144-precomposed.png'>
    <link rel='apple-touch-icon-precomposed' sizes='114x114' href='assets/ico/apple-touch-icon-114-precomposed.png'>
    <link rel='apple-touch-icon-precomposed' sizes='72x72' href='assets/ico/apple-touch-icon-72-precomposed.png'>
    <link rel='apple-touch-icon-precomposed' href='assets/ico/apple-touch-icon-57-precomposed.png'>
    <link rel='shortcut icon' href='assets/ico/favicon.png'>

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src='assets/js/jquery.js'></script>
    <script src='assets/js/bootstrap-transition.js'></script>
    <script src='assets/js/bootstrap-alert.js'></script>
    <script src='assets/js/bootstrap-modal.js'></script>
    <script src='assets/js/bootstrap-dropdown.js'></script>
    <script src='assets/js/bootstrap-scrollspy.js'></script>
    <script src='assets/js/bootstrap-tab.js'></script>
    <script src='assets/js/bootstrap-tooltip.js'></script>
    <script src='assets/js/bootstrap-popover.js'></script>
    <script src='assets/js/bootstrap-button.js'></script>
    <script src='assets/js/bootstrap-collapse.js'></script>
    <script src='assets/js/bootstrap-carousel.js'></script>
    <script src='assets/js/bootstrap-typeahead.js'></script>

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
    </style>


</head>
<body>
";

$active_items = array(
                    'home' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);

//$user = get_logged_in_user();

//echo "<b>Welcome: " . json_encode($user->name) . "!\n";

/*
 * This is a little convoluted, but it will quickly select a random video_segment which has
 * been processed.
 *
 * TODO: select one that has been processed, not validated, and has no observation by the user already.
 *
 * TODO: first select one that has observations by OTHER users, then select one with no observations.
 */

$species_id = mysql_real_escape_string($_GET['species']);
$location_id = mysql_real_escape_string($_GET['site']);

$wildlife_db = mysql_pconnect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$query = "select filename from video_segment_2 AS r1 JOIN (SELECT (RAND() * (SELECT MAX(id) FROM video_segment_2 WHERE processing_status = 'DONE' AND species_id = $species_id AND location_id = $location_id)) AS id) AS r2 WHERE r1.id >= r2.id AND r1.processing_status = 'DONE' AND r1.species_id = $species_id AND r1.location_id = $location_id ORDER BY r1.id ASC limit 1;";

$result = mysql_query($query);
if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

$found = true;
if (!$row) $found = false;

$segment_filename = $row['filename'];

//echo "file: $segment_filename\n";
//echo "species_id: $species_id\n";
//echo "location_id: $location_id\n";

$query = "SELECT long_name FROM locations WHERE id = $location_id\n";
$result = mysql_query($query);
if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

if (!$row) $location_name = 'unknown location';
else $location_name = $row['long_name'];

$query = "SELECT name FROM species WHERE id = $species_id\n";
$result = mysql_query($query);
if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

$row = mysql_fetch_assoc($result);

if (!$row) $species_name = 'unknown species';
else $species_name = $row['name'];


echo"
    <div class='well well-large'>
        <div class='row-fluid'>
            <div class='container'>
                <div class='span6'>";

if ($found) {
    echo "
                        <div class='row-fluid'>
                            <video style='width:100%;' id='wildlife_video' controls='controls' src='http://wildlife.und.edu/$segment_filename'>
                                This video requires a browser that supports HTML5 video.
                            </video>
                        </div>  <!-- row-fluid -->

                        <div class='row-fluid'>
                            <a class='btn btn-small btn-primary span5 pull-left' style='margin-top:0px;' id='fast_backward_button' value='fast backward'>fast backward</a>
                            <div class='span2'>
                            <input style='width:100%; padding:3px; margin:1px;' type='text' id='speed_textbox' value='speed: 1' readonly='readonly'>
                            </div>

                            <a class='btn btn-small btn-primary span5 pull-right' style='margin-top:0px;' id='fast_forward_button' value='fast forward'>fast forward</a>
                        </div>
    ";
} else {
    echo "<p>No videos of " . $species_name . " currently available at $location_name.<p>\n";
    echo "<p>Please go to the <a href = 'video_selector.php'>video selection webpage</a> to select another specices and site.</p>";
}

echo "
                </div>  <!-- span6 -->
            </div>  <!-- container -->
        </div> <!-- row-fluid -->
    </div>  <!-- well -->
    ";

print_footer();

echo "
</body>
</html>
";

?>
