<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$start_time = 0;

function start_timer($name) {
    global $start_time;

    echo "\n\nStarting test: " . $name;
    $start_time = microtime(true);
}

function end_timer() {
    global $start_time;

    if ($start_time)
        echo "\n\tDone. Total time: " . (microtime(true) - $start_time);
    else
        echo "\n\tNo starting time.";

    $start_time = 0;
}

// number of iterations to run for
$iterations = 20;

// grab that many users
$users = array(1, 120521);
for ($i = 0; $i < ($iterations-2); $i++)
    $users[] = rand(2, 120000);

// setup our projects for testing
$projects = array(
    1 => array(1, 2),
    3 => array(0)
);

// test the recursive lookup
foreach ($projects as $project_id => $species_array) {
    foreach ($species_array as $species_id) {
        if ($species_id > 0)
            $species = "and species=$species_id";
        else
            $species = "";

        start_timer("Recursive lookup: $project_id, $species_id");
        foreach ($users as $user_id) {
            //echo "\n\tUser: $user_id";
            $temp_result = query_wildlife_video_db("select max(id), min(id) from images");
            $row = $temp_result->fetch_assoc();
            $max_int = $row['max(id)'];
            $min_int = $row['min(id)'];

            do {
                $temp_id = mt_rand($min_int, $max_int);
                $result = query_wildlife_video_db("select images.id, archive_filename, watermarked_filename, watermarked, species, year from images left outer join image_observations on images.id = image_observations.image_id where views < needed_views and project_id=$project_id $species and image_observations.user_id is null and images.id = $temp_id");
            } while ($result->num_rows < 1 /*or $temp_result->num_rows > 0*/);
        }
        
        end_timer();
    }
}

// test join option
foreach ($projects as $project_id => $species_array) {
    foreach ($species_array as $species_id) {
        if ($species_id > 0)
            $species = "and species=$species_id";
        else
            $species = "";

        start_timer("Joined lookup: $project_id, $species_id");
        foreach ($users as $user_id) {
//            echo "\n\tUser: $user_id";
            $query = "select images.id as id, images.archive_filename, images.watermarked_filename, images.watermarked, images.species, images.year from images left outer join image_observations on images.id = image_observations.image_id where images.views < images.needed_views and images.project_id=$project_id $species and image_observations.user_id is null limit 100";
            $result = query_wildlife_video_db($query);
        }
        end_timer();
    }
}

echo "\n";
