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

$easy_query = "SELECT time_to_sec(timediff(end_time, start_time)) AS duration FROM watched_videos WHERE timediff(end_time, start_time) IS NOT NULL AND difficulty = 'easy' HAVING duration < 3600 ORDER BY duration";
$medium_query = "SELECT time_to_sec(timediff(end_time, start_time)) AS duration FROM watched_videos WHERE timediff(end_time, start_time) IS NOT NULL AND difficulty = 'medium' HAVING duration < 3600 ORDER BY duration";
$hard_query = "SELECT time_to_sec(timediff(end_time, start_time)) AS duration FROM watched_videos WHERE timediff(end_time, start_time) IS NOT NULL AND difficulty = 'hard' HAVING duration < 3600 ORDER BY duration";
$easy_result = query_wildlife_video_db($easy_query);
$medium_result = query_wildlife_video_db($medium_query);
$hard_result = query_wildlife_video_db($hard_query);

$easy_rows = $easy_result->num_rows;
$medium_rows = $medium_result->num_rows;
$hard_rows = $hard_result->num_rows;

echo "
<div class='containder'>
    <div class='row'>
        <div class='col-sm-12'>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['corechart']});
        google.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
";

echo "['Easy'";
$index = 0;
$total = 0;
while ($row = $easy_result->fetch_assoc()) {
    $total += $row['duration'];
    if ($index == 0) {
        echo ", " . $row['duration'];
    } else if ($index == floor(1/4 * $easy_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == floor(3/4 * $easy_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == $easy_rows - 1) {
        echo ", " . $row['duration'];
        //echo ", 'Mean: " . $total/$easy_rows . "'";
    }
    $index++;
}
echo "],\n";

echo "['Medium'";
$index = 0;
$total = 0;
while ($row = $medium_result->fetch_assoc()) {
    $total += $row['duration'];
    if ($index == 0) {
        echo ", " . $row['duration'];
    } else if ($index == floor(1/4 * $medium_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == floor(3/4 * $medium_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == $hard_rows - 1) {
        echo ", " . $row['duration'];
        //echo ", 'Mean: " . $total/$medium_rows . "'";
    }
    $index++;
}
echo "],\n";

echo "['Hard'";
$index = 0;
$total = 0;
while ($row = $hard_result->fetch_assoc()) {
    $total += $row['duration'];
    if ($index == 0) {
        echo ", " . $row['duration'];
    } else if ($index == floor(1/4 * $hard_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == floor(3/4 * $hard_rows)) {
        echo ", " . $row['duration'];
    } else if ($index == $hard_rows - 1) {
        echo ", " . $row['duration'];
        //echo ", 'Mean: " . $total/$hard_rows . "'";
    }
    $index++;
}
echo "]";

echo "
            ], true);

            var options = {
                title: 'Duration spent on video vs. User provided difficulty',
                vAxis: {title: 'Duration (Cut off at one hour to remove idle users)'},
                hAxis: {title: 'Difficulty'},
                legend: 'none'
            };

            var chart = new google.visualization.CandlestickChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>

            <h1>Observation Duration vs Perceived Difficulty</h1>

            <div id='chart_div' style='width: auto; height: 700px;'></div>

            <h2>Description:</h2>
            <p>This candlestick chart shows the amount of time a user sepent watching a video against their perceived difficulty of that video. Upper and lower bounds of the 'candle' are the first and third quartile of the data set. The ends of the 'wick' are the minimum and maximum values for the data set.</p>
            <p>The data set is set a maximim of single hour of observation since some users walk away without finishing a video or step away from the video for an extended period of time.</p>

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
