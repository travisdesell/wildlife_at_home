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

print_header("Wildlife@Home: Computer Accuracy by Event Type", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 5;
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
";

foreach($algs as $a_id => $a_name) {
    echo "data.addColumn('number', '$a_name');";
}

echo "
            data.addRows([
";

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id, video_id, species_id FROM timed_observations AS t WHERE expert = 1 AND event_id = $type_id AND species_id = 1 AND start_time_s > 10 AND start_time_s <= end_time_s AND (SELECT COUNT(*) FROM computed_events AS comp WHERE comp.video_id = t.video_id) > 0";
    $timed_result = query_wildlife_video_db($timed_query);
    $alg_num_events = array();
    $alg_match_events = array();
    foreach($algs as $a_id => $a_name) {
        $alg_num_events[$a_id] = 0;
        $alg_match_events[$a_id] = 0;
    }
    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];
        $video_id = $timed_row['video_id'];
        //$species_id = $timed_row['species_id'];
        $expert_id = getExpert($video_id);

        foreach($algs as $a_id => $a_name) {
            list($start_match, $end_match) = getBufferAccuracy($obs_id, $a_id, $buffer);

            $alg_num_events[$a_id] += 2;

            $alg_match_events[$a_id] += $start_match;
            $alg_match_events[$a_id] += $end_match;
        }
    }

    $add_data = false;
    foreach($alg_match_events as $a_id => $a_val) {
        if ($a_val > 0) {
            $add_data = true;
        }
    }

    if ($add_data) {
        echo "[";
        echo "'$type_name'";
        foreach($alg_match_events as $a_id => $a_val) {
            echo ",";
            if ($alg_num_events[$a_id] > 0) {
                echo $a_val / $alg_num_events[$a_id] * 100;
            } else {
                echo "0";
            }
        }
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Computer accuracy for each event type',
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

            <h1>Computer Accuracy by Event Type</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error (seconds) in either direction allowed for two events to be matched. The default value is 30.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>TOOD: Edit this</p>
            <p>This bar chart show the percentage of user events that have a matching expert observed event. Each bar represens the percent of events that match an expert observation. The legent shows the breakdown for each species.</p>
            <p>In order to collect this data we discard all vidoes that do not have an expert observation or the expert observation is invalid. This is done by getting a list of all event types and then counting the total number of user events that have a matchins event and dividing it by the number of user events of that type that have an valid expert observation for that video.</p>

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
