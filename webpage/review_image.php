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

$image_id = -1;
$project_id = 1;
$species_id = 0;
$nest_confidence = 0;
if (isset($_GET['p'])) {
    $project_id = $boinc_db->real_escape_string($_GET['p']);
}
if (isset($_GET['s'])) {
    $species_id = $boinc_db->real_escape_string($_GET['s']);
}

$result = NULL;
$mosaic_number = 0;
$mosaic_id = 0;

// project 4 is super special mosaic project
if ($project_id == 4) {
    $species_id = 2;
    $nest_confidence = 1;

    // see which mosaics this user has submitted for
    $result = query_wildlife_video_db("SELECT DISTINCT m.id FROM image_observations AS io JOIN images AS i ON io.image_id = i.id JOIN mosaic_split_images AS s ON io.image_id = s.image_id JOIN mosaic_images AS m ON s.mosaic_image_id = m.id WHERE io.user_id = $user_id");
    if ($result->num_rows > 0) {
        // determine the first mosaic for which the user has not submitted all
        while ($row = $result->fetch_assoc()) {
            $mosaic_id = $row['id'];
            $temp_result = query_wildlife_video_db("SELECT MAX(s.number) FROM mosaic_split_images AS s JOIN image_observations AS io ON io.image_id = s.image_id WHERE s.mosaic_image_id = $mosaic_id AND io.user_id = $user_id");

            if ($temp_result->num_rows < 1)
                continue;

            // if we found one that the user hasn't completed, stick to this mosaic
            $temp_row = $temp_result->fetch_assoc();
            $number = $temp_row['MAX(s.number)'];
            if ($number < 99) {
                // get the actual next value
                $mosaic_number = $number + 1;
                break;
            }

            // reset the mosaic_id and number
            $mosaic_id = 0;
            $mosaic_number = 0;
        }
    }

    // if we don't have a mosaic_id, we need to select a random mosaic
    if ($mosaic_id < 1) {
        $result = query_wildlife_video_db("SELECT m.id FROM mosaic_images as m JOIN mosaic_split_images AS s ON m.id = s.mosaic_image_id JOIN images AS i ON s.image_id = i.id LEFT OUTER JOIN image_observations AS io ON (s.image_id = io.image_id AND io.user_id = 100) WHERE s.number = 0 AND i.views < i.needed_views AND io.user_id IS NULL ORDER BY rand() LIMIT 1");

        // no mosaics left for this user!
        if ($result->num_rows < 1) {
            $result = NULL;
        } else {
            $row = $result->fetch_assoc();
            $mosaic_id = $row['id'];
            $mosaic_number = 0;
        }
    }

    if ($result) {
        $result = query_wildlife_video_db("SELECT i.id, archive_filename, watermarked_filename, watermarked, species, year FROM mosaic_split_images AS s JOIN images AS i ON s.image_id = i.id WHERE s.mosaic_image_id = $mosaic_id AND s.number = $mosaic_number");
    }
} else {
    if (array_key_exists('image_id', $_GET)) {
        $image_id = $boinc_db->real_escape_string($_GET['image_id']);
        $result = query_wildlife_video_db("SELECT id, archive_filename, watermarked_filename, watermarked, species, year FROM images WHERE id = $image_id");
    } else {
        $species = '';
        if ($species_id > 0)
            $species = "and species=$species_id";

        $temp_result = query_wildlife_video_db("select max(id), min(id) from images");
        $row = $temp_result->fetch_assoc();
        $max_int = $row['max(id)'];
        $min_int = $row['min(id)'];

        do {
            $temp_id = mt_rand($min_int, $max_int);
            $result = query_wildlife_video_db("select images.id, archive_filename, watermarked_filename, watermarked, species, year from images left outer join image_observations on images.id = image_observations.image_id where views < needed_views and project_id=$project_id $species and image_observations.user_id is null and images.id = $temp_id");
        } while ($result->num_rows < 1);
    }
}

