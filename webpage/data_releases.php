<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Data Releases", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

$grouse_ids_2015 = array(10720, 10724, 10860, 10941, 11719, 11722, 11730, 11732, 11735, 11745, 11749, 11768, 11777, 11780, 11800, 11802, 11812, 11824, 11833, 11834, 11838, 15374, 15375, 16082, 16086, 16094, 16112, 16115, 18617, 4391, 4395, 4396, 4403, 4433, 4436, 4454, 4489, 4514, 4525, 4526, 4532, 4545, 4557, 4564, 4579, 4587, 4601, 4606, 4607, 4625, 4710, 4725, 6396, 6397, 6407, 6422, 6432, 6437, 6439, 6440, 6443, 6451, 6461, 6479, 6483, 6490, 6491, 6492, 6494, 6498, 6502, 6508, 6511, 6813, 6825, 6828, 6859, 6861, 6875, 6930, 6975, 8996, 8999, 9024, 9026, 9031, 9034, 9039, 9050, 9056, 9058, 9062, 9068, 9119, 9146, 9150, 9151, 9152, 9156, 9158, 9160, 9164, 9167, 9176, 9541, 9731, 9734, 9785, 9880);
$n_grouse_ids = count($grouse_ids_2015);

$tern_plover_ids_2015 = array(14515,22996,23005,23011,23014,23033,23052,23060,23224,23248,23254,
    58277,58279,58281,58283,58285,58287,58289,58291,58293,58295,58297,58299,58301,58303,58305,58307,58309,58311,58341,58345,58347,58349,58351,58381,58382,58384,58385,58386,58387,58397,58399,58400,58401,58402,
    59018,59019,59020,59021,59022,59023,59024,59025,59026,59027,59029,59031,59032,59033,59034,59035,59036,59038,59039,59040,59041,59044,59045,59046,59047,59048,59050,59052,
    59056,
    59062,59064,59066,59068,59070,59071,59072,59073,59074,59077,59081,59083,
    59090,59091,59096,59097,59105,59110, 59165,59173,59177,59178);
$n_tern_plover_ids = count($tern_plover_ids_2015);

/* removed IDs due to humans:
 *  59113,
    58275,
    59017,
    59054,
    59058,
    59060,
    59089,
    59085,
    59179
 */


$all_ids = array_merge($grouse_ids_2015, $tern_plover_ids_2015);
$n_all_ids = count($all_ids);

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='well'>

                    <div class='page-header'>
                    <h2>Wildlife@Home Data Release 1, April 2015</h2>
                    </div>

                    <h3>Information</h3>
                    <p align='justify'>
                    The following presents a list of $n_all_ids Sharp-tailed Grouse (species id 1), Interior Least Tern (species id 2) and Piping Plover (species id 3) videos. Download links are provided along with all the human observations (both expert and volunteer) for each video.
                    </p>

                    <h3>Use</h3>
                    <p align='justify'>
                        This data release is currently intended for research into computer vision techniques and other areas of computer science. Dr. Susan Ellis-Felege reserves the right to publish on the biological aspects of these videos and human observations.
                    </p>

                    <h3>Reference</h3>
                    <p align='justify'>
                        This data set was released in conjunction with a publication for the <a href='http://escience2015.mnm-team.org'>eScience 2015 conference</a>: <!-- Please cite the following paper if you intend to use this dataset: -->
                        <ul>
                            <li><b>A Comparison of Background Subtraction Algorithms for Detecting Avian Nesting Events in Uncontrolled Outdoor Video</b>. Kyle Goehner, Travis Desell, Rebecca Eckroad, Leila Mohsenian, Paul Burr, Nicholas Caswell, Alicia Andes and Susan-Ellis-Felege. In the <i>11th IEEE International Conference on eScience</i>.  Munich, Germany, August 31 - September 04, 2015.</li>
                        </ul>
                    </p>
                </div> <!--well-->
            </div> <!--col-sm-12-->
        </div> <!--row-->
    </div> <!-- container -->";

echo "<div class='container'>";
echo "<table class='table table-striped table-bordered table-condensed'>";
echo "<thead> 
    <tr>
        <th>Video ID</th>
        <th>Species</th>
        <th>Download</th>
        <th>Event</th>
        <th>Start Time (s)</th>
        <th>End Time (s)</th>
        <th>Tags</th>
        <th>Comments</th>
        <th>Expert</th>
    </tr>
</thead>";

echo "<tbody>";
foreach ($all_ids as $id) {
    $result = query_wildlife_video_db("SELECT watermarked_filename, species_id FROM video_2 WHERE id = $id");
    $row = $result->fetch_assoc();
    $species_id = $row['species_id'];
    $watermarked_filename = ltrim($row['watermarked_filename'], '/');

    $obs_result = query_wildlife_video_db("SELECT expert, start_time_s, end_time_s, event_id, tags, comments FROM timed_observations WHERE video_id = $id");
    $n_rows = $obs_result->num_rows;

    echo "<tr>
            <td rowspan='$n_rows'>$id</td>
            <td rowspan='$n_rows'>$species_id</td>
            <td rowspan='$n_rows'>
                <a href='http://$sharehost/$watermarked_filename.mp4'>[mp4]</a>
            </td>";

    $i = 0;
    while (($obs_row = $obs_result->fetch_assoc()) != NULL) {
        if ($i > 0) echo "<tr>";
        $event_id = $obs_row['event_id'];
        $start_time_s = $obs_row['start_time_s'];
        $end_time_s = $obs_row['end_time_s'];
        $tags = $obs_row['tags'];
        $comments = $obs_row['comments'];
        $expert = $obs_row['expert'];

        $event_result = query_wildlife_video_db("SELECT name, category FROM observation_types WHERE id = $event_id");
        $event_row = $event_result->fetch_assoc();
        $event_name = $event_row['category'] . " - " . $event_row['name'];

        echo "<td>$event_name</td>";
        echo "<td>$start_time_s</td>";
        echo "<td>$end_time_s</td>";
        echo "<td>$tags</td>";
        echo "<td>$comments</td>";
        echo "<td>$expert</td>";

        echo "</tr>";

        $i++;
    }



    echo "</tr>";
}

echo "</tbody>";

echo "</table>";
echo "</div> <!--container-->";

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";


?>
