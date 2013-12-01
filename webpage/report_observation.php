<?php

require_once('/home/tdesell/wildlife_at_home/webpage/boinc_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/home/tdesell/wildlife_at_home/webpage/user.php');

function get_observation_data($data, $from_db = false) {

    $res->comments = mysql_real_escape_string($data['comments']);
    $res->bird_leave = $data['bird_leave'];
    $res->bird_return = $data['bird_return'];
    $res->bird_presence = $data['bird_presence'];
    $res->bird_absence = $data['bird_absence'];
    $res->predator_presence = $data['predator_presence'];
    $res->nest_defense = $data['nest_defense'];
    $res->nest_success = $data['nest_success'];
    $res->chick_presence = $data['chick_presence'];
    $res->interesting = $data['interesting'];
    $res->user_id = $data['user_id'];
    $res->video_segment_id = $data['video_segment_id'];
    $res->video_issue = $data['video_issue'];

    if (!$from_db) {
        $res->status = 'UNVALIDATED';
        $res->id = -1;
    } else {
        $res->status = $data['status'];
        $res->id = $data['id'];
    }

    return $res;
}

$post_observation = get_observation_data($_POST);

$start_time = mysql_real_escape_string($_POST['start_time']);
$species_id = mysql_real_escape_string($_POST['species_id']);
$location_id = mysql_real_escape_string($_POST['location_id']);
$duration_s = mysql_real_escape_string($_POST['duration_s']);
/**
 * Grab the other observations from the database.
 */

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

//echo "WILDLIFE_USER: $wildlife_user\n";
//echo "WILDLIFE_PASSWD: $wildlife_passwd\n";

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

/**
 *  We only need to get the canonical result and/or any other unvalidated observations
 */
$query = "SELECT * FROM observations WHERE video_segment_id = $post_observation->video_segment_id";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$db_observations = array();

$canonical_observation = NULL;

while ($row = mysql_fetch_assoc($result)) {
    $observation = get_observation_data($row, true);
    if ($observation->status == 'CANONICAL') $canonical_observation = $observation;

    $user = get_user_from_id__fixme($observation->user_id);
    $observation->user_name = $user['name'];

    $db_observations[] = $observation;
}

/**
 *  insert the observation into the database
 */

if (array_key_exists('reviewing_reported', $_POST) && $_POST['reviewing_reported'] == 'true') {
    $query = "REPLACE INTO observations SET" .
        " comments = '$post_observation->comments'," .
        " bird_leave = $post_observation->bird_leave, " .
        " bird_return = $post_observation->bird_return, " .
        " bird_presence = $post_observation->bird_presence, " .
        " bird_absence = $post_observation->bird_absence, " .
        " predator_presence = $post_observation->predator_presence, " .
        " nest_defense = $post_observation->nest_defense, " .
        " nest_success = $post_observation->nest_success, " .
        " chick_presence = $post_observation->chick_presence, " .
        " interesting = $post_observation->interesting, " .
        " user_id = $post_observation->user_id, " .
        " video_issue = $post_observation->video_issue, " .
        " status = 'EXPERT', " .
        " species_id = $species_id, " .
        " location_id = $location_id, " .
        " video_segment_id = $post_observation->video_segment_id," .
        " insert_time = NOW()";

    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    error_log($query);
} else {
    $query = "REPLACE INTO observations SET" .
        " comments = '$post_observation->comments'," .
        " bird_leave = $post_observation->bird_leave, " .
        " bird_return = $post_observation->bird_return, " .
        " bird_presence = $post_observation->bird_presence, " .
        " bird_absence = $post_observation->bird_absence, " .
        " predator_presence = $post_observation->predator_presence, " .
        " nest_defense = $post_observation->nest_defense, " .
        " nest_success = $post_observation->nest_success, " .
        " chick_presence = $post_observation->chick_presence, " .
        " interesting = $post_observation->interesting, " .
        " user_id = $post_observation->user_id, " .
        " video_issue = $post_observation->video_issue, " .
        " status = '$post_observation->status', " .
        " species_id = $species_id, " .
        " location_id = $location_id, " .
        " video_segment_id = $post_observation->video_segment_id," .
        " insert_time = NOW()";

    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$query = "UPDATE video_segment_2 SET crowd_obs_count = crowd_obs_count + 1, crowd_status = 'WATCHED' WHERE id = " . $post_observation->video_segment_id;
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

error_log("_POST[reviewing_reported] = " . $_POST['reviewing_reported'] . ", user_id: " . $post_observation->user_id);

$boinc_db = mysql_connect("localhost", $boinc_user, $boinc_passwd);
mysql_select_db("wildlife", $boinc_db);

$query = "UPDATE user SET total_observations = total_observations + 1 WHERE id = " . $post_observation->user_id;
$result = attempt_query_with_ping($query, $boinc_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");

$query = "UPDATE team SET total_observations = total_observations + 1 WHERE id = (SELECT teamid FROM user WHERE user.id = " . $post_observation->user_id .")";
$result = attempt_query_with_ping($query, $boinc_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");


if (array_key_exists('reviewing_reported', $_POST) && $_POST['reviewing_reported'] == 'true') {
    $user_id = $post_observation->user_id;
    $query = "SELECT name FROM user WHERE id = $user_id";
    $result = attempt_query_with_ping($query, $boinc_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($boinc_db) . "): " . mysql_error($boinc_db) . "\nquery: $query\n");
    $row = mysql_fetch_assoc($result);
    $user_name = $row['name'];

    error_log("in reviewing reported: id: $user_id, name: $user_name");

    $query = "UPDATE reported_video SET reviewer_id = $user_id, reviewer_name = '$user_name', review_comments = '$post_observation->comments', instructional = $post_observation->interesting, valid_report = " . $_POST['valid_report'] . " WHERE video_segment_id = $post_observation->video_segment_id";
    error_log("UPDATING REPORTED  VIDEO WITH: $query");
    $result = attempt_query_with_ping($query, $wildlife_db);
    error_log(" dying? ");
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    error_log("UPDATED REPORTED  VIDEO WITH: $query");

    $query = "UPDATE video_segment_2 SET report_status = 'REVIEWED', validate_for_review = true, instructional = $post_observation->interesting WHERE id = $post_observation->video_segment_id";
    error_log(" UPDATING VIDEO SEGMENT 2 WITH: $query");
    $result = attempt_query_with_ping($query, $wildlife_db);
    error_log(" dying? ");
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    error_log(" UPDATED VIDEO SEGMENT 2 WITH: $query");

    $query = "UPDATE species SET waiting_review = waiting_review - 1 WHERE id = $species_id";
    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
}

$result = array( 'post_observation' => $post_observation, 'db_observations' => $db_observations );

error_log( json_encode($result) );

echo json_encode($result);
?>
