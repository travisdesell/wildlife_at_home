<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");


print_header("Wildlife@Home: Image Viewer",  "<link href='./css/canvas_test.css' rel='stylesheet'> <script type='text/javascript' src='./js/canvas_test.js'></script>", "wildlife");
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

//from citizen_science_grid/my_query.php
$result = query_wildlife_video_db("SELECT * FROM images WHERE watermarked = 1 ORDER BY RAND() LIMIT 1");
$row = $result->fetch_assoc();

$image = $row['watermarked_filename'];

echo "
<div class='row'>
    <div class='col-sm-4'>
        <div id='selection-information'>
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
