<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

//echo $cwd[__FILE__];
require_once($cwd[__FILE__] . "/../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Video Timeline", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

$query = "SELECT user_id, expert, ot.name AS event_name, start_time, end_time FROM timed_observations JOIN observation_types AS ot ON event_id = ot.id WHERE video_id = $video_id AND start_time > 0 AND end_time > 0 AND start_time < end_time ORDER BY expert DESC";
$result = query_wildlife_video_db($query);

echo "
<div class='containder'>
    <div class='row'>
    <div class='col-sm-12'>
    <script type = 'text/javascript' src='js/data_download.js'></script>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['timeline']});
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
            var chart = new google.visualization.Timeline(container);
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Event Type');
            data.addColumn('date', 'Start');
            data.addColumn('date', 'End');
            data.addRows([
";

while ($row = $result->fetch_assoc()) {
    $name_query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];
    if ($row['expert'] == 1) {
        $name = $name . " (expert)";
    }

    echo "['" . $name . "'";
    echo ",'" . $row['event_name'] . "'";
    echo ", getDate('" . $row['start_time'] . "')";
    echo ", getDate('" . $row['end_time'] . "')";
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                backgroundColor: '#ffd'
            };

            chart.draw(data, options);
        }
    </script>

            <h1>Video Timeline</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>video_id=</dt>
                <dd>The ID of the video in the database.</dd>
            </dl>
            
            <h2>Description:</h2>
            <p>This chart is a timeline of the user events calculated for a specifed video (see parameters section). This provides information at a glance of how user events compared against each other and the expert(s). If an expert has classified a video it will appear in the top position of the timelime.</p>

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
