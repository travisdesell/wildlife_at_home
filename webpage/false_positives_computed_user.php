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

print_header("Wildlife@Home: Computer False Positives vs Users By Species", "", "wildlife");
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

if (!isset($threshold)) {
    $threshold = 95;
}

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
            data.addColumn('string', 'Species');
";

foreach($algs as $a_id => $a_name) {
    echo "data.addColumn('number', '$a_name');";
}

echo "
            data.addRows([
";

while ($species_row = $species_result->fetch_assoc()) {
    $species_id = $species_row['id'];
    $species_name = $species_row['name'];
    $video_query = "SELECT DISTINCT video_id, user_id FROM timed_observations AS t WHERE expert = 0 AND species_id = $species_id AND start_time_s >= 0 AND start_time_s <= end_time_s AND EXISTS (SELECT * FROM computed_events AS comp JOIN event_algorithms AS alg ON comp.algorithm_id = alg.id WHERE comp.video_id = t.video_id AND alg.main_version_id = comp.version_id)";
    $video_result = query_wildlife_video_db($video_query);
    $alg_num_false = array();
    foreach($algs as $a_id => $a_name) {
        $alg_num_false[$a_id] = 0;
    }
    while ($video_row = $video_result->fetch_assoc()) {
        $video_id = $video_row['video_id'];
        $user_id = $video_row['user_id'];

        foreach($algs as $a_id => $a_name) {
            $alg_num_false[$a_id] += getFalsePositives($video_id, $user_id, $a_id, $buffer);
        }
    }

    $add_data = false;
    foreach($alg_num_false as $a_id => $a_val) {
        if ($a_val > 0) {
            $add_data = true;
        }
    }

    if ($add_data) {
        echo "[";
        echo "'$species_name'";
        foreach($alg_num_false as $a_id => $a_val) {
            echo ",";
            echo $a_val;
        }
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Computed False Positives vs Users for each Species',
                hAxis: {title: 'Species'},
                vAxis: {
                    title: 'False Positives',
                    minValue: 0,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Computer False Posities vs Users by Species</h1>

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
