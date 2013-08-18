<?php

require_once('/home/tdesell/wildlife_at_home/webpage/award_credit.inc');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/projects/wildlife/html/inc/util.inc');
require_once('/projects/wildlife/html/inc/bossa_impl.inc');

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
    $res->too_dark = $data['too_dark'];
    $res->corrupt = $data['corrupt'];

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

    $user = get_user_from_id($observation->user_id);
    $observation->user_name = $user->name;

    $db_observations[] = $observation;
}

/**
 *  insert the observation into the database
 */

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
    " too_dark = $post_observation->too_dark, " .
    " corrupt = $post_observation->corrupt, " .
    " status = '$post_observation->status', " .
    " species_id = $species_id, " .
    " location_id = $location_id, " .
    " video_segment_id = $post_observation->video_segment_id";

$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$query = "UPDATE video_segment_2 SET crowd_obs_count = crowd_obs_count + 1, crowd_status = IF(crowd_status = 'UNWATCHED', 'WATCHED', crowd_status) WHERE id = " . $post_observation->video_segment_id;
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

$result = array( 'post_observation' => $post_observation, 'db_observations' => $db_observations );

error_log( json_encode($result) );

echo json_encode($result);
?>
