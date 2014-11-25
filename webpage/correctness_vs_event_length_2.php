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

if (!isset($scale_factor)) {
    $scale_factor = 10;
}

if (!isset($video_id)) {
    $watch_query = "SELECT DISTINCT w.user_id, w.video_id FROM watched_videos w JOIN timed_observations AS obs ON obs.video_id = w.video_id JOIN video_2 AS vid ON vid.id = w.video_id WHERE obs.expert = 0 AND vid.duration_s >= 30 AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.end_time) > TO_SECONDS(i.start_time))";
} else { 
    $watch_query = "SELECT DISTINCT w.user_id, w.video_id FROM watched_videos w JOIN timed_observations AS obs ON obs.video_id = w.video_id JOIN video_2 AS vid ON vid.id = w.video_id WHERE w.video_id = $video_id AND obs.expert = 0 AND vid.duration_s >= 30 AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND TO_SECONDS(i.start_time) > 0 AND TO_SECONDS(i.end_time) > TO_SECONDS(i.start_time))";
}
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
                ['Event Duration as a Portion of Video Length', 'Buffer Correctness', 'Euclidian Correctness', 'Scaled Buffer Correctness', 'Scaled Euclidan Correctness'],
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

function getEventWeight($obs_id, $scale_factor) {
    $weight_query = "SELECT (v.duration_s/(TO_SECONDS(t.end_time) - TO_SECONDS(t.start_time) + v.duration_s/$scale_factor))/(SELECT SUM(vid.duration_s/(TO_SECONDS(obs.end_time) - TO_SECONDS(obs.start_time) + vid.duration_s/$scale_factor)) FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE obs.video_id = t.video_id AND obs.user_id = t.user_id GROUP BY obs.user_id) AS weight FROM timed_observations AS t JOIN video_2 AS v ON v.id = t.video_id WHERE t.id = $obs_id";
    $weight_result = query_wildlife_video_db($weight_query);
        while ($weight_row = $weight_result->fetch_assoc()) {
            return $weight_row['weight'];
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
        $buffer_correctness = getBufferCorrectness($obs_id, $buffer);
        $euclidian_correctness = getEuclidianCorrectness($obs_id);
        $scaled_event_weight = getEventWeight($obs_id, $scale_factor);
        $event_weight = 1/$num_events;
        echo "[";
        echo $event_duration_proportion;
        echo ",";
        echo $buffer_correctness * $event_weight;
        echo ",";
        echo $euclidian_correctness * $event_weight;
        echo ",";
        echo $buffer_correctness * $scaled_event_weight;
        echo ",";
        echo $euclidian_correctness * $scaled_event_weight;
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Event Correctness vs Event Length',
                vAxis: {title: 'Event Correctness as a Portion of Total Observation Correctness'},
                hAxis: {title: 'Event Duration as a Portion of Video Length'},
                series: {
                    0: { pointSize: 10, pointShape: 'square' },
                    1: { pointSize: 5, pointShape: 'square' },
                    2: { pointSize: 10, pointShape: 'triangle' },
                    3: { pointSize: 5, pointShape: 'triangle' },
                    4: { pointShape: 'star' },
                    5: { pointShape: 'diamond' },
                    6: { pointShape: 'circle' },
                    7: { pointShape: 'polygon' }
                },
                trendlines: {
                    0: {type: 'exponential'},
                    1: {type: 'exponential'},
                    2: {type: 'exponential'},
                    3: {type: 'exponential'}
                }
            };

            var chart = new google.visualization.ScatterChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Event Correctness VS Event Length (2.0)</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>video_id=</dt>
                <dd>The ID of the video in the database. If left empty this will load data for all video (slow). Also, if all data is loaded there will be a gernal upward trend since the majority of videos only have one or two events.</dd>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>
                <dt>scale_factor=</dt>
                <dd>The skewness input for the scaled correctness algorithms, large values give greater favor to the shorter events and smaller values weight events more evenly. This value must be greater than 0. The default value is 10.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This scatterplot with trendlines shows the events for a given video and shows what percentange of the total user observation an event is worth and how this compares event legth as a portion of video length.</p>
            <p>Video ID 6511 is a decent example.</p>

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
