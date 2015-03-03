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

print_header("Wildlife@Home: Algorithm Accuracy", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

if (!isset($video_id)) {
    $video_id = 6511;
}

if (!isset($buffer)) {
    $buffer= 5;
}

if (!isset($scale_factor)) {
    $scale_factor= 0.10;
}

$duration_query = "SELECT duration_s FROM video_2 WHERE id = $video_id";
$duration_result = query_wildlife_video_db($duration_query);
$duration_row = $duration_result->fetch_assoc();
$video_duration = $duration_row['duration_s'];

$query = "SELECT id, name FROM event_algorithms";

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
            data.addColumn('string', 'Name');
            data.addColumn('number', 'Buffer Correctness');
            data.addRows([
";

$result = query_wildlife_video_db($query);
while ($row = $result->fetch_assoc()) {
    $algorithm_id = $row['id'];
    $algorithm_name = $row['name'];
    $expert_id = getExpert($video_id);

    $obs_query = "SELECT id FROM timed_observations WHERE user_id = $expert_id AND video_id = $video_id AND start_time_s > 10 AND start_time_s <= end_time_s";
    $obs_result = query_wildlife_video_db($obs_query);

    $total = $obs_result->num_rows * 2;
    $total_buffer_correctness = 0;
    while ($obs_row = $obs_result->fetch_assoc()) {
        $obs_id = $obs_row['id'];
        list($buffer_correctness, $buffer_specificity) = getBufferAccuracy($obs_id, $algorithm_id, $buffer);
        #$total_buffer_correctness += $buffer_correctness * getComputedEventWeight($obs_id, $expert_id);
        $total_buffer_correctness += $buffer_correctness;
    }
    echo "[";
    echo "'$algorithm_name'";
    echo ",";
    echo $total_buffer_correctness * 100 / $total;
    echo "],";
}

echo "
                ]);
";

echo "
            var options = {
                title: 'Accuracy',
                hAxis: {title: 'Algorithm'},
                vAxis: {
                    title: 'Percent Accuracy',
                    maxValue: 100,
                    minValue: 0,
                },
            };
            
            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Algorithm Accuracy</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>video_id=</dt>
                <dd>The ID of the video in the database.</dd>

                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This barchart shows how each algorithm's accuracy is rated with the buffer correctness algorithm.</p>

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
