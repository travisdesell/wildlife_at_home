<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

if (!isset($_POST['metadata'])) {
    echo json_encode(array(
        'success' => false,
        'errors' => ['No metadata.'],
        'count' => 0
    ));
    return;
}

// get our metadata
$metadata = $_POST['metadata'];
$nothing_here = $metadata['nothing_here'];
$image_id = $metadata['image_id'];
$comments = $metadata['comments'];
$start_time = $metadata['start_time'];
$submit_time = time();
$duration = $submit_time - $start_time;
$mysqltime = date("Y-m-d H:i:s", $submit_time);

// make sure we don't have this in the db already
$result = query_wildlife_video_db("select * from image_observations where user_id=$user_id and image_id=$image_id");
if ($result->num_rows > 0) {
    echo json_encode(array(
        'success' => false,
        'errors' => ["You've already submitted this image. Please reload and try again."],
        'count' => 0
    ));
    return;
}

$success = false;
$count = 0;
$image_observation_id = NULL;
$errors = array();
if ($nothing_here) {
    $success = query_wildlife_video_db("insert into image_observations (user_id, image_id, nothing_here, submit_time, duration) values ($user_id, $image_id, 1, '$mysqltime', $duration)");

    if (!$success) {
        echo json_encode(array(
            'success' => false,
            'errors' => ['Unable to insert metadata.'],
            'count' => 0
        ));
        return;
    }

    $image_observation_id = $wildlife_db->insert_id;
} else {
    // make sure we have boxes
    if (!isset($_POST['boxes'])) {
        echo json_encode(array(
            'success' => false,
            'errors' => ['No boxes defined.'],
            'count' => 0
        ));
        return;
    }

    // insert our metadata information first
    $success = query_wildlife_video_db("insert into image_observations (user_id, image_id, nothing_here, submit_time, duration) values ($user_id, $image_id, 0, '$mysqltime', $duration)");

    if (!$success) {
        echo json_encode(array(
            'success' => false,
            'errors' => ['Unable to insert metadata.'],
            'count' => 0
        ));
        return;
    }

    $image_observation_id = $wildlife_db->insert_id;

    $data = $_POST['boxes'];
    for ($i = 0; $i < count($data); $i++) {
        $height = (int)$data[$i]['height'];
        $width = (int)$data[$i]['width'];
        $x = (int)$data[$i]['x'];
        $y = (int)$data[$i]['y'];
        $species_id = (int)$data[$i]['species_id'];
        $on_nest = (int)$data[$i]['on_nest'];
        
        $temp_success = query_wildlife_video_db("INSERT INTO image_observation_boxes (image_observation_id, species_id, x, y, width, height, on_nest) values ($image_observation_id, $species_id, $x, $y, $width, $height, $on_nest)");
        if ($temp_success) $count++;
        else $errors[] = "Unable to insert box index $i.";
    }
}

// add comments, if needed
if ($success && $metadata['comments']) {
    $comments = mysql_escape_string($metadata['comments']);

    $temp_success = query_wildlife_video_db("INSERT INTO image_observation_comments (image_observation_id, comment) values ($image_observation_id, $comments)");
    if (!$temp_success) $errors[] = 'Unable to insert comments.';
}

// finally, update our table on success and return the count
if ($success) {
    query_wildlife_video_db("UPDATE images SET views = views + 1 WHERE id=$image_id");
}

echo json_encode(array(
    'success' => $success,
    'errors' => $errors,
    'count' => $count
));
?>
