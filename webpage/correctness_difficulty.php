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

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 5;
}

$easy_watch_query = "SELECT user_id, video_id FROM watched_videos WHERE difficulty = 'easy'";
$medium_watch_query = "SELECT user_id, video_id FROM watched_videos WHERE difficulty = 'medium'";
$hard_watch_query = "SELECT user_id, video_id FROM watched_videos WHERE difficulty = 'hard'";
$easy_watch_result = query_wildlife_video_db($easy_watch_query);
$medium_watch_result = query_wildlife_video_db($medium_watch_query);
$hard_watch_result = query_wildlife_video_db($hard_watch_query);

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
";

function standard_deviation($sample){
    if(is_array($sample)){
        $mean = array_sum($sample) / count($sample);
        foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
        return sqrt(array_sum($devs) / (count($devs) - 1));
    }
}

function getCorrectness($user_id, $video_id, $buffer) {
    $event_query = "SELECT event_id, to_seconds(start_time) AS start_sec, to_seconds(end_time) AS end_sec FROM timed_observations AS t JOIN observation_types AS e ON e.id = event_id WHERE expert = 0 AND user_id = $user_id AND video_id = $video_id AND start_time > 0 AND end_time > start_time AND EXISTS (SELECT * FROM timed_observations AS i WHERE t.video_id = i.video_id AND i.expert = 1 AND i.start_time > 0 AND i.end_time > i.start_time)";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    $num_match_events = 0;

    if ($num_events > 0) {
        while ($event_row = $event_result->fetch_assoc()) {
            $event_id = $event_row['event_id'];
            $start_sec = $event_row['start_sec'];
            $end_sec = $event_row['end_sec'];

            $start_sec_top = $start_sec - $buffer;
            $start_sec_bot = $start_sec + $buffer;
            $end_sec_top = $end_sec - $buffer;
            $end_sec_bot = $end_sec + $buffer;
            $match_query = "SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND to_seconds(start_time) BETWEEN $start_sec_top AND $start_sec_bot AND to_seconds(end_time) BETWEEN $end_sec_top AND $end_sec_bot";
            $match_result = query_wildlife_video_db($match_query);
            $num_matches = $match_result->num_rows;

            if ($num_matches >= 1) {
                $num_match_events += 1;
            }
        }

        assert($num_match_events <= $num_events);
        return ($num_match_events / $num_events);
    } else {
        return 0;
    }
}

$elements = array();
while ($easy_watch_row = $easy_watch_result->fetch_assoc()) {
    array_push($elements, getCorrectness($easy_watch_row['user_id'], $easy_watch_row['video_id'], $buffer));
}
sort($elements);
$size = sizeof($elements);
$avg = array_sum($elements) / $size;
$std = standard_deviation($elements);
if ($size > 0) {
    echo "[";
    echo "'Easy - $size'";
    echo ",";
    echo $elements[0];
    echo ",";
    echo $avg - $std;
    echo ",";
    echo $avg + $std;
    echo ",";
    echo $elements[$size - 1];
    echo "],";
}

$elements = array();
while ($medium_watch_row = $medium_watch_result->fetch_assoc()) {
    array_push($elements, getCorrectness($medium_watch_row['user_id'], $medium_watch_row['video_id'], $buffer));
}
sort($elements);
$size = sizeof($elements);
$avg = array_sum($elements) / $size;
$std = standard_deviation($elements);
if ($size > 0) {
    echo "[";
    echo "'Medium - $size'";
    echo ",";
    echo $elements[0];
    echo ",";
    echo $avg - $std;
    echo ",";
    echo $avg + $std;
    echo ",";
    echo $elements[$size - 1];
    echo "],";
}

$elements = array();
while ($hard_watch_row = $hard_watch_result->fetch_assoc()) {
    array_push($elements, getCorrectness($hard_watch_row['user_id'], $hard_watch_row['video_id'], $buffer));
}
sort($elements);
$size = sizeof($elements);
$avg = array_sum($elements) / $size;
$std = standard_deviation($elements);
if ($size > 0) {
    echo "[";
    echo "'Hard - $size'";
    echo ",";
    echo $elements[0];
    echo ",";
    echo $avg - $std;
    echo ",";
    echo $avg + $std;
    echo ",";
    echo $elements[$size - 1];
    echo "],";
}

echo "
                ], true);

";
echo "
            var options = {
                legend: 'none'

            };

            var chart = new google.visualization.CandlestickChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness vs Difficulty</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This candlestick chart shows the distribution of user correctness vs their perceived difficulty of a video. Correctness in this case is determined by the number of events in their observation that matched an expert event divided by the total number of events they observed for that video.
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
