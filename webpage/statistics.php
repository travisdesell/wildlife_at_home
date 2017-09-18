<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Usage Statistics", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <h4>Wildlife@Home Usage Statistics:</h4>";

function get_count($query) {
    $result = query_boinc_db($query);
    $row = $result->fetch_assoc();
    return $row['count(*)'];
}

function get_count_wildlife($query) {
    $result = query_wildlife_video_db($query);
    $row = $result->fetch_array();
    return $row[0];
}

echo "<table class='table table-bordered'><col style='width:50%' span='2' />";

//total participation
$query = "SELECT count(*) FROM user where valid_observations + bossa_total_credit + valid_observations_new + total_observations + valid_events + total_events + matched_image_observations + total_image_observations > 0;";
$total_users = get_count($query);

echo "<tr> <td>Total users</td> <td>$total_users</td> </tr>";
echo "</table>";

echo "<table class='table table-bordered'><col style='width:50%' span='2' />";
echo "<tr> <td><b>Old Video Interface:</b></td> <td></td> </tr>";
//old split video
$query = "SELECT count(*) FROM user WHERE total_observations > 0";
$total_old_users = get_count($query);
echo "<tr> <td>Total users</td> <td>$total_old_users</td> </tr>";

//minutes watched (old interface)
$query = "SELECT sum(duration_s) FROM video_segment_2 WHERE crowd_obs_count + expert_obs_count > 0";
$old_time = get_count_wildlife($query);
echo "<tr> <td>Distinct video watched</td> <td>" . number_format(($old_time / (60 * 60)), 2) . " hours</td> </tr>";

$query = "SELECT sum(duration_s * (crowd_obs_count + expert_obs_count)) FROM video_segment_2 WHERE crowd_obs_count + expert_obs_count > 0";
$old_time = get_count_wildlife($query);
echo "<tr> <td>Video watched</td> <td>" . number_format(($old_time / (60 * 60)), 2) . " person hours</td> </tr>";
echo "</table>";


echo "<table class='table table-bordered'><col style='width:50%' span='2' />";
echo "<tr> <td><b>New Video Interface:</b></td> <td></td> </tr>";
//new video
$query = "SELECT count(*) FROM user WHERE total_events > 0";
$total_new_users = get_count($query);
echo "<tr> <td>Total users</td> <td>$total_new_users</td> </tr>";
//minutes watched
$query = "SELECT sum(duration_s) FROM video_2 WHERE crowd_obs_count + expert_obs_count > 0";
$new_time = get_count_wildlife($query);
echo "<tr> <td>Distinct video watched</td> <td>" . number_format(($new_time / (60 * 60)), 2) . " hours</td> </tr>";

$query = "SELECT sum(duration_s * (crowd_obs_count + expert_obs_count)) FROM video_2 WHERE crowd_obs_count + expert_obs_count > 0";
$new_time = get_count_wildlife($query);
echo "<tr> <td>Video watched</td> <td>" . number_format(($new_time / (60 * 60)), 2) . " person hours</td> </tr>";
echo "</table>";


echo "<table class='table table-bordered'><col style='width:50%' span='2' />";
echo "<tr> <td><b>Image Interface:</b></td> <td></td> </tr>";
//images
$query = "SELECT count(*) FROM user WHERE total_image_observations > 0";
$total_image_users = get_count($query);
echo "<tr> <td>Total users (images interface)</td> <td>$total_image_users</td> </tr>";

//objects marked
$query = "SELECT count(*) FROM images WHERE views + expert_views > 0";
$image_count = get_count_wildlife($query);
echo "<tr> <td>Images with observations</td> <td>" . $image_count . " images</td> </tr>";

$query = "SELECT count(*) FROM image_observations";
$n_observations = get_count_wildlife($query);
echo "<tr> <td>Observations made</td> <td>" . $n_observations . " observations</td> </tr>";

echo "</table>";


echo "<h4>Wildlife@Home Video Statistics:</h4>";

$query = "SELECT id, name, latin_name FROM species";
$result = query_wildlife_video_db($query);

echo "<table class='table table-bordered'>";
echo "<thead>";
echo "<th>Species</th>";
echo "<th>Time (hours)</th>";
echo "<th>Converted (hours)</th>";
echo "</thead>";


while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $name = $row['name'];
    $latin_name = $row['latin_name'];

    $query = "SELECT SUM(duration_s) FROM video_2 WHERE species_id = " . $id;
    $duration = number_format(get_count_wildlife($query) / (60.0 * 60.0), 2);

    $query = "SELECT SUM(duration_s) FROM video_2 WHERE species_id = " . $id . " AND ogv_generated > 0";
    $duration_converted = number_format(get_count_wildlife($query) / (60.0 * 60.0), 2);

    echo "<tr><td><b>$name</b> (<i>$latin_name</i>)</td> <td>$duration hours</td> <td>$duration_converted</td> </tr>";
}
echo "</table>";


echo "
            </section>
        </div>
    </div>
</div>";

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";


?>
