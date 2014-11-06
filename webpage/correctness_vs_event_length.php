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

$watch_query = "SELECT DISTINCT w.user_id, w.video_id FROM watched_videos w JOIN timed_observations AS obs ON obs.video_id = w.video_id JOIN video_2 AS vid ON vid.id = w.video_id WHERE obs.expert = 0 AND vid.duration_s >= 30 AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.end_time) > TO_SECONDS(i.start_time))";
$watch_result = query_wildlife_video_db($watch_query);

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
                ['Event Duration as a Portion of Video Length', 'Event Correctness as a Portion of Total Observation Correctness'],
";

function getBufferCorrectness($obs_id, $buffer) {
    $event_query = "SELECT video_id, event_id, TO_SECONDS(start_time) AS start_time, TO_SECONDS(end_time) AS end_time FROM timed_observations AS obs WHERE obs.id = $obs_id AND TO_SECONDS(start_time) > 0 AND TO_SECONDS(start_time) < TO_SECONDS(end_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.start_time) < TO_SECONDS(i.end_time))";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    $num_match_events = 0;

    if ($num_events > 0) {
        while ($event_row = $event_result->fetch_assoc()) {
            $video_id = $event_row['video_id'];
            $event_id = $event_row['event_id'];
            $start_sec = $event_row['start_time'];
            $end_sec = $event_row['end_time'];

            $start_sec_top = $start_sec - $buffer;
            $start_sec_bot = $start_sec + $buffer;
            $end_sec_top = $end_sec - $buffer;
            $end_sec_bot = $end_sec + $buffer;
            $match_query = "SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND to_seconds(start_time) BETWEEN $start_sec_top AND $start_sec_bot AND TO_SECONDS(end_time) BETWEEN $end_sec_top AND $end_sec_bot";
            $match_result = query_wildlife_video_db($match_query);
            $num_matches = $match_result->num_rows;

            if ($num_matches >= 1) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    return 0;
}

function getEuclidianCorrectness($obs_id) {
    $event_query = "SELECT obs.video_id, obs.event_id, vid.duration_s, (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.id = $obs_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time) AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.start_time) < TO_SECONDS(i.end_time))";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    $num_match_events = 0;

    if ($num_events > 0) {
        while ($event_row = $event_result->fetch_assoc()) {
            $video_id = $event_row['video_id'];
            $event_id = $event_row['event_id'];
            $video_duration = $event_row['duration_s'];
            $start_time = $event_row['start_time'];
            $end_time = $event_row['end_time'];
            $match_query = "SELECT (TO_SECONDS(obs.start_time) - TO_SECONDS(vid.start_time)) AS start_time, (TO_SECONDS(obs.end_time) - TO_SECONDS(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time)";
            $match_result = query_wildlife_video_db($match_query);

            $min_dist = -1;
            $max_dist = ($video_duration*1.41421356237); // Divide by hypotenuse of a square
            while ($row = $match_result->fetch_assoc()) {
                $temp_start = $row['start_time'];
                $temp_end = $row['end_time'];
                $dist = sqrt((($temp_start - $start_time)*($temp_start - $start_time)) + (($temp_end - $end_time)*($temp_end - $end_time)));
                if ($dist <= $max_dist && ($min_dist == -1 || $dist < $min_dist)) {
                    $min_dist = $dist;
                }
            }

            return 1-($min_dist/$max_dist);
        }
    }
    return 0;
}

while ($watch_row = $watch_result->fetch_assoc()) {
    $user_id = $watch_row['user_id'];
    $video_id = $watch_row['video_id'];
    $event_query = "SELECT TO_SECONDS(obs.start_time) AS start_time, TO_SECONDS(obs.end_time) AS end_time, duration_s, obs.id AS obs_id FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE user_id = $user_id AND obs.video_id = $video_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time) AND TO_SECONDS(obs.end_time) - TO_SECONDS(obs.start_time) <= vid.duration_s";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    while ($event_row = $event_result->fetch_assoc()) {
        $obs_id = $event_row['obs_id'];
        $event_length = $event_row['end_time'] - $event_row['start_time'];
        $video_length = $event_row['duration_s'];
        $event_duration_proportion = $event_length/$video_length;
        //$correctness = getBufferCorrectness($obs_id, $buffer);
        $correctness = getEuclidianCorrectness($obs_id);
        echo "[";
        echo $event_duration_proportion;
        echo ",";
        echo $correctness/$num_events;
        //echo $correctness;
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Correctness vs Experience',
                vAxis: {title: 'Event Correctness as a Portion of Total Observation Correctness'},
                hAxis: {title: 'Event Duration as a Portion of Video Length'},
                legend: 'none'
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
