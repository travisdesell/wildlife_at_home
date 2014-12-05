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

print_header("Wildlife@Home: Duration vs Difficulty", "", "wildlife");
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

$type_query = "SELECT id, name FROM observation_types";
$type_result = query_wildlife_video_db($type_query, $wildlife_db);

echo "
<div class='containder'>
    <div class='row'>
        <div class='col-sm-12'>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['corechart']});
        google.setOnLoadCallback(drawChart);

        function getDate(date_string) {
            if (typeof date_string === 'string') {
                var a = date_string.split(/[- :]/);
                return new Date(a[0], a[1]-1, a[2], a[3] || 0, a[4] || 0, a[5] || 0);
            }
            return null;
        }

        function drawChart() {
            var container = document.getElementById('chart_div');
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Event Type');
            data.addColumn('number', 'Buffer Percent Correct');
            data.addColumn('number', 'Euclidean Percent Correct');
            data.addColumn('number', 'Combined Euclidean Percent Correct');
            data.addRows([
";
            //data.addColumn({type: 'string', role: 'tooltip'});

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id FROM timed_observations AS t WHERE expert = 0 AND event_id = $type_id AND start_time > 0 AND end_time > start_time AND EXISTS (SELECT * FROM timed_observations AS i WHERE t.video_id = i.video_id AND i.expert = 1 AND i.start_time > 0 AND i.end_time > i.start_time)";
    $timed_result = query_wildlife_video_db($timed_query);
    $num_events = $timed_result->num_rows;
    $buffer_match_events = 0;
    $euclidean_match_events = 0;
    $segmented_euclidean_match_events = 0;
    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];
        $buffer_correctness = getBufferCorrectness($obs_id, $buffer);
        $euclidean_correctness = getEuclidianCorrectness($obs_id);
        $segmented_euclidean_correctness = getSegmentedEuclidianCorrectness($obs_id);

        if ($euclidean_correctness > 0.95) {
            $euclidean_match_events += $euclidean_correctness;
        }
        
        if ($segmented_euclidean_correctness > 0.95) {
            $segmented_euclidean_match_events += $segmented_euclidean_correctness;
        }

        $buffer_match_events += $buffer_correctness;
    }

    if ($buffer_match_events > 0 || $euclidean_match_events > 0) {
        echo "[";
        echo "'$type_name'";
        echo ",";
        echo $buffer_match_events / $num_events;
        echo ",";
        echo $euclidean_match_events / $num_events;
        echo ",";
        echo $segmented_euclidean_match_events / $num_events;
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Percent of correct events for each type',
                vAxis: {
                    title: 'Percent Correct',
                    maxValue: 1.0,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness Test</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This bar chart show the percentage of user events that have a matching expert observed event. Each bar represents the event types.</p>
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
