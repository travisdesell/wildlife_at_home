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

print_header("Wildlife@Home: Expert Event Conflicts Table", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$conflict_map = array(
    // Not In Video
    4 => array(1, 2, 3, 5, 6, 7, 8, 15, 18, 41),

    // Standing
    5 => array(6, 7, 8),

    // Flying
    6 => array(5, 7, 8, 41),

    // Walkiing
    7 => array(5, 6, 8, 41),

    // Sitting
    8 => array(5, 6, 7),

    // In Video
    18 => array(4),

    // On Nest
    41 => array(4, 6, 7, 42),

    // Off Nest
    42 => array(41),
);

// Get Parameters
//parse_str($_SERVER['QUERY_STRING']);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

$event_query = "SELECT vid.animal_id, obs.video_id, obs.event_id, e.name AS event_name, obs.start_time, obs.end_time, to_seconds(obs.start_time) AS start_sec, to_seconds(obs.end_time) AS end_sec FROM timed_observations AS obs JOIN observation_types AS e ON e.id = event_id JOIN video_2 AS vid ON vid.id = video_id WHERE obs.expert = 1 AND obs.start_time > 0 AND obs.end_time > obs.start_time";
$event_result = attempt_query_with_ping($event_query, $wildlife_db);
if (!$event_result) {
    error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $event_query\n");
    die("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $event_query\n");
}

echo "
<div class='containder'>
    <div class='row'>
        <div class='col-sm-12'>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1', {packages:['table']});
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
            var chart = new google.visualization.Table(container);
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Animal ID');
            data.addColumn('string', 'Video ID');
            data.addColumn('string', 'Event Type');
            data.addColumn('date', 'Start');
            data.addColumn('date', 'End');
            data.addRows([
";

while ($event_row = mysql_fetch_assoc($event_result)) {
    $animal_id = $event_row['animal_id'];
    $video_id = $event_row['video_id'];
    $event_id = $event_row['event_id'];
    $start_sec = $event_row['start_sec'];
    $end_sec = $event_row['end_sec'];

    $conflicts = join(',', $conflict_map[$event_id]);
    if (!empty($conflicts)) {
        $match_query = "SELECT * FROM timed_observations WHERE expert = 1 AND video_id = $video_id AND event_id IN ($conflicts) AND (to_seconds(start_time) BETWEEN $start_sec AND $end_sec OR to_seconds(end_time) BETWEEN $start_sec AND $end_sec)";
        $match_result = attempt_query_with_ping($match_query, $wildlife_db);
        if (!$match_result) {
            error_log("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $match_query\n");
            die("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "/nquery: $match_query\n");
        }
        $num_matches = mysql_num_rows($match_result);
        if ($num_matches >= 1) {
            echo "['" . $animal_id . "'";
            echo ",'" . $video_id . "'";
            echo ",'" . $event_row['event_name'] . "'";
            echo ", getDate('" . $event_row['start_time'] . "')";
            echo ", getDate('" . $event_row['end_time'] . "')";
            echo "],";
        }
    }
}

echo "
                ]);

";
echo "
            var options = {
                showRowNumber: true
            };

            chart.draw(data, options);
        }
    </script>

            <h1>Expert Event Conflicts Table</h1>
            <p>Tuples can be sorted by clicking on column headers. Also, fixed events should be removed if this page is refreshed.</p>
            <p><b>NOTE:</b> All rows in this table are duplicated since both pairs of conflicting events are reported.</p>

            <div id='chart_div' style='margin: auto; width: auto; height: auto;'></div>

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
