<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");


print_header("Wildlife@Home: Image Viewer",  "<link href='./wildlife_css/canvas_test.css' rel='stylesheet'> <script type='text/javascript' src='./js/canvas_test.js'></script>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

function container_start() {
    echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='well'>";

}

function container_end() {
    echo "
                </div> <!-- well -->
            </div> <!-- col-sm-12 -->
        </div> <!-- row -->
    </div> <!-- /container -->";
}

/*
container_start();

echo "
<div class='row'>
    <div class='col-sm-8'>
        <div id='canvas'>
            <img class='img-responsive' src='./images/nd_pred_coyote.jpg'></img>
        </div>
    </div>

    <div class='col-sm-4'>
        <div id='selection-information'>
        </div>
        <button class='btn btn-primary' id='submit-selections-button'>Submit</button>

    </div>
</div>";

container_end();
*/

//$result = query_wildlife_video_db("SELECT * FROM images WHERE watermarked = 1 ORDER BY RAND() LIMIT 1");
//$row = $result->fetch_assoc();

//from citizen_science_grid/my_query.php
//this is supposedly much faster, as per: http://akinas.com/pages/en/blog/mysql_random_row/
$offset_result = query_wildlife_video_db( " SELECT FLOOR(RAND() * COUNT(*)) AS `offset` FROM images");
$offset_row = $offset_result->fetch_assoc();
$offset = $offset_row['offset'];

$result = query_wildlife_video_db("SELECT id, watermarked_filename, watermarked FROM images LIMIT $offset, 1 ");
$row = $result->fetch_assoc();

$image_id = $row['id'];
$image_watermarked = $row['watermarked'];
$image = $row['watermarked_filename'];

echo "
<div class='row'>
    <div class='col-sm-4'>
        <div id='selection-information'>
            You are looking at image: $image_id and it is watermarked? $image_watermarked.
        </div>
        <button class='btn btn-primary' id='submit-selections-button'>Submit</button>
    </div>

    <div class='col-sm-8'>
        <div id='canvas'>
            <img class='img-responsive' src='http://wildlife.und.edu/$image'></img>
        </div>
    </div>
</div>
";


print_footer();


?>
