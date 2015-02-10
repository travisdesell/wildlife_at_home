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

$query = "SELECT user_id, video_id FROM watched_videos WHERE video_id = $video_id";

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
            data.addColumn('number', 'Euclidean Correctness');
            data.addColumn('number', 'Segment Checking Euclidean Correctness');
            data.addColumn('number', 'Segment Checking Euclidean Correctness (Recurse)');
            data.addRows([
";

$result = query_wildlife_video_db($query);
while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $name_query = "SELECT name FROM user WHERE id = " . $user_id;
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];
    $expert_id = getExpert($video_id);

    $obs_query = "SELECT id FROM timed_observations WHERE user_id = $user_id AND video_id = $video_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(start_time) < TO_SECONDS(end_time)";
    $obs_result = query_wildlife_video_db($obs_query);

    $total_buffer_correctness = 0;
    $total_euclidean_correctness = 0;
    $total_segmented_euclidean_correctness = 0;
    $total_segmented_euclidean_correctness_recurse = 0;
    while ($obs_row = $obs_result->fetch_assoc()) {
        $obs_id = $obs_row['id'];
        list($buffer_correctness, $buffer_specificity) = getBufferCorrectness($obs_id, $expert_id, $buffer);
        list($euclidean_correctness, $euclidean_specificity) = getEuclideanCorrectness($obs_id, $expert_id);
        list($segmented_euclidean_correctness, $segmented_euclidean_specificity) = getSegmentedEuclideanCorrectness($obs_id, $expert_id, 95, false);
        list($segmented_euclidean_correctness_recurse, $segmented_euclidean_specificity_recurse) = getSegmentedEuclideanCorrectness($obs_id, $expert_id);
        $total_buffer_correctness += $buffer_correctness * getEventWeight($obs_id, $expert_id);
        $total_euclidean_correctness += $euclidean_correctness * getEventWeight($obs_id, $expert_id);
        $total_segmented_euclidean_correctness += $segmented_euclidean_correctness * getEventWeight($obs_id, $expert_id);
        $total_segmented_euclidean_correctness_recurse += $segmented_euclidean_correctness * getEventWeight($obs_id, $expert_id);
    }
    echo "[";
    echo "'$name'";
    echo ",";
    echo $total_buffer_correctness * 100;
    echo ",";
    echo $total_euclidean_correctness * 100;
    echo ",";
    echo $total_segmented_euclidean_correctness * 100;
    echo ",";
    echo $total_segmented_euclidean_correctness_recurse * 100;
    echo "],";
}

echo "
                ]);
            var new_data = new google.visualization.arrayToDataTable([
                ['Name', 'Buffer Correctness', 'Euclidean Correctness', 'Segment Checking Euclidean Correctness', 'Segment Checking Euclidean Correctness (Recurse)'],
";

$result = query_wildlife_video_db($query);
while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $name_query = "SELECT name FROM user WHERE id = " . $user_id;
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];

    $obs_query = "SELECT id FROM timed_observations WHERE user_id = $user_id AND video_id = $video_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(start_time) < TO_SECONDS(end_time)";
    $obs_result = query_wildlife_video_db($obs_query);

    $total_buffer_correctness = 0;
    $total_euclidean_correctness = 0;
    $total_segmented_euclidean_correctness = 0;
    $total_segmented_euclidean_correctness_recurse = 0;
    while ($obs_row = $obs_result->fetch_assoc()) {
        $obs_id = $obs_row['id'];
        list($buffer_correctness, $buffer_specificity) = getBufferCorrectness($obs_id, $expert_id, $buffer);
        list($euclidean_correctness, $euclidean_specificity) = getEuclideanCorrectness($obs_id, $expert_id);
        list($segmented_euclidean_correctness, $segmented_euclidean_specificity) = getSegmentedEuclideanCorrectness($obs_id, $expert_id, 95, false);
        list($segmented_euclidean_correctness_recurse, $segmented_euclidean_specificity_recurse) = getSegmentedEuclideanCorrectness($obs_id, $expert_id);
        $total_buffer_correctness += $buffer_correctness * getEventScaledWeight($obs_id, $expert_id, $scale_factor);
        $total_euclidean_correctness += $euclidean_correctness * getEventScaledWeight($obs_id, $expert_id, $scale_factor);
        $total_segmented_euclidean_correctness += $segmented_euclidean_correctness * getEventScaledWeight($obs_id, $expert_id, $scale_factor);
        $total_segmented_euclidean_correctness_recurse += $segmented_euclidean_correctness_recurse * getEventScaledWeight($obs_id, $expert_id, $scale_factor);
    }
    echo "[";
    echo "'$name'";
    echo ",";
    echo $total_buffer_correctness * 100;
    echo ",";
    echo $total_euclidean_correctness * 100;
    echo ",";
    echo $total_segmented_euclidean_correctness * 100;
    echo ",";
    echo $total_segmented_euclidean_correctness_recurse * 100;
    echo "],";
}

echo "
                ]);
";

echo "
            var options = {
                title: 'User Correctness',
                hAxis: {title: 'User'},
                vAxis: {
                    title: 'Percent Correct',
                    maxValue: 100,
                    minValue: 0,
                },
                diff: {oldData: {title: 'Data'}}
            };
            
            data = new_data;

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            var diffData = chart.computeDiff(old_data, new_data);

            chart.draw(diffData, options);
        }
    </script>

            <h1>User Correctness</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>video_id=</dt>
                <dd>The ID of the video in the database.</dd>

                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>

                <dt>scale_factor=</dt>
                <dd>This value adjust the weight given to short events. Values close to 0 heavily favor shorter events and large values (~100) weight events evenly. The default value is 0.1.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This barchart shows how each user was rated with the three different correctness algorithms and how those scores are affected according to the weight of each event. The grey background is a fair weighting (event correctness / total number of events) and the colored foreground is a scaled weight where short events are given a larger portion of the total observational weight.</p>

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
