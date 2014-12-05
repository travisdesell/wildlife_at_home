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

if (!isset($check_seg)) {
    $check_seg = 1;
}

$duration_query = "SELECT duration_s FROM video_2 WHERE id = $video_id";
$duration_result = query_wildlife_video_db($duration_query);
$duration_row = $duration_result->fetch_assoc();
$video_duration = $duration_row['duration_s'];

$query = "SELECT (TIME_TO_SEC(obs.start_time) - TIME_TO_SEC(vid.start_time)) AS start_time, (TIME_TO_SEC(obs.end_time) - TIME_TO_SEC(vid.start_time)) AS end_time, type.name AS type_name, event_id, user_id FROM timed_observations obs JOIN observation_types AS type ON type.id = event_id JOIN video_2 AS vid ON vid.id = video_id WHERE expert = 0 AND video_id = $video_id AND obs.start_time > 0 AND obs.start_time < obs.end_time";
$result = query_wildlife_video_db($query);

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
                ['ID', 'Start Time', 'Citizen End Time', 'Type Name', 'Percent Error'],
";

function distToClosestExpertEvent($video_id, $event_id, $start_time, $end_time) {
    $query = "SELECT (TIME_TO_SEC(obs.start_time) - TIME_TO_SEC(vid.start_time)) AS start_time, (TIME_TO_SEC(obs.end_time) - TIME_TO_SEC(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND obs.start_time > 0 AND obs.start_time < obs.end_time";
    $result = query_wildlife_video_db($query);

    $min_dist = -1;
    while ($row = $result->fetch_assoc()) {
        $temp_start = $row['start_time'];
        $temp_end = $row['end_time'];
        $dist = sqrt((($temp_start - $start_time)*($temp_start - $start_time)) + (($temp_end - $end_time)*($temp_end - $end_time)));
        if ($min_dist == -1 || $dist < $min_dist) {
            $min_dist = $dist;
        }
    }

    return $min_dist;
}

function distToClosestExpertCombinedEvents($video_id, $event_id, $start_time, $end_time) {
    $query = "SELECT (TIME_TO_SEC(obs.start_time) - TIME_TO_SEC(vid.start_time)) AS start_time, (TIME_TO_SEC(obs.end_time) - TIME_TO_SEC(vid.start_time)) AS end_time FROM timed_observations AS obs JOIN video_2 AS vid ON vid.id = video_id WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND obs.start_time > 0 AND obs.start_time < obs.end_time";
    $result = query_wildlife_video_db($query);
    $start_times = array();
    $end_times = array();

    while ($row = $result->fetch_assoc()) {
        $start_times[] = $row['start_time'];
        $end_times[] = $row['end_time'];
    }

    $min_dist = -1;
    foreach ($start_times as $temp_start) {
        foreach ($end_times as $temp_end) {
            $dist = sqrt((($temp_start - $start_time)*($temp_start - $start_time)) + (($temp_end - $end_time)*($temp_end - $end_time)));
            if ($min_dist == -1 || $dist < $min_dist) {
                $min_dist = $dist;
            }
        }
    }
    return $min_dist;
}

while ($row = $result->fetch_assoc()) {
    $name_query = "SELECT name FROM user WHERE id = " . $row['user_id'];
    $name_result = query_boinc_db($name_query);
    $name_row = $name_result->fetch_assoc();
    $name = $name_row['name'];

    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
    $type_id = $row['event_id'];
    $type_name = $row['type_name'];
    if ($check_seg) {
        $distance = distToClosestExpertCombinedEvents($video_id, $type_id, $start_time, $end_time);
    } else {
        $distance = distToClosestExpertEvent($video_id, $type_id, $start_time, $end_time);
    }
    if ($distance < 0) {
        $distance = $video_duration * 1.41421356237;
    }
    $value = ($end_time-$start_time)/$video_duration;
    echo "[";
    echo "''";
    echo ",";
    echo $start_time/$video_duration;
    echo ",";
    echo $end_time/$video_duration;
    echo ",";
    echo "'$type_name'";
    echo ",";
    echo $distance/($video_duration*1.41421356237) * 100; //Divide by hypotenuse of a square
    echo "],";
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Time Interval Plane',
                hAxis: {title: 'Start Time'},
                vAxis: {title: 'End Time'}
            };

            var chart = new google.visualization.BubbleChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Video Time Plane</h1>

            <div id='chart_div' style='margin: auto; width: auto; height: 500px;'></div>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error in either direction allowed for two events to be matched. The default value is 5.</dd>

                <dt>check_seg=</dt>
                <dd>Boolean value (0 or 1) to check for segmented events.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This scatterplot shows events plotted with their distance from a matching expert event as size. This means large dots indicate a possible incorrect user event. Color indicates event type and can show which event types users have a difficult time classifying.</p>

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
