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

$type_query = "SELECT id, name FROM observation_types";
$type_result = query_wildlife_video_db($type_query, $wildlife_db);

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

function getExpertMatch($timed_id, $buffer) {
    $event_query = "SELECT event_id, video_id, to_seconds(start_time) AS start_sec, to_seconds(end_time) AS end_sec FROM timed_observations WHERE id = $timed_id";
    $event_result = query_wildlife_video_db($event_query);
    $num_events = $event_result->num_rows;
    $num_match_events = 0;

    if ($num_events > 0) {
        while ($event_row = $event_result->fetch_assoc()) {
            $event_id = $event_row['event_id'];
            $video_id = $event_row['video_id'];
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

        return $num_match_events / $num_events;
    } else {
        return 0;
    }
}

$events = array();
echo "['Hour',";
while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    //$timed_query = "SELECT HOUR(obs.start_time) AS hour, COUNT(*) AS count FROM timed_observations AS obs WHERE expert = 0 AND event_id = $type_id AND start_time > 0 AND end_time > start_time AND EXISTS (SELECT * FROM timed_observations AS i WHERE obs.video_id = i.video_id AND i.expert = 1 AND i.start_time > 0 AND i.end_time > i.start_time) GROUP BY hour ORDER BY hour";
    $timed_query = "SELECT HOUR(obs.start_time) AS hour, COUNT(*) AS count FROM timed_observations AS obs WHERE expert = 1 AND event_id = $type_id AND start_time > 0 AND end_time > start_time GROUP BY hour ORDER BY hour";
    $timed_result = query_wildlife_video_db($timed_query);

    $event_count = array();
    while ($timed_row = $timed_result->fetch_assoc()) {
        //$num_match_events += getExpertMatch($timed_row['id'], $buffer);
        $event_count[$timed_row['hour']]=$timed_row['count'];
    }
    array_push($events, $event_count);

    echo "'$type_name'";
    echo ",";
}
echo "],";

for($hour=0;$hour<24;$hour++) {
    echo "[$hour,";
    for($event=0;$event<count($events);$event++) {
        $temp_val = 0;
        if (array_key_exists($hour, $events[$event])) {
            $temp_val = $events[$event][$hour];
        }
        echo "$temp_val,";
    }
    echo"],";
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Number of event occurrences in each hour of the day',
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Events by Time of Day</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

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
