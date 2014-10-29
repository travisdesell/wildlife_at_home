<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname(dirname($cwd[__FILE__]));

//echo $cwd[__FILE__];
require_once($cwd[__FILE__] . "/../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/navbar.php");
//require_once($cwd[__FILE__] . "/../citizen_science_grid/news.php");
require_once($cwd[__FILE__] . "/../citizen_science_grid/footer.php");
//require_once($cwd[__FILE__] . "/../citizen_science_grid/uotd.php");
require_once($cwd[__FILE__] . "/webpage/wildlife_db.php");
require_once($cwd[__FILE__] . "/webpage/my_query.php");

print_header("Wildlife@Home: Duration vs Difficulty", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$easy_query = "SELECT experience FROM watched_videos_stats WHERE timediff(end_time, start_time) IS NOT NULL AND time_to_sec(timediff(end_time, start_time)) < 3600 AND difficulty = 'easy' ORDER BY experience ASC";
$medium_query = "SELECT experience FROM watched_videos_stats WHERE timediff(end_time, start_time) IS NOT NULL AND time_to_sec(timediff(end_time, start_time)) < 3600 AND difficulty = 'medium' ORDER BY experience ASC";
$hard_query = "SELECT experience FROM watched_videos_stats WHERE timediff(end_time, start_time) IS NOT NULL AND time_to_sec(timediff(end_time, start_time)) < 3600 AND difficulty = 'hard' ORDER BY experience ASC";
$easy_result = attempt_query_with_ping($easy_query, $wildlife_db);
$medium_result = attempt_query_with_ping($medium_query, $wildlife_db);
$hard_result = attempt_query_with_ping($hard_query, $wildlife_db);
if (!$easy_result || !$medium_result || !$hard_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $easy_query\n");
    die("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $easy_query\n");
}

$easy_rows = mysql_num_rows($easy_result);
$medium_rows = mysql_num_rows($medium_result);
$hard_rows = mysql_num_rows($hard_result);

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
while ($row = mysql_fetch_assoc($easy_result)) {
    $total += $row['experience'];
    if ($index == 0) {
        echo ", " . $row['experience'];
    } else if ($index == floor(1/4 * $easy_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == floor(3/4 * $easy_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == $easy_rows - 1) {
        echo ", " . $row['experience'];
        //echo ", 'Mean: " . $total/$easy_rows . "'";
    }
    $index++;
}
echo "],\n";

echo "['Medium'";
$index = 0;
$total = 0;
while ($row = mysql_fetch_assoc($medium_result)) {
    $total += $row['experience'];
    if ($index == 0) {
        echo ", " . $row['experience'];
    } else if ($index == floor(1/4 * $medium_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == floor(3/4 * $medium_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == $hard_rows - 1) {
        echo ", " . $row['experience'];
        //echo ", 'Mean: " . $total/$medium_rows . "'";
    }
    $index++;
}
echo "],\n";

echo "['Hard'";
$index = 0;
$total = 0;
while ($row = mysql_fetch_assoc($hard_result)) {
    $total += $row['experience'];
    if ($index == 0) {
        echo ", " . $row['experience'];
    } else if ($index == floor(1/4 * $hard_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == floor(3/4 * $hard_rows)) {
        echo ", " . $row['experience'];
    } else if ($index == $hard_rows - 1) {
        echo ", " . $row['experience'];
        //echo ", 'Mean: " . $total/$hard_rows . "'";
    }
    $index++;
}
echo "]";

echo "
            ], true);

            var options = {
                title: 'User percent experience vs. User provided difficulty',
                vAxis: {title: 'Percent Experience'},
                hAxis: {title: 'Provided Difficulty'},
                legend: 'none'
            };

            var chart = new google.visualization.CandlestickChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }
    </script>

            <h1>User Experience vs Difficulty</h1>

            <div id='chart_div' style='width: auto; height: 700px;'></div>

            <h2>Description:</h2>
            <p>This candlestick chart shows the relation of a user's experience vs their perceived difficulty of a video.</p>
            <p>Experience is measured as the amount of time a user has spent watching videos in seconds. In other words it is the sum of the durations of all videos they have watched prior to the currently observed video. If this is their first video then their experience is just that of the current video. If this is the last video then it is the total time they have spent watching video. This is not not be confused with the total amount of video-time they have watched.</p>
            <p>To simplify the calculation of experience we do not add experience collected in case were the user took longer than an hour to finish the classification.</p>

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
