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

print_header("Wildlife@Home: Computer Accuracy with Consensus", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 30;
}

if (!isset($threshold)) {
    $threshold = 95;
}

$type_query = "SELECT id, name FROM observation_types";
$type_result = query_wildlife_video_db($type_query, $wildlife_db);

$species_query = "SELECT id, name FROM species";
$species_result = query_wildlife_video_db($species_query, $wildlife_db);

$algorithm_query = "SELECT id, name FROM event_algorithms";
$algorithm_result = query_wildlife_video_db($algorithm_query, $wildlife_db);

/*
$species = array();
while ($species_row = $species_result->fetch_assoc()) {
    $species_id = $species_row['id'];
    $species_name = $species_row['name'];
    $species[$species_id] = $species_name;
}
ksort($species);
*/

$algs = array();
while ($alg_row = $algorithm_result->fetch_assoc()) {
    $alg_id = $alg_row['id'];
    $alg_name = $alg_row['name'];
    $algs[$alg_id] = $alg_name;
}
ksort($algs);

echo "
<div class='containder'>
    <div class='row'>
    <div class='col-sm-12'>
    <script type = 'text/javascript' src='js/data_download.js'></script>
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
            data.addColumn('string', 'Event Type');
            data.addColumn('number', 'Any Algorithm');
            data.addColumn('number', 'All Algorithms');
";

echo "
            data.addRows([
";

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id, video_id, species_id FROM timed_observations AS t WHERE expert = 1 AND event_id = $type_id AND species_id <> 1 AND start_time_s > 10 AND start_time_s <= end_time_s AND (SELECT COUNT(*) FROM computed_events AS comp WHERE comp.video_id = t.video_id) > 0";
    $timed_result = query_wildlife_video_db($timed_query);

    $consensus_num = 0;
    $consensus_any_matches = 0;
    $consensus_all_matches = 0;

    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];
        $video_id = $timed_row['video_id'];

        $any_match = 0;
        $all_match = 0;
        foreach($algs as $a_id => $a_name) {
            list($correctness, $specificity) = getBufferAccuracy($obs_id, $a_id, $buffer);

            if($correctness > $any_match) {
                $any_match = $correctness;
            }
            $all_match += $correctness;
            $alg_match_events[$a_id] += $correctness;
        }
        $consensus_num += 2;
        $consensus_any_matches += $any_match;
        if($all_match == count($algs) * 2) {
            $consensus_all_matches += 2;
        }
    }

    if ($consensus_num > 0) {
        echo "[";
        echo "'$type_name'";
        echo ",";
        echo "$consensus_any_matches / $consensus_num * 100";
        echo ",";
        echo "$consensus_all_matches / $consensus_num * 100";
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Computer accuracy with consensus vs experts on tern and plover nests',
                hAxis: {title: 'Event Type'},
                vAxis: {
                    title: 'Accuracy',
                    maxValue: 100,
                    minValue: 0,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Computer Accuracy with Consensus vs Experts on Tern and Plover Nests</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error (seconds) in either direction allowed for two events to be matched. The default value is 30.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This bar chart shows the percentage of expert observations that have a matching computed event for each event type and algorithm type.</p>

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