if (!$result || $result->num_rows < 1) {
    echo "
    <div class='container-fluid'>
    <div class='row'>
        <div class='col-sm-12'>
            <div class='alert alert-error' role='alert' id='ajaxalert'>
                <strong>Error!</strong> Unable to find an available image for project_id=$project_id $species.
            </div>
        </div>
    </div>
    ";
} else {

$row = $result->fetch_assoc();

$image_id = $row['id'];
$image_watermarked = $row['watermarked'];
$image = $image_watermarked ? $row['watermarked_filename'] : $row['archive_filename'];
$year = $row['year'];

if ($project_id == 4) {
    $alert_class = 'alert-info';
    $alert_message = "Completed <strong>$mosaic_number</strong> out of <strong>100</strong> for Mosaic #<strong>$mosaic_id</strong>.";
} else {
    $alert_class = 'alert-info';
    $alert_message = "<strong>Note about boxes!</strong> Try to fit boxes as close to the species as possible to help our computers learn to automate the detection.";
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
    <div class='col-sm-4'>
        <div class='container-fluid'>
            <div class='row'>
            <div id='selection-information'>
                    <div class='btn-group btn-group-sm' role='group'>
                        <button type='button' class='btn disabled' disabled><strong>Image #: $image_id</strong></button>
                        <button type='button' id='discuss-button' class='btn btn-primary' data-toggle='tooltip' title='Discuss this image on the forum'>&nbsp;<span class='glyphicon glyphicon-comment'> </span></button>
                    </div>
                    <!-- You are looking at image: $image_id and it is watermarked? $image_watermarked. <br>Year: $year. <br> $image Image ID: $image_id -->
                    <div class='btn-group btn-group-sm pull-right' role='group'>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#helpModal'>Species <span class='glyphicon glyphicon-question-sign'> </span></button>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#interfaceModal'>Interface <span class='glyphicon glyphicon-question-sign'> </span></button>
                    </div>
                    <br><br>
                 </div>
                 </div>
            <div class='row'>
                <textarea class='form-control' rows='3' placeholder='Comments' name='comment-area' id='comment-area'></textarea>
                <br>
                <div class='text-center'>
                    <div class='btn-group btn-group-lg'>
                        <button class='btn btn-primary' id='skip-button' data-toggle='tooltip' title='Skip this image'>Skip</button>
                        <button class='btn nothing btn-danger' id='nothing-here-button' data-toggle='tooltip' title='No animals in this image'>There's Nothing Here</button>
                        <button class='btn btn-primary disabled' id='submit-selections-button' data-toggle='tooltip' title='Submit species to the database' disabled>Submit</button>
                     </div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-sm-8' id='canvasContainer'>
        <canvas id='canvas' width='600' height='400'>
        </canvas>
    </div>
    </div>
</div>";

}
	

print_footer();

echo "<div id='submitModal' class='modal fade' data-backdrop='static'>
		<div class='modal-dialog modal-sm' role='dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					<h4 class='modal-title''>Submission Complete</h4>
				</div>
					<div class='modal-body'>
						<p>Thank you!</p>
					</div>
					<div class='modal-footer'>
						<button id='modalSubButton' type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>
					</div>
			</div>
		</div>
	</div>
	<div id='interfaceModal' class='modal fade' style='height: 80%'>
		<div class='modal-dialog modal-lg' role='dialog'>
			<div class='modal-content'>
                <div class='modal-header'>
				    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>X</button>
					<h4 class='modal-title''>Interface Help</h4>
                </div>
                <div class='modal-body' style='overflow-y: scroll'>
                    <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Action</th>
                        <th>Result</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><span class='glyphicon glyphicon-hand-up'></span> <strong>(x2)</strong></td>
                        <td>Double-tap / Double-click</td>
                        <td>Creates a new box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-hand-up'></span> <strong>(x3)</strong></td>
                        <td>Triple-tap / Triple-click</td>
                        <td>Deletes a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-full'></span></td>
                        <td>Zoom / Scroll Up</td>
                        <td>Zooms the image in</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-small'></span></td>
                        <td>Zoom / Scroll Down</td>
                        <td>Zooms the image out</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-move'></span></td>
                        <td>Tap / Click and Drag</td>
                        <td>Moves a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-vertical'></span></td>
                        <td>Tap / Click on Top or Bottom and Drag</td>
                        <td>Adjusts the height of a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-resize-horizontal'></span></td>
                        <td>Tap / Click on Side and Drag</td>
                        <td>Adjust the width of a box</td>
                    </tr>
                    <tr>
                        <td><span class='glyphicon glyphicon-fullscreen'></span></td>
                        <td>Tap / Click on Corner and Drag </td>
                        <td>Adjust the height and width of a box</td>
                    </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
	<div id='helpModal' class='modal fade' style='height: 80%'>
		<div class='modal-dialog modal-lg' role='dialog'>
			<div class='modal-content'>
				<div class='modal-header'>
					 <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>X</button>
					<h4 class='modal-title''>Species Help</h4>
				</div>
                <div class='modal-body' style='overflow-y: scroll'>
";


$projects_template = file_get_contents($cwd[__FILE__] . "/templates/species_description_popup.html");

require_once($cwd[__FILE__] . "/image_species.php");

$project_objects = NULL;
if (array_key_exists($project_id, $project_species)) {
    $project_objects = $project_species[$project_id];
}

if ($project_objects) {
    $m = new Mustache_Engine;
    $project_objects['project_id'] = $project_id;
    echo $m->render($projects_template, $project_objects);
} else {
    echo '<p>Help for this project coming soon!</p>';
}

echo "
                </div>
			</div>
		</div>
    </div>";

echo "
<form class='hidden' action='' method='POST' id='submitForm'>
<input type='hidden' id='submitStart' name='submitStart' value='".time()."'/>
<input type='hidden' id='submitEnd' name='submitEnd' value='0'/>
<input type='hidden' id='image_id' name='image_id' value='$image_id'/>
</form>
    
<form id='forumPost' class='hidden' action='//csgrid.org/csg/forum_post.php?id=8' method='post' target='_blank'>
    <input type='hidden' id='forumContent' name='content' value=''>
</form>";

echo "<script src='./js/jquery.mousewheel.min.js'></script>
<script src='./js/hammer.min.js'></script>
<script src='./js/canvas_selector.js'></script>
<script>
    var imgsrc = 'http://wildlife.und.edu/$image';
    var species_id = $species_id;
    var nest_confidence = $nest_confidence;
</script>
<script src='./js/review_image.js'></script>";

?>
