<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Observations Export", "", "wildlife");
print_navbar("", "Wildlife@Home");

$user = csg_get_user(true);

if (!csg_is_special_user($user)) {
    echo "
        <div class='container'>
            <div class='row'>
                <div class='col-sm-12'>

                    <div class='well'>
                        Only project scientists can access this page.
                    </div>

                </div> <!-- col-sm-12 -->
            </div> <!-- row -->
        </div> <!-- /container -->";
} else {
    echo "
        <div class='container'>
            <div class='row'>
                <div class='col-sm-12'>

                    <div class='well'>";

    $expert_video_result = query_wildlife_video_db("SELECT id FROM video_2 as v2 WHERE EXISTS(SELECT * FROM timed_observations as tobs WHERE v2.id = tobs.video_id AND tobs.expert = true)");

    $videos_with_expert = array();
    $videos_with_both = array();
    while ($row = $expert_video_result->fetch_assoc()) {
        $videos_with_expert[] = $row;

        $user_obs_result = query_wildlife_video_db("SELECT count(*) FROM timed_observations WHERE video_id = " . $row['id'] . " AND expert = false");
        $user_obs_row = $user_obs_result->fetch_assoc();

        if ($user_obs_row['count(*)'] > 0) {
            $videos_with_both[] = $row;
        }

    }

    echo count($videos_with_expert) . " videos with expert timed observations.<br>";
    echo count($videos_with_both) . " videos with expert AND user timed observations.<br>";
    echo "<br>";


    $videos_with_events = 0;
    $duplicates = 0;
    $matches = array();
    $had_match = 0;
    $valid_matches = 0;
    $invalid_matches = 0;
    $unvalidated_matches = 0;

    $obs_for_table['observations'] = array();

    for ($i = 0; $i < count($videos_with_both); $i++) {
        $timed_obs_result = query_wildlife_video_db("SELECT event_id, start_time_s, end_time_s, start_time, end_time, species_id, location_id, status, user_id, expert FROM timed_observations WHERE video_id = " . $videos_with_both[$i]['id']);

        $obs_by_user = array();
        while ($timed_obs_row = $timed_obs_result->fetch_assoc()) {
            if ($timed_obs_row['start_time_s'] < 0 || $timed_obs_row['end_time_s'] < 0) continue;

            /*
            echo   $timed_obs_row['event_id'] . " " 
                 . $timed_obs_row['species_id'] . " "
                 . $timed_obs_row['location_id'] . " "
                 . $timed_obs_row['start_time_s'] . " "
                 . $timed_obs_row['end_time_s'] . " "
                 . $timed_obs_row['status'] . " "
                 . $timed_obs_row['user_id'] . " "
                 . $timed_obs_row['expert'] . "<br>";
             */

            $name_result = query_boinc_db("SELECT name FROM user WHERE id = " . $timed_obs_row['user_id']);
            $name_row = $name_result->fetch_assoc();
            $timed_obs_row['name'] = $name_row['name'];
            $timed_obs_row['video_id'] = $videos_with_both[$i]['id'];

            $type_result = query_wildlife_video_db("SELECT name, category FROM observation_types WHERE id = " . $timed_obs_row['event_id']);
            $type_row = $type_result->fetch_assoc();
            $timed_obs_row['event_name'] = $type_row['category'] . " - " . $type_row['name'];

            $obs_by_user[$timed_obs_row['user_id']][] = $timed_obs_row;
        }

        //calculate valid vs invalid events per species/location
        //valid vs invalid events per user
        //valid vs invalid events per time of day

//        echo count($obs_by_user) . " users with events with valid times.<br>";
        if (count($obs_by_user) > 1) {
            $videos_with_events++;

            //need to count how many of the users observations match other users observations
            //then bin by species/location, time of day, difficulty, time to submit
            //check for duplicates as well
            //avg distance to nearest event?
            $all_user_obs = array();
            foreach (array_keys($obs_by_user) as $user) {
//                echo "user: " . $user . " '" . $obs_by_user[$user][0]['name'] . "' has " . count($obs_by_user[$user]) . " events.<br>";

                $user_obs = $obs_by_user[$user];
                for ($j = 0; $j < count($user_obs); $j++) { //mark all observations as ready for processing
                    $user_obs[$j]['mark'] = 'READY';
                }

                for ($j = 0; $j < count($user_obs); $j++) {
                    $k = 0;
                    for ($k = $j+1; $k < count($user_obs); $k++) {
                        if ($user_obs[$k]['mark'] == 'DUPLICATE') continue; //skip duplicates

                        if ( $user_obs[$j]['event_id'] == $user_obs[$k]['event_id']
                            && abs($user_obs[$j]['start_time_s'] - $user_obs[$k]['start_time_s']) < 10.0
                            && abs($user_obs[$j]['end_time_s'] - $user_obs[$k]['end_time_s']) < 10.0) {
                                $duplicates += 1; 
                                $user_obs[$k]['mark'] = 'DUPLICATE';    //mark this observation as a duplicate so we can skip it
                                                                        //when we analyze matches

                                /*
                                echo "DUPLICATE: <br>";
                                echo json_encode($user_obs[$j]) . "<br>";
                                echo json_encode($user_obs[$k]) . "<br>";
                                */
                        }
                    }
                }
                $all_user_obs[] = $user_obs;
            }

            //compare observation of first user to all other observations of all other users
            //skip all marked 'DUPLICATE' or 'VALID'
            //if observation matches a quorum of other user observations, mark it and the others as 'VALID'
            //if it does not match a quorum, mark it 'INCONCLUSIVE' (or 'INVALID', or leave it as 'READY'?)

            for ($j = 0; $j < count($all_user_obs); $j++) {

                //iterate over first users observations
                for ($k = 0; $k < count($all_user_obs[$j]); $k++) {
                    $obs_for_table['observations'][] = $all_user_obs[$j][$k]; //don't display duplicates in the table output
                    if ($all_user_obs[$j][$k]['mark'] == 'DUPLICATE') continue; //skip any observations marked as duplicates

                    $event_matches = 0;
                    $match_list = array();

                    //iterate over other users' observations
                    for ($l = $j + 1; $l < count($all_user_obs); $l++) {
                        for ($m = 0; $m < count($all_user_obs[$l]); $m++) {
                            if ($all_user_obs[$l][$m]['mark'] == 'DUPLICATE') continue; //skip any observations marked as duplicates
                            if ($all_user_obs[$l][$m]['mark'] == 'VALID') continue; //skip any observations already marked valid


                            if ($all_user_obs[$j][$k]['event_id'] == $all_user_obs[$l][$m]['event_id']
                                && abs($all_user_obs[$j][$k]['start_time_s'] == $all_user_obs[$l][$m]['start_time_s']) < 10.0
                                && abs($all_user_obs[$j][$k]['end_time_s'] == $all_user_obs[$l][$m]['end_time_s']) < 10.0) {
                                    $event_matches++;
                                    $match_list[] = array('user' => $l, 'event' => $m);
                            }

                        }
                    }
                    if ($event_matches > count($all_user_obs)/2) {
                        $all_user_obs[$j][$k]['mark'] = 'VALID';
                        if ($all_user_obs[$j][$k]['status'] == 'VALID') $valid_matches++;
                        if ($all_user_obs[$j][$k]['status'] == 'INVALID') $invalid_matches++;
                        if ($all_user_obs[$j][$k]['status'] == 'UNVALIDATED') $unvalidated_matches++;

                        $had_match++;
                        foreach ($match_list as $match) {
                            $all_user_obs[$match['user']][$match['event']]['mark'] = 'VALID';
                            if ($all_user_obs[$match['user']][$match['event']]['status'] == 'VALID') $valid_matches++;
                            if ($all_user_obs[$match['user']][$match['event']]['status'] == 'INVALID') $invalid_matches++;
                            if ($all_user_obs[$match['user']][$match['event']]['status'] == 'UNVALIDATED') $unvalidated_matches++;
                            $had_match++;
                        }
                    }

                }
            }

            //count up the number of workable videos per species/location
            $species_id = $obs_by_user[$user][0]['species_id'];
            $location_id = $obs_by_user[$user][0]['location_id'];
            $matches[$species_id][$location_id]++;
        }
        //echo "<br>";

//        if ($i > 10) break;
    }

    $unvalidated_inconclusive = 0;
    for($i = 0; $i < count($obs_for_table['observations']); $i++) {
        if ($obs_for_table['observations'][$i]['mark'] == 'READY') $obs_for_table['observations'][$i]['mark'] = 'INCONCLUSIVE';

        if ($obs_for_table['observations'][$i]['mark'] == 'INCONCLUSIVE' && $obs_for_table['observations'][$i]['status'] == 'UNVALIDATED') $unvalidated_inconclusive++;
    }

    echo $videos_with_events . " videos with a usable number of events.<br>";
    echo count($obs_for_table['observations']) . " number of usable events.<br>";
    echo $duplicates . " events duplicated by user.<br>";
    echo $had_match . " events had a match from another user.<br>";
    echo $valid_matches . " events calculated as valid here that were valid by the assimilator (VALID in mark column, VALID in status column).<br>";
    echo $invalid_matches . " events calculated as valid here that were invalid by the assimilator (VALID in mark column, INVALID in status column).<br>";
    echo $unvalidated_matches . " events calculated as valid here that were unvalidated by the assimilator (VALID in mark column, UNVALIDATED in status column).<br>";
    echo $unvalidated_inconclusive . " events calculated as inconclusive that were unvalidated by the assimilator (INCONCLUSIVE in mark column, UNVALIDATED in status column).<br>";
    echo "<br>";

    echo "Species 1 - Sharptailed Grouse (<i>Tympanuchus phasianellus</i>)<br>";
    echo "Species 2 - Interior Least Tern (<i>Sternula antillarum</i>)<br>";
    echo "Species 3 - Piping Plover (<i>Charadrius melodus</i>)<br>";
    echo "<br>";

    foreach (array_keys($matches) as $species) {
        foreach (array_keys($matches[$species]) as $location) {
            echo "species: $species, location: $location -- " . $matches[$species][$location] . " videos.<br>";
        }
    }

    echo "          </div>
                </div> <!-- col-sm-12 -->";

    $observations_template = file_get_contents($cwd[__FILE__] . "/templates/observations_export_table.html");

    $m = new Mustache_Engine;
    echo $m->render($observations_template, $obs_for_table);

    echo "
            </div> <!-- row -->
        </div> <!-- /container -->";
}


print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";

?>
