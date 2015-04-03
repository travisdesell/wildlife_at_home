<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

//echo $cwd[__FILE__];
require_once($cwd[__FILE__] . "/../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Expert Time Conflict Table", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
//parse_str($_SERVER['QUERY_STRING']);

$query = "SELECT DISTINCT vid.animal_id, vid.watermarked_filename AS video_name, obs.video_id, ot.name AS event_name, obs.start_time, obs.end_time FROM timed_observations AS obs JOIN observation_types AS ot ON obs.event_id = ot.id JOIN video_2 AS vid ON vid.id = obs.video_id WHERE expert = 1 AND (obs.start_time_s < 0 OR obs.start_time_s > obs.end_time_s)";

$result = query_wildlife_video_db($query);

echo "
<div class='containder'>
    <div class='row'>
        <div class='col-sm-12'>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['table']});
        google.setOnLoadCallback(drawChart);

        function drawChart() {
            var container = document.getElementById('chart_div');
            var chart = new google.visualization.Table(container);
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Animal ID');
            data.addColumn('string', 'Video ID');
            data.addColumn('string', 'Video Name');
            data.addColumn('string', 'Event Type');
            data.addRows([
";

while ($row = $result->fetch_assoc()) {
    echo "['" . trim($row['animal_id']) . "'";
    echo ",'" . trim($row['video_id']) . "'";
    echo ",'" . trim(end(explode('/', $row['video_name']))) . "'";
    echo ",'" . trim($row['event_name']) . "'";
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                showRowNumber: true
            };

            chart.draw(data, options);
        }
    </script>

            <h1>Expert Time Conflicts Table</h1>
            <p>Tuples can be sorted by clicking on column headers. Also, fixed events should be removed if this page is refreshed.</p>

            <div id='chart_div' style='margin: auto; width: auto; height: auto;'></div>

            <h2>Description:</h2>
            <p>This table is a collection of expert classified events where the event start time is less than 0 or the start time is greater than or equal to the end time.<p>
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
