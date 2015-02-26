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

print_header("Wildlife@Home: Event Weight vs Length", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

if (!isset($video_id)) {
    $video_id = 6511;
}

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 5;
}

if (!isset($scale_factor)) {
    $scale_factor = 0.10;
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

while ($watch_row = $watch_result->fetch_assoc()) {
    $user_id = $watch_row['user_id'];
    $video_id = $watch_row['video_id'];
    $expert_id = getExpert($video_id);
    $event_query = "SELECT TO_SECONDS(obs.start_time) AS start_time, TO_SECONDS(obs.end_time) AS end_time, duration_s, obs.id AS obs_id FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = obs.video_id WHERE user_id = $user_id AND obs.video_id = $video_id AND TO_SECONDS(obs.start_time) > 0 AND TO_SECONDS(obs.start_time) < TO_SECONDS(obs.end_time) AND TO_SECONDS(obs.end_time) - TO_SECONDS(obs.start_time) <= vid.duration_s";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    while ($event_row = $event_result->fetch_assoc()) {
        $obs_id = $event_row['obs_id'];
        $event_length = $event_row['end_time'] - $event_row['start_time'];
        $video_length = $event_row['duration_s'];
        $event_duration_proportion = $event_length/$video_length;
        list($buffer_correctness, $buffer_specificity) = getBufferCorrectness($obs_id, $expert_id, $buffer);
        list($euclidean_correctness, $euclidean_specificity) = getEuclideanCorrectness($obs_id, $expert_id);
        $scaled_event_weight = getEventScaledWeight($obs_id, $expert_id, $scale_factor);
        $event_weight = getEventWeight($obs_id, $expert_id);
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
                title: 'Event Weight vs Event Length',
                vAxis: {title: 'Event Correctness as a Portion of Total Observation Correctness'},
                hAxis: {title: 'Event Duration as a Portion of Video Length'},
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

            <h1>Event Weight vs Event Length</h1>

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
