<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

print_header("Wildlife@Home: Image Viewer",  "<link href='./wildlife_css/review_image.css' rel='stylesheet'>", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

$mosaic_id = 0;
if (isset($_GET['m'])) {
    $mosaic_id = $boinc_db->real_escape_string($_GET['m']);
}

$result = NULL;
$mosaic_count = 0;

if ($mosaic_id == 0) {
    // nothing
} else {
    $result = query_wildlife_video_db("SELECT COUNT(*) FROM mosaic_images AS m INNER JOIN mosaic_split_images AS s ON m.id = s.mosaic_image_id INNER JOIN images AS i ON s.image_id = i.id INNER JOIN image_observations as o ON i.id = o.image_id INNER JOIN image_observation_boxes AS b ON o.id = b.image_observation_id WHERE m.id = $mosaic_id AND o.user_id = $user_id"); 

    if ($result->num_rows > 0) {
        // determine the first mosaic for which the user has not submitted all
        while ($row = $result->fetch_assoc()) {
            $mosaic_count = $row['COUNT(*)'];
        }
    }
}

$alert_class = 'alert-danger';
$alert_message = "<strong>Error!</strong> Unable to find responses for Mosaic <strong>#$mosaic_id</strong>";
if ($mosaic_count) {
    $alert_class = 'alert-success';
    $alert_message = "<strong>Awesome Job!</strong> You found <strong>$mosaic_count</strong> objects in Mosaic <strong>#$mosaic_id</strong>";
}

echo "
<div class='container-fluid'>
    <div class='row'>
        <div class='col-sm-12'>
            <div class='alert $alert_class' role='alert' id='ajaxalert'>
                $alert_message
            </div>
        </div>
    </div>
    <div class='row'>
        <div class='col-sm-12'>
            Be a boss - <a href='review_image.php?p=4'>Do another mosaic!</a>
        </div>
    </div>
</div>
";

print_footer();
?>
