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

print_header("Wildlife@Home: Video Time Plane", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

if (!isset($video_id)) {
    $video_id = 6511;
}

if (!isset($check_seg)) {
    $check_seg = 1;
}

$duration_query = "SELECT duration_s FROM video_2 WHERE id = $video_id";
$duration_result = query_wildlife_video_db($duration_query);
$duration_row = $duration_result->fetch_assoc();
$video_duration = $duration_row['duration_s'];

$query = "SELECT obs.id, (TIME_TO_SEC(obs.start_time) - TIME_TO_SEC(vid.start_time)) AS start_time, (TIME_TO_SEC(obs.end_time) - TIME_TO_SEC(vid.start_time)) AS end_time, type.name AS type_name, event_id, user_id FROM timed_observations obs JOIN observation_types AS type ON type.id = event_id JOIN video_2 AS vid ON vid.id = video_id WHERE expert = 0 AND video_id = $video_id AND obs.start_time > 0 AND obs.start_time < obs.end_time";
$result = query_wildlife_video_db($query);

echo "
<div class='containder'>
    <div class='row'>
    <div class='col-sm-12'>
    <script type = 'text/javascript' src='js/data_download.js'></script>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['corechart']});
        google.setOnLoadCallback(drawChart);

        var data;

        function downloadChart() {
            var csv_data = dataTableToCSV(data);
            downloadCSV(csv_data);
        }

        function getDate(date_string) {
            if (typeof date_string === 'string') {
                var a = date_string.split(/[- :]/);
                return new Date(a[0], a[1]-1, a[2], a[3] || 0, a[4] || 0, a[5] || 0);
            }
            return null;
        }

        function drawChart() {
            var container = document.getElementById('chart_div');
            data = new google.visualization.DataTable();
            data.addColumn('string', 'ID');
            data.addColumn('number', 'Start Time');
            data.addColumn('number', 'End Time');
            data.addColumn('string', 'Type Name');
            data.addColumn('number', 'Percent Error');
            data.addRows([
";

while ($row = $result->fetch_assoc()) {
    $name_query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];

    $obs_id = $row['id'];
    $expert_id = getExpert($video_id);
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    $type_id = $row['event_id'];
    $type_name = $row['type_name'];
    if ($check_seg) {
        list($segmented_euclidean_correctness, $segmented_euclidean_specificity) = getSegmentedEuclideanCorrectness($obs_id, $expert_id);
        $error = 1 - $segmented_euclidean_correctness;
        //$distance = distToClosestExpertCombinedEvents($video_id, $type_id, $start_time, $end_time);
    } else {
        list($euclidean_correctness, $euclidean_specificity) = getEuclideanCorrectness($obs_id, $expert_id);
        $error = 1 - $euclidean_correctness;
        //$distance = distToClosestExpertEvent($video_id, $type_id, $start_time, $end_time);
    }
    $value = ($end_time-$start_time)/$video_duration;
    $start_percent = $start_time/$video_duration * 100;
    $end_percent = $end_time/$video_duration * 100;

    if ($start_percent > 100) {
        $start_percent = 100;
    }
    if ($end_percent > 100) {
        $end_percent = 100;
    }

    echo "[";
    echo "''";
    echo ",";
    echo $start_percent;
    echo ",";
    echo $end_percent;
    echo ",";
    echo "'$type_name'";
    echo ",";
    echo $error * 100;
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Time Interval Plane',
                hAxis: {
                    title: 'Start Time',
                    maxValue: 100
                },
                vAxis: {
                    title: 'End Time',
                    maxValue: 100
                }
            };

            var chart = new google.visualization.BubbleChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Video Time Plane</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>

                <dt>check_seg=</dt>
                <dd>Boolean value (0 or 1) to check for segmented events.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This scatterplot shows events plotted with their distance from a matching expert event as size. This means large dots indicate a possible incorrect user event. Color indicates event type and can show which event types users have a difficult time classifying.</p>

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
