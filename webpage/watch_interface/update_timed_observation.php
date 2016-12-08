<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../../citizen_science_grid/my_query.php');
require_once($cwd[__FILE__] . '/../../../citizen_science_grid/user.php');
require_once($cwd[__FILE__] . '/../watch_interface/observation_table.php');

require $cwd[__FILE__] . '/../../../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$user = csg_get_user();
$user_id = $user['id'];

$observation_id = $boinc_db->real_escape_string($_POST['observation_id']);
$video_id = $boinc_db->real_escape_string($_POST['video_id']);
$event_id  = $boinc_db->real_escape_string($_POST['event_id']);
$start_time = $boinc_db->real_escape_string($_POST['start_time']);
$end_time = $boinc_db->real_escape_string($_POST['end_time']);
$start_time_s = $boinc_db->real_escape_string($_POST['start_time_s']);
$end_time_s = $boinc_db->real_escape_string($_POST['end_time_s']);
$tags = $boinc_db->real_escape_string($_POST['tags']);

$comments = $_POST['comments'];
//error_log("comments: '$comments'");
//$comments = mysqli_real_escape_string($wildlife_db, $comments);
//error_log("escaped comments: '" . $comments . "'");

//$comments = str_replace('\'', '\'', $comments);
//error_log("str replaced: '$comments'");

$query = "SELECT species_id FROM video_2 WHERE id = $video_id";
$result = query_wildlife_video_db($query);
$row = $result->fetch_assoc();
$species_id = $row['species_id'];

//$query = "UPDATE timed_observations SET start_time = \"$start_time\", end_time = \"$end_time\", start_time_s = $start_time_s, end_time_s = $end_time_s, event_id =\"$event_id\", comments = \"$comments\", tags = \"$tags\" WHERE id = $observation_id";

//error_log("start time: '$start_time', end time: '$end_time'");

$query = "UPDATE timed_observations SET start_time = :start_time, end_time = :end_time, start_time_s = :start_time_s, end_time_s = :end_time_s, event_id =:event_id, comments = :comments, tags = :tags WHERE id = :observation_id";
//$bind_params = array( 'start_time' => $start_time, 'end_time' => $end_time, 'start_time_s' => $start_time_s, 'end_time_s' => $end_time_s, 'event_id' => $event_id, 'comments' => $comments, 'tags' => $tags, 'observation_id' => $observation_id);

//$result = query_wildlife_video_db_prepared($query, $bind_params);

$wildlife_pdo = new PDO("mysql:host=localhost;dbname=wildlife_video;", $wildlife_user, $wildlife_passwd);

try {
//    error_log("quoted: '" . $wildlife_pdo->quote($comments) . "'");

    $stmt = $wildlife_pdo->prepare($query);
    $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindParam(':start_time_s', $start_time_s, PDO::PARAM_INT);
    $stmt->bindParam(':end_time_s', $end_time_s, PDO::PARAM_INT);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->bindValue(':comments', $comments, PDO::PARAM_STR);
    $stmt->bindParam(':tags', $tags, PDO::PARAM_STR);
    $stmt->bindParam(':observation_id', $observation_id, PDO::PARAM_INT);
    $stmt->execute();

//    $stmt->execute($a_bind_params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
    mysqli_error_msg($wildlife_db, $query);
}




$response['observation_id'] = $observation_id;
$response['html'] = get_timed_observation_row($observation_id, $species_id, 0);

//error_log(json_encode($response));

echo json_encode($response);
?>
