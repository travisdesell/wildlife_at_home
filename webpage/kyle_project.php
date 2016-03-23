<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Citizen Detection of Animals in Aerial Imagery", "<link href='./wildlife_css/canvas_marshall.css' rel='stylesheet'> <script type='text/javascript' src='./js/canvas_test_marshall.js'></script>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>
    <div class='row'>
        <div class='col-sm-12'>
            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Citizen Detection of Animals in Aerial Imagery <small>by Marshall Mattingly</small></h2>
                </div>
            </section>

            <section id='image' class='well'>";

$query = "SELECT *
  FROM tblSplitImages AS r1 JOIN
       (SELECT CEIL(RAND() *
                     (SELECT MAX(splitImageId)
                        FROM tblSplitImages)) AS splitImageId)
        AS r2 
 WHERE r1.splitImageId >= r2.splitImageId
 ORDER BY r1.splitImageId ASC
 LIMIT 1";

///*
$result = query_uas_db($query);
$row = $result->fetch_assoc();

$splitImageId = $row['splitImageId'];
$imageId = $row['imageId'];
$image_name = $row['name'];

$query = "SELECT
    img.timestamp, img.latitude, img.longitude,
    img.height, img.yaw, img.pitch, img.roll,
    flight.name
    FROM tblImages AS img JOIN
        tblFlights AS flight
    WHERE img.imageId = ${imageId} AND
        img.flightId = flight.flightId";

$result = query_uas_db($query);
$row = $result->fetch_assoc();

$image_timestamp = $row['timestamp'];
$image_latitude = $row['latitude'];
$image_longitude = $row['longitude'];
$image_height = $row['height'];
$image_yaw = $row['yaw'];
$image_pitch = $row['pitch'];
$image_roll = $row['roll'];
$flight_name  = $row['name'];
//*/

/*
$min = 1;
$max = 1200;

$splitImageId = rand($min, $max);
$splitImageNumber = rand(1, 25);
$image_name = "1_${splitImageId}_${splitImageNumber}.jpg";
 */

echo "
            <div class='row'>
                <div class='col-sm-4'>
                    <div id='selection-information'>
                    <!-- You are looking at image: $splitImageId. --> 
                        <table>
                        <tr><td style='min-width:135px'><b>Split Image:</b></td><td> ${image_name}</td></tr>
                        <tr><td><b>Flight:</b></td><td> ${flight_name}</td></tr>
                        <tr><td><b>Time:</b></td><td> ${image_timestamp}</td></tr>
                        <tr><td><b>Lat/Long/Height:</b></td><td> ${image_latitude} / ${image_longitude} / ${image_height}</td></tr>
                        <tr><td><b>Yaw/Pitch/Roll:</b></td><td> ${image_yaw} / ${image_pitch} / ${image_roll}</td></tr>
                        </table>
                    </div>
                    <textarea class='nothing-here-box' type='text' size='34' maxlength='512' value ='' id='comments' placeholder='comments' row='1'></textarea><br>
                    <button class='btn btn-primary' id='skip-button'>Skip</button>
                    <button class='btn btn-primary' id='submit-selections-button'>Submit</button>
                </div>
                <div class='col-sm-8' onselectstart='return false' ondragstart='return false'>
                    <div id='canvas'>
                        <img class='img-responsive' id='$splitImageId'  src='images/uas/$image_name'></img>
                    </div>
                </div>
            </div>
        </section>";

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";


?>
