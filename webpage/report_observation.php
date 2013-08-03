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
 * if there are enough obsevations try to validate it
 */

function calculate_sameness($val1, $val2) {
    if ($val1 == 0 || $val2 == 0) return 0;   //if either are unsure this is not a match
    else if ($val1 == $val2)      return 1;   //if both are sure and the same it is a match
    else                          return -10; //if both are sure and different it is not a match
}

function match($obs1, $obs2) {
    $same_val_count = 0;

    if ($obs1->too_dark > 0 && $obs2->too_dark > 0) return 8;
    if ($obs1->corrupt > 0 && $obs2->corrupt > 0) return 8;

    $same_val_count += calculate_sameness($obs1->bird_leave, $obs2->bird_leave);
    $same_val_count += calculate_sameness($obs1->bird_return, $obs2->bird_return);
    $same_val_count += calculate_sameness($obs1->bird_presence, $obs2->bird_presence);
    $same_val_count += calculate_sameness($obs1->bird_absence, $obs2->bird_absence);
    $same_val_count += calculate_sameness($obs1->predator_presence, $obs2->predator_presence);
    $same_val_count += calculate_sameness($obs1->nest_defense, $obs2->nest_defense);
    $same_val_count += calculate_sameness($obs1->nest_success, $obs2->nest_success);
    $same_val_count += calculate_sameness($obs1->chick_presence, $obs2->chick_presence);

    return $same_val_count;
}

/**
 *  If there is no canonoical observation:
 *      see if the current observation matches any in the database
 *          if not, just insert the current observation into the database
 *          if it does -- set the current observation to the canonical one, then:
 *              validate the current and all matches to the current.
 *              set all non matches to the current to invalid
 *  If there is a canonical observation:
 *      see if the current observation matches it
 *          if it does -- validate the current observation and insert it to the database
 *          if it does not -- set the current to invalid and insert it to the database
 *      
 */

$MAX_CREDIT = $duration_s;

$update_db_obs_credit = false;
$update_post_credit = false;

if (is_null($canonical_observation)) {
    $same_obs_id = -1;

    for ($i = 0; $i < count($db_observations); $i++) {
        /**
         * If both observations have 8 or more matches (that aren't unsure), and no mistmatches, we've got a match
         */
        $match_count = match($post_observation, $db_observations[$i]);
        if ( $match_count >= 6 ) {
            $same_obs_id = $db_observations[$i]->id;
            break;
        }
    }

    if ($same_obs_id > 0) {    //There was a match
        //set the current observation's status to canonical
        $post_observation->status = 'CANONICAL';
        $post_observation->credit = $MAX_CREDIT;

        for ($i = 0; $i < count($db_observations); $i++) {
            $match_count = match($post_observation, $db_observations[$i]);

            if ($match_count >= 0) {
                $db_observations[$i]->status = 'VALID';
                $db_observations[$i]->credit = ($MAX_CREDIT * $match_count) / 8.0;  //award credit
            } else {
                $db_observations[$i]->status = 'INVALID';
                $db_observations[$i]->credit = 0;
            }
        }

        $update_post_credit = true;
        $update_db_obs_credit = true;
    }
} else {
    $match_count = match($post_observation, $canonical_observation);
    if ($match_count >= 0) {
        $post_observation->status = 'VALID';
        $post_observation->credit = ($MAX_CREDIT * $match_count) / 8.0; //award credit

        $update_post_credit = true;
    } else {
        $post_observation->status = 'INVALID';
        $post_observation->credit = 0;
    }
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

$affected_rows = mysql_affected_rows($wildlife_db);
error_log("AFFECTED ROWS: $affected_rows");

if ($affected_rows > 1) {
    /**
     * In this case, we replaced an observation.  This should not happen, it could mean that someone is gaming the
     * project, trying to enter in multiple observations for the same video to award them credit.  If this is the
     * case we need to not award credit or validate against that observaiton.
     */

    $update_db_obs_credit = false;
    $update_post_credit = false;

    $post_observation->credit = 0;
}


/**
 * if update_db_obs_credit is true, the video observations were validated,
 *      so update the crowd_status to 'VALIDATED';
 * otherwise update it to 'WATCHED'
 */
$crowd_status = 'WATCHED';
if ($update_db_obs_credit || !is_null($canonical_observation)) $crowd_status = 'VALIDATED';

/**
 * calculate the interesting count, the count of observations.
 */

$interesting_count = 0;
if (isset($post_observation->interesting) && $post_observation->interesting > 0) {
    $interesting_count = 1;
}

for ($i = 0; $i < count($db_observations); $i++) {
    if (isset($db_observations[$i]->interesting) && $db_observations[$i]->interesting > 0) {
        $interesting_count += 1;
    }
}

$crowd_obs_count = 1 + count($db_observations);

error_log("updating crowd_obs_count to $crowd_obs_count and crowd_status to $crowd_status");

//$query = "UPDATE video_segment_2 SET crowd_status = '$crowd_status' WHERE id = $post_observation->video_segment_id";
$query = "UPDATE video_segment_2 SET crowd_status = '$crowd_status', crowd_obs_count = $crowd_obs_count, interesting_count = $interesting_count WHERE id = $post_observation->video_segment_id";
$result = attempt_query_with_ping($query, $wildlife_db);
if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");


if ($update_post_credit) {
    /* add credit to the user */

    bossa_award_credit($post_observation->user_id, $post_observation->credit, $start_time, time());
}

if ($update_db_obs_credit) {

    /* update the observations to invalid or valid */
    for ($i = 0; $i < count($db_observations); $i++) {
        $query = "UPDATE observations SET status = '" . $db_observations[$i]->status . "' WHERE id = " . $db_observations[$i]->id;

        $result = attempt_query_with_ping($query, $wildlife_db);
        if (!$result) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");

        if ($db_observations[$i]->status == 'VALID') {
            bossa_award_credit($db_observations[$i]->user_id, $db_observations[$i]->credit, $start_time, time());
        }
    }

    /* update the progress table */
    $query = "UPDATE progress SET validated_video_s = validated_video_s + " . $duration_s . " WHERE progress.species_id = $species_id AND progress.location_id = $location_id";
    error_log($query);
    $result = attempt_query_with_ping($query, $wildlife_db);
    if (!$result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
        die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    }
}

$result = array( 'post_observation' => $post_observation, 'db_observations' => $db_observations );

error_log( json_encode($result) );

echo json_encode($result);
?>
