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

print_header("Wildlife@Home: Correctness by Event Type", "", "wildlife");
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

echo "
<div class='containder'>
    <div class='row'>
    <div class='col-sm-12'>
    <script type = 'text/javascript' src='js/data_download.js'></script>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1.1', {packages:['table']});
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
            data.addColumn('number', 'Total');
            data.addColumn('number', 'Buffer True Positives');
            data.addColumn('number', 'Buffer False Positives');
            data.addColumn('number', 'Euclidean True Positives');
            data.addColumn('number', 'Euclidean False Positives');
            data.addColumn('number', 'Segment Checking Euclidean True Positives');
            data.addColumn('number', 'Segment Checking Euclidean False Positives');
            data.addRows([
";

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id, video_id FROM timed_observations AS t WHERE expert = 0 AND event_id = $type_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(end_time) >= TO_SECONDS(start_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE t.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.end_time) >= TO_SECONDS(i.start_time))";
    $timed_result = query_wildlife_video_db($timed_query);
    $num_events = $timed_result->num_rows;
    $buffer_true_positives = 0;
    $buffer_false_positives = 0;
    $euclidean_true_positives = 0;
    $euclidean_false_positives = 0;
    $segmented_euclidean_true_positives = 0;
    $segmented_euclidean_false_positives = 0;
    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];
        $video_id = $timed_row['video_id'];

        $expert_query = "SELECT user_id FROM timed_observations WHERE video_id = $video_id AND expert = 1 LIMIT 1";
        $expert_result = query_wildlife_video_db($expert_query);
        $expert_id = -1;
        while ($expert_row = $expert_result->fetch_assoc()) {
            $expert_id = $expert_row['user_id'];
        }

        list($buffer_correctness, $buffer_specificity) = getBufferCorrectness($obs_id, $expert_id, $buffer);
        list($euclidean_correctness, $euclidean_specificity) = getEuclideanCorrectness($obs_id, $expert_id, $threshold);
        list($segmented_euclidean_correctness, $segmented_euclidean_specificity) = getSegmentedEuclideanCorrectness($obs_id, $expert_id, $threshold);

        if ($euclidean_specificity) {
            $euclidean_true_positives += 1;
        } else {
            $euclidean_false_positives += 1;
        }
        
        if ($segmented_euclidean_specificity) {
            $segmented_euclidean_true_positives += 1;
        } else {
            $segmented_euclidean_false_positives += 1;
        }

        if ($buffer_specificity) {
            $buffer_true_positives += 1;
        } else {
            $buffer_false_positives += 1;
        }
    }

    echo "[";
    echo "'$type_name'";
    echo ",";
    echo $num_events;
    echo ",";
    echo $buffer_true_positives;
    echo ",";
    echo $buffer_false_positives;
    echo ",";
    echo $euclidean_true_positives;
    echo ",";
    echo $euclidean_false_positives;
    echo ",";
    echo $segmented_euclidean_true_positives;
    echo ",";
    echo $segmented_euclidean_false_positives;
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Percent of correct events for each type'
            };

            var chart = new google.visualization.Table(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness by Type as a Table</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: auto;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
                <dt>threshold=</dt>
                <dd>The correctness required to count two events as a true match. The default value is 95%.</dd>
            </dl>

            <h2>Description:</h2>
            <p>This table shows the number of true positives and false positives for each event type and algorithm.</p>
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
