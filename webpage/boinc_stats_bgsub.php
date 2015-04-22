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

if (!isset($view)) {
    $view = 'all';
}


$query = "SELECT COUNT(*) AS results, SUM(cpu_time)/60/60 AS cpu_hours, SUM(cpu_time)/COUNT(*)/60/60 AS cpu_hours_per_result FROM result WHERE appid = 22";
$result = query_boinc_db($query);

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
            data.addColumn('number', 'Number of Results');
            data.addColumn('number', 'Total CPU Hours');
            data.addColumn('number', 'CPU Hours Per Result');
            data.addRows([
";

while ($row = $result->fetch_assoc()) {
    $num_results = $row['results'];
    $cpu_hours= $row['cpu_hours'];
    $cpu_hours_per_result = $row['cpu_hours_per_result'];

    echo "[";
    echo "$num_results";
    echo ",";
    echo $cpu_hours;
    echo ",";
    echo $cpu_hours_per_result;
    echo "],";
}

echo "
                ]);

";
echo "

            var chart = new google.visualization.Table(document.getElementById('chart_div'));
            var view = new google.visualization.DataView(data);

            chart.draw(view);
        }
    </script>

            <h1>BOINC Stats for Wildlife Background Subtraction</h1>

            <div id='chart_div' style='margin: auto; width: 90%;'></div>
            
            <button onclick='downloadChart()'>Download as CSV</button>

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
