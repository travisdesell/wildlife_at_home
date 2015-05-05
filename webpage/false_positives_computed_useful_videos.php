<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

//echo $cwd[__FILE__];
require_once($cwd[__FILE__] . "/../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/webpage/correctness.php");

print_header("Wildlife@Home: Computer False Positives vs Experts By Species", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 10;
}

if (!isset($species)) {
    $species = 1;
}

if (!isset($version)) {
    $version = "main"; // or "beta"
}

if (!isset($sample)) {
    $sample = "everyone"; // or "experts" or "users"
}

$species_query = "SELECT id, name FROM species WHERE id = $species";
$species_result = query_wildlife_video_db($species_query, $wildlife_db);

$algorithm_query = "SELECT id, name FROM event_algorithms";
$algorithm_result = query_wildlife_video_db($algorithm_query, $wildlife_db);


$algs = array();
while ($alg_row = $algorithm_result->fetch_assoc()) {
    $alg_id = $alg_row['id'];
    $alg_name = $alg_row['name'];
    if ($alg_id <= 3) {
        $algs[$alg_id] = $alg_name;
    }
}
ksort($algs);

echo "
<div class='containder'>
    <div class='row'> <div class='col-sm-12'> <script type = 'text/javascript' src='js/data_download.js'></script>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1.1', {packages:['corechart']});
        google.setOnLoadCallback(drawChart);

        var data;

        function downloadChart() {
            var csv_data = dataTableToCSV(data);
            downloadCSV(csv_data);
        }

        function drawChart() {
            var container = document.getElementById('chart_div');
            data = new google.visualization.DataTable();
            data.addColumn('number', 'Threshold Percent');
";

foreach($algs as $a_id => $a_name) {
    echo "data.addColumn('number', '$a_name');";
}

echo "
            data.addRows([
";

while ($species_row = $species_result->fetch_assoc()) {
    $not_in_vid_id = 4;
    $event_id = $not_in_vid_id;
    $species_id = $species_row['id'];
    $species_name = $species_row['name'];

    $video_query = "SELECT DISTINCT t.video_id AS video_id, t.user_id AS user_id FROM timed_observations AS t INNER JOIN computed_events AS comp ON comp.video_id = t.video_id INNER JOIN event_algorithms AS alg ON comp.algorithm_id = alg.id AND comp.version_id = ";
    if ($version == "beta") {
        $video_query = $video_query . "alg.beta_version_id WHERE ";
    } else { // "live"
        $video_query = $video_query . "alg.main_version_id WHERE ";
    }
    if ($sample == "everyone") {
        // Don't add anything
    } elseif ($sample == "experts") {
        $video_query = $video_query . "expert = 1 AND ";
    } elseif ($sample == "users") {
        $video_query = $video_query . "expert = 0 AND ";
    } else {
        throw new Exception("Incorrect sample name.");
    }
    $video_query = $video_query . "species_id = $species_id AND t.event_id = $event_id AND t.start_time_s >= 0 AND t.start_time_s <= t.end_time_s";

    $video_result = query_wildlife_video_db($video_query);
    $num_videos = $video_result->num_rows;
    $alg_num_false = array();
    $alg_useful_vids = array();
    $thresholds = array();
    for($x = 0; $x <= 100; $x++) {
        $thresholds[] = $x;
    }
    foreach($algs as $a_id => $a_name) {
        $alg_num_false[$a_id] = array();
        $alg_useful_vids[$a_id] = array();
        foreach($thresholds as $thresh) {
            $alg_useful_vids[$a_id][$thresh] = 0;
        }
    }
    while ($video_row = $video_result->fetch_assoc()) {
        $video_id = $video_row['video_id'];
        $user_id = $video_row['user_id'];

        foreach($algs as $a_id => $a_name) {
            list($false_positives, $total_seconds) = getFalsePositives($video_id, $user_id, $a_id, $buffer);
            $alg_num_false[$a_id][] += $false_positives;
            foreach($thresholds as $thresh) {
                if (($false_positives/$total_seconds)*100 <= $thresh) {
                    $alg_useful_vids[$a_id][$thresh] += 1;
                }
            }
        }
    }

    $add_data = false;
    foreach($alg_num_false as $a_id => $a_val) {
        foreach($a_val as $x) {
            if($x > 0) {
                $add_data = true;
                break;
            }
        }
        if($add_data) break;
    }

    if ($add_data) {
        foreach($thresholds as $thresh) {
            echo "[";
            echo $thresh;
            foreach($alg_num_false as $a_id => $a_val) {
                echo ",";
                echo $alg_useful_vids[$a_id][$thresh]/$num_videos * 100;
            }
            echo "],";
        }
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Percentage of Videos with Useful Computed Results vs False Postive Percentage Threshold',
                hAxis: {
                    title: 'False Postive Pecentage Threshold',
                    minValue: 0,
                    maxValue: 100
                },
                vAxis: {
                    title: 'Percent of Useful Videos',
                    minValue: 0,
                    maxValue: 100
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Computer False Positives vs Experts by Species</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The time (seconds) after the start and before the end of a 'not in video' event. The default value is 10.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This bar chart show the number of false positives classifed by each of the different algorithms. A false positive is a computed event that occurs during a 'not in video' event.</p>

        </div>
    </div>
</div>
";

print_footer("Travis Desell, 'Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team'", "Travis Desell, Susan Ellis-Felege");

echo "
    </body>
</html>
";
?>
