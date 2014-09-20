<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");


print_header("Wildlife@Home: Image Viewer",  "<link href='./css/canvas_test.css' rel='stylesheet'> <script type='text/javascript' src='./js/canvas_test.js'></script>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home");

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

echo "
<div class='row'>
    <div class='col-sm-4'>
        <div id='selection-information'>
        </div>
        <button class='btn btn-primary' id='submit-selections-button'>Submit</button>
    </div>

    <div class='col-sm-8'>
        <div id='canvas'>
            <img class='img-responsive' src='./images/nd_pred_coyote.jpg'></img>
        </div>
    </div>
</div>
";


print_footer();


?>
