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

    print_header("Wildlife@Home: Image Viewer",  "<link href='./wildlife_css/review_image.css?v=2017011902' rel='stylesheet'>", "wildlife");
    print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

    $image_id = -1;
    $project_id = 1;
    $species_id = 0;
    $nest_confidence = 0;
    $reload_location = "null";
    $mosaic_projects = array(4, 5, 6);
    $can_reload = 1;
    $year = 0;
    $spoof = false;

    if (isset($_GET['p'])) {
        $project_id = intval($boinc_db->real_escape_string($_GET['p']));
    }
    if (isset($_GET['s'])) {
        $species_id = intval($boinc_db->real_escape_string($_GET['s']));
    }
    if (isset($_GET['y']) && csg_is_special_user($user)) {
        $year = intval($boinc_db->real_escape_string($_GET['y']));
    }
    if (isset($_GET['u']) && csg_is_special_user($user)) {
        $user_id = intval($boinc_db->real_escape_string($_GET['u']));
        $spoof = true;
    }

    $result = NULL;
    $mosaic_number = 0;
    $mosaic_id = 0;
    $mosaic_count = 100;
    $mosaic_empty = 0;
    $mosaic_skipped = 0;
    $mosaic_toskip = 0;
    $spoof_note = "";

    // is_expert?
    $is_expert = query_wildlife_video_db("SELECT COUNT(*) FROM image_observation_experts WHERE user_id=$user_id");
    if ($is_expert && $is_expert->num_rows > 0) {
        $row = $is_expert->fetch_row();
        $is_expert = $row[0] == 1;
    } else {
        die('Unable to connect to database');
    }

    if ($spoof) {
        $spoof_note .= "User: $user_id<br>";
    }

    // project 4 is super special mosaic project
    if (in_array($project_id, $mosaic_projects)) {
        if ($spoof) {
            $spoof_note .= "Mosaic project: $project_id<br>";
        }
            
        $species_id = 2;
        $nest_confidence = 1;

        // see if the user has a non-completed mosaic for this project
        $result = query_wildlife_video_db("SELECT mus.mosaic_image_id AS mosaic_image_id FROM mosaic_user_status AS mus INNER JOIN mosaic_images AS mi ON mi.id = mus.mosaic_image_id WHERE mus.user_id=$user_id AND mus.completed=0 AND mi.project_id=$project_id ORDER BY mosaic_image_id DESC LIMIT 1");

        if ($result->num_rows == 0) {
            // determine the first mosaic from the queue which the user has not submitted all
            if ($is_expert) {
                $view = "view_mosaic_expert_queue";
            } else {
                $view = "view_mosaic_citizen_queue";
            }

            if ($spoof) {
                $spoof_note .= "Finding a new mosaic on $view<br>";
            }

            $result = query_wildlife_video_db("SELECT queue.mosaic_image_id AS mosaic_image_id FROM $view AS queue WHERE queue.project_id = $project_id AND queue.mosaic_image_id NOT IN (SELECT mosaic_image_id FROM mosaic_user_status WHERE user_id = $user_id) LIMIT 1");

            // nothing in the queue?
            if ($result->num_rows == 0) {
                if ($spoof) {
                    $spoof_note .= "No mosaic found in $view<br>";

                    $result = query_wildlife_video_db("SELECT COUNT(*) FROM $view WHERE project_id = $project_id");
                    $num_mosaics = ($result->fetch_assoc())["COUNT(*)"];

                    $result = query_wildlife_video_db("SELECT COUNT(*) FROM mosaic_user_status AS mus INNER JOIN mosaic_images AS mi ON mi.id = mus.mosaic_image_id WHERE mus.completed=1 AND mus.user_id=$user_id AND mi.project_id=$project_id");
                    $num_completed = ($result->fetch_assoc())["COUNT(*)"];

                    $spoof_note .= "<b>$num_completed out of $num_mosaics completed.</b><br>";
                }

                $result = NULL;
            }
        }
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $mosaic_id = $row['mosaic_image_id'];
            $mosaic_number = -1;

            // get the highest number the user has submitted for this mosaic
            $temp_result = query_wildlife_video_db("SELECT msi.number FROM mosaic_split_images AS msi INNER JOIN image_observations AS io ON io.image_id = msi.image_id WHERE msi.mosaic_image_id = $mosaic_id AND io.user_id = $user_id ORDER BY msi.number DESC LIMIT 1");
            if ($temp_result->num_rows > 0) {
                $temp_row = $temp_result->fetch_assoc();
                $mosaic_number = $temp_row['number'];
                if ($spoof) {
                    $spoof_note .= "Found submission $mosaic_id -> #$mosaic_number<br>";
                }
            }

            // get the next image for this mosaic that isn't empty
            $temp_result = query_wildlife_video_db("SELECT number FROM mosaic_split_images WHERE mosaic_image_id=$mosaic_id AND is_empty=0 AND number > $mosaic_number ORDER BY number ASC LIMIT 1");

            // if we don't have another image, the user is done!
            if ($temp_result->num_rows == 0) {
                query_wildlife_video_db("UPDATE mosaic_user_status SET completed=1 WHERE user_id=$user_id AND mosaic_image_id=$mosaic_id");
                $reload_location = "full_mosaic.php?m=$mosaic_id";
                $can_reload = 0;
                $result = NULL;
            } else {
                if ($mosaic_number < 0) {
                    $mosaic_number = 0;
                }

                $temp_row = $temp_result->fetch_assoc();
                $mosaic_new_number = $temp_row['number'];
                $mosaic_skipped = $mosaic_new_number - $mosaic_number;
                $mosaic_number = $mosaic_new_number;

                // how many are left to skip?
                $temp_result = query_wildlife_video_db("SELECT COUNT(*) FROM mosaic_split_images WHERE mosaic_image_id=$mosaic_id AND is_empty=1 AND number > $mosaic_number");
                $temp_row = $temp_result->fetch_assoc();
                $mosaic_toskip = $temp_row['COUNT(*)'];

                // how many are total
                $temp_result = query_wildlife_video_db("SELECT split_count, empty_count FROM mosaic_images WHERE id = $mosaic_id");
                $temp_row = $temp_result->fetch_assoc();
                $mosaic_count = $temp_row['split_count'];
                $mosaic_empty = $temp_row['empty_count'];

                // update the result 
                $result = query_wildlife_video_db("SELECT i.id, archive_filename, watermarked_filename, watermarked, species, year FROM mosaic_split_images AS s INNER JOIN images AS i ON s.image_id = i.id WHERE s.mosaic_image_id = $mosaic_id AND s.number = $mosaic_number");

                if ($spoof) {
                    $spoof_note .= "Number = $mosaic_number, Count = $mosaic_count, ToSkip = $mosaic_toskip, Skipped = $mosaic_skipped, Empty = $mosaic_empty<br>";
                }
            }
        } else {
            $result = NULL;
            $mosaic_number = 0;
            $mosaic_skipped = 0;
            $mosaic_toskip = 0;
        }
    } else {
        // not the mosaic project
        if (array_key_exists('image_id', $_GET)) {
            $image_id = $boinc_db->real_escape_string($_GET['image_id']);
            $result = query_wildlife_video_db("SELECT id, archive_filename, watermarked_filename, watermarked, species, year FROM images WHERE id = $image_id");
        } else {
            $species = '';
            if ($species_id > 0)
                $species = "and iq.species=$species_id";

            /*
            $temp_result = query_wildlife_video_db("select max(id), min(id) from images");
            $row = $temp_result->fetch_assoc();
            $max_int = $row['max(id)'];
            $min_int = $row['min(id)'];

            do {
                $temp_id = mt_rand($min_int, $max_int);
                $result = query_wildlife_video_db("select images.id, archive_filename, watermarked_filename, watermarked, species, year from images left outer join image_observations on images.id = image_observations.image_id where views < needed_views and project_id=$project_id $species and image_observations.user_id is null and images.id = $temp_id");
            } while ($result->num_rows < 1);
             */

            // user our new queue system
            $query = "select i.id, i.archive_filename, i.watermarked_filename, i.watermarked, i.species, i.year from images_queue as iq inner join images as i on iq.image_id = i.id inner join image_observations as io on i.id = io.image_id where iq.project_id = $project_id $species and io.user_id != $user_id ORDER BY rand() LIMIT 1";
            $result = query_wildlife_video_db($query);
        }
    }

    if (!$result || $result->num_rows < 1) {
        echo "
        <div class='container-fluid'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='alert alert-danger' role='alert' id='ajaxalert'>
                    <strong>Error!</strong> Unable to find an available image for project_id=$project_id $species.
        ";

        if ($spoof) {
            echo "<br>$spoof_note";
        }

        echo "
                </div>
            </div>
        </div>
        ";
    } else {

    $row = $result->fetch_assoc();

    $image_id = $row['id'];
    $year = $row['year'];

    // see if we have a shifted version of the image
    $temp_result = query_wildlife_video_db("SELECT archive_filename FROM uas_blueshift_images WHERE image_id=$image_id");
    if ($temp_result && $temp_result->num_rows > 0) {
        $temp_row = $temp_result->fetch_assoc();
        $image_watermarked = 0;
        $image = $temp_row['archive_filename'];
    } else {
        $image_watermarked = $row['watermarked'];
        $image = $image_watermarked ? $row['watermarked_filename'] : $row['archive_filename'];
    }

    // always left trim
    $image = ltrim($image, '/');

    if (in_array($project_id, $mosaic_projects)) {
        $alert_class = 'alert-info';
        $alert_message = "<strong>".($mosaic_count - $mosaic_toskip - $mosaic_number)."</strong> out of <strong>".($mosaic_count - $mosaic_empty)."</strong> remaining for Mosaic #<strong>$mosaic_id</strong>.";
    } else {
        $alert_class = 'alert-info';
        $alert_message = "<strong>Note about boxes!</strong> Try to fit boxes as close to the species as possible (75% or more of the creature should fit in the smalled box; any less and the creature should be ignored). Boxes can shrink (a little) and grow.";
    }

    if ($spoof) {
        $alert_message .= "<br>$spoof_note";
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
                <div class='row' id='row-image-info'>
                    <div class='btn-group btn-group-sm' role='group'>
                        <button type='button' class='btn disabled' disabled><strong>Image #: $image_id</strong></button>
                        <button type='button' id='discuss-button' class='btn btn-primary' data-toggle='tooltip' title='Discuss this image on the forum'>&nbsp;<span class='glyphicon glyphicon-comment'> </span></button>
                    </div>
                    <!-- You are looking at image: $image_id and it is watermarked? $image_watermarked. <br>Year: $year. <br> $image Image ID: $image_id -->
                    <div class='btn-group btn-group-sm pull-right' role='group'>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#helpModal'>Species <span class='glyphicon glyphicon-question-sign'> </span></button>
                        <button type='button' class='btn btn-info' data-toggle='modal' data-target='#interfaceModal'>Interface <span class='glyphicon glyphicon-question-sign'> </span></button>
                    </div>
                </div>
                <div class='row' id='row-selection-info'>
                    <div class='well pre-scrollable' id='selection-info-container'>
                        <div id='selection-information'>
                        </div>
                    </div>
                </div>
                <div class='row' id='row-button-area'>
                    <textarea class='form-control' rows='3' placeholder='Comments' name='comment-area' id='comment-area'></textarea>
                    <br>
                    <div class='text-center'>
                        <div class='btn-group btn-group-lg'>
";

    if (!in_array($project_id, $mosaic_projects)) {
        echo "
                            <button class='btn btn-primary' id='skip-button' data-toggle='tooltip' title='Skip this image'>Skip</button>
";
    }

    echo "
                            <button class='btn nothing btn-danger' id='nothing-here-button' data-toggle='tooltip' title='No animals in this image'>There's Nothing Here</button>
                            <button class='btn btn-primary disabled' id='submit-selections-button' data-toggle='tooltip' title='Submit species to the database' disabled>Submit</button>
                         </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='col-sm-8' id='col-canvas'>
            <div class='row'>
                <div class='col-sm-11' id='canvasContainer'>
                    <canvas id='canvas' width='600' height='400'></canvas>
                </div>
                <div class='col-sm-1'>
                    <div class='progress progress-bar-vertical' id='progress_vertical'>
                        <div class='progress-bar progress-bar-transparent' id='progress_vertical_top'></div>
                        <div class='progress-bar progress-bar-info' id='progress_vertical_middle'></div>
                        <div class='progress-bar progress-bar-transparent' id='progress_vertical_bottom'></div>
                    </div>
                </div>
            </div>
            <div class='row' style='margin-top: 5px'>
                <div class='col-sm-11'>
                    <div class='progress' id='progress_horizontal'>
                        <div class='progress-bar progress-bar-transparent' id='progress_horizontal_left'></div>
                        <div class='progress-bar progress-bar-info' id='progress_horizontal_middle'></div>
                        <div class='progress-bar progress-bar-transparent' id='progress_horizontal_right'></div>
                    </div>
                </div>
                <div class='col-sm-1'>
                    <span id='scale_span'>1.0x</span>
                </div>
            </div>
        </div>
        </div>
    </div>";

    }
        

    print_footer('','');

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
    $project_no = $project_id;
    if (in_array($project_id, $mosaic_projects)) {
        $project_no = 3;
    }
    if (array_key_exists($project_no, $project_species)) {
        $project_objects = $project_species[$project_no];
    }

    if ($project_objects) {
        $m = new Mustache_Engine;
        $project_objects['project_id'] = $project_no;
        echo $m->render($projects_template, $project_objects);
    } else {
        echo '<p>Help for this project coming soon!</p>';
    }

    echo "
                    </div>
                </div>
            </div>
        </div>";

    if (!$spoof) {
    echo "
    <form class='hidden' action='' method='POST' id='submitForm'>
    <input type='hidden' id='submitStart' name='submitStart' value='".time()."'/>
    <input type='hidden' id='submitEnd' name='submitEnd' value='0'/>
    <input type='hidden' id='image_id' name='image_id' value='$image_id'/>
    </form>
        
    <form id='forumPost' class='hidden' action='//csgrid.org/csg/forum_post.php?id=8' method='post' target='_blank'>
        <input type='hidden' id='forumContent' name='content' value=''>
        </form>";
    }


    echo "<script src='./js/jquery.mousewheel.min.js'></script>
    <script src='./js/hammer.min.js'></script>
    <script src='./js/canvas_selector.js'></script>
    <script>
    var imgsrc = 'http://$sharehost/$image';
    var species_id = $species_id;
    var nest_confidence = $nest_confidence;
    var reload_location = '$reload_location';
    var can_reload = $can_reload;
    var project_id = $project_id;
</script>
<script src='./js/review_image.js?v=2017051503'></script>";

?>
