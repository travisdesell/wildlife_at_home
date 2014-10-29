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

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 5;
}

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$type_query = "SELECT id, name FROM observation_types";
$type_result = attempt_query_with_ping($type_query, $wildlife_db);
if (!$type_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $type_query\n");
    die("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $type_query\n");
}

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
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Event Type');
            data.addColumn('number', 'Percent Correct');
            data.addColumn({type: 'string', role: 'tooltip'});
            data.addRows([
";

function getExpertMatch($db, $timed_id, $buffer) {
    $event_query = "SELECT event_id, video_id, to_seconds(start_time) AS start_sec, to_seconds(end_time) AS end_sec FROM timed_observations WHERE id = $timed_id";
    $event_result = attempt_query_with_ping($event_query, $db);
    if (!$event_result) {
        error_log("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "/nquery: $event_query\n");
        die("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "/nquery: $event_query\n");
    }
    $num_events = mysql_num_rows($event_result);
    $num_match_events = 0;

    if ($num_events > 0) {
        while ($event_row = mysql_fetch_assoc($event_result)) {
            $event_id = $event_row['event_id'];
            $video_id = $event_row['video_id'];
            $start_sec = $event_row['start_sec'];
            $end_sec = $event_row['end_sec'];

            $start_sec_top = $start_sec - $buffer;
            $start_sec_bot = $start_sec + $buffer;
            $end_sec_top = $end_sec - $buffer;
            $end_sec_bot = $end_sec + $buffer;
            $match_query = "SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND event_id = $event_id AND to_seconds(start_time) BETWEEN $start_sec_top AND $start_sec_bot AND to_seconds(end_time) BETWEEN $end_sec_top AND $end_sec_bot";
            $match_result = attempt_query_with_ping($match_query, $db);
            if (!$match_result) {
                error_log("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "/nquery: $match_query\n");
                die("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "/nquery: $match_query\n");
            }
            $num_matches = mysql_num_rows($match_result);

            if ($num_matches >= 1) {
                $num_match_events += 1;
            }
        }

        return $num_match_events / $num_events;
    } else {
        return 0;
    }
}

while ($type_row = mysql_fetch_assoc($type_result)) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];
    $timed_query = "SELECT id FROM timed_observations AS t WHERE expert = 0 AND event_id = $type_id AND start_time > 0 AND end_time > start_time AND EXISTS (SELECT * FROM timed_observations AS i WHERE t.video_id = i.video_id AND i.expert = 1 AND i.start_time > 0 AND i.end_time > i.start_time)";
    $timed_result = attempt_query_with_ping($timed_query, $wildlife_db);
    if (!$timed_result) {
        error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $timed_query\n");
        die("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $timed_query\n");
    }
    $num_events = mysql_num_rows($timed_result);
    $num_match_events = 0;
    while ($timed_row = mysql_fetch_assoc($timed_result)) {
        $num_match_events += getExpertMatch($wildlife_db, $timed_row['id'], $buffer);
    }

    if ($num_match_events > 0) {
        echo "[";
        echo "'$type_name'";
        echo ",";
        echo $num_match_events / $num_events;
        echo ",";
        echo "'$num_match_events / $num_events'";
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Percent of correct events for each type',
                vAxis: {
                    title: 'Percent Correct',
                    maxValue: 1.0,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Correctness Test</h1>

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
