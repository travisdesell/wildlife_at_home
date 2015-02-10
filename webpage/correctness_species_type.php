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

print_header("Wildlife@Home: Correctness by Event Type and Species", "", "wildlife");
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

$species = array();
while ($species_row = $species_result->fetch_assoc()) {
    $species_id = $species_row['id'];
    $species_name = $species_row['name'];
    $species[$species_id] = $species_name;
}
ksort($species);

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

foreach($species as $s_id => $s_name) {
    echo "data.addColumn('number', '$s_name');";
}

echo "
            data.addRows([
";

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id, video_id, species_id FROM timed_observations AS t WHERE expert = 0 AND event_id = $type_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(end_time) >= TO_SECONDS(start_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE t.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.end_time) >= TO_SECONDS(i.start_time))";
    $timed_result = query_wildlife_video_db($timed_query);
    $species_num_events = array();
    $species_match_events = array();
    foreach($species as $s_id => $s_name) {
        $species_num_events[$s_id] = 0;
        $species_match_events[$s_id] = 0;
    }
    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];
        $video_id = $timed_row['video_id'];
        $species_id = $timed_row['species_id'];

        $expert_query = "SELECT user_id FROM timed_observations WHERE video_id = $video_id AND expert = 1 LIMIT 1";
        $expert_result = query_wildlife_video_db($expert_query);
        $expert_id = -1;
        while ($expert_row = $expert_result->fetch_assoc()) {
            $expert_id = $expert_row['user_id'];
        }

        //$correctness = getBufferCorrectness($obs_id, $expert_id, $buffer);
        list($correctness, $specificity) = getEuclideanCorrectness($obs_id, $expert_id, $threshold);
        //$correctness = getSegmentedEuclideanCorrectness($obs_id, $expert_id, $threshold);

        $species_num_events[$species_id] += 1;

        if ($specificity) {
            $species_match_events[$species_id] += $correctness;
        }
    }

    $add_data = false;
    foreach($species_match_events as $s_id => $s_val) {
        if ($s_val > 0) {
            $add_data = true;
        }
    }

    if ($add_data) {
        echo "[";
        echo "'$type_name'";
        foreach($species_match_events as $s_id => $s_val) {
            echo ",";
            if ($species_num_events[$s_id] > 0) {
                echo $s_val / $species_num_events[$s_id] * 100;
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
                title: 'Percent of correct events for each type by species',
                hAxis: {title: 'Event Type'},
                vAxis: {
                    title: 'Percent Correct',
                    maxValue: 100,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness by Event Type and Species</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
            </dl>
            

            <h2>Description:</h2>
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
