<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

//echo $cwd[__FILE__];
require_once($cwd[__FILE__] . "/../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Duration vs Difficulty", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

$expert_query = "SELECT TIME_TO_SEC(start_time) AS start_time, TIME_TO_SEC(end_time) AS end_time FROM timed_observations WHERE expert = 1 AND video_id = $video_id";
$citizen_query = "SELECT TIME_TO_SEC(start_time) AS start_time, TIME_TO_SEC(end_time) AS end_time FROM timed_observations WHERE expert = 0 AND video_id = $video_id";
$expert_result = query_wildlife_video_db($expert_query);
$citizen_result = query_wildlife_video_db($citizen_query);

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
            var data = new google.visualization.arrayToDataTable([
                ['Start Time', 'Expert Time', 'Citizen Time'],
";

while ($row = $citizen_result->fetch_assoc()) {
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    echo "[";
    echo $start_time;
    echo ",";
    echo "null";
    echo ",";
    echo $end_time;
    echo "],";
}

while ($row = $expert_result->fetch_assoc()) {
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    echo "[";
    echo $start_time;
    echo ",";
    echo $end_time;
    echo ",";
    echo "null";
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Time Interval Plane',
                hAxis: {title: 'Start Time'},
                vAxis: {title: 'End Time'}
            };

            var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness Test</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This scatterplot shows the </p>
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
