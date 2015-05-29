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

print_header("Wildlife@Home: Computer Accuracy with Consensus", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//echo "Header:";

ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

// Get Parameters
parse_str($_SERVER['QUERY_STRING']);

// Set buffer for correctness time (+ or - the buffer value)
if (!isset($buffer)) {
    $buffer = 30;
}

if (!isset($sample)) {
    $sample = "experts"; // or "everyone" or "users"
}

if (!isset($species)) {
    $species = 1;
}

if (!isset($beta)) {
    $beta = FALSE;
} else {
    $beta = TRUE;
}

$type_query = "SELECT id, name FROM observation_types";
$type_result = query_wildlife_video_db($type_query, $wildlife_db);

$species_query = "SELECT id, name FROM species";
$species_result = query_wildlife_video_db($species_query, $wildlife_db);

$algorithm_query = "SELECT id, name FROM event_algorithms";
$algorithm_result = query_wildlife_video_db($algorithm_query, $wildlife_db);

/*
$species = array();
while ($species_row = $species_result->fetch_assoc()) {
    $species_id = $species_row['id'];
    $species_name = $species_row['name'];
    $species[$species_id] = $species_name;
}
ksort($species);
*/

$algs = array();
while ($alg_row = $algorithm_result->fetch_assoc()) {
    $alg_id = $alg_row['id'];
    $alg_name = $alg_row['name'];
    $algs[$alg_id] = $alg_name;
}
ksort($algs);

echo "
<div class='containder'>
    <div class='row'>
    <div class='col-sm-12'>
    <script type = 'text/javascript' src='js/data_download.js'></script>
    <script type = 'text/javascript' src='https://www.google.com/jsapi'></script>
    <script type = 'text/javascript'>
        google.load('visualization', '1.1', {packages:['corechart']});
        google.setOnLoadCallback(drawChart);

        var data;

        function downloadChart() {
            var csv_data = dataTableToCSV(data);
            downloadCSV(csv_data);
        }

        function drawChart() {
            var container = document.getElementById('chart_div');
            data = new google.visualization.DataTable();
            data.addColumn('string', 'Event Type');
            data.addColumn('number', 'Any Algorithm');
            data.addColumn('number', 'All Algorithms');
            data.addColumn('number', 'AccAvg & ViBe Algorithms');
            data.addColumn('number', 'AccAvg & PBAS Algorithms');
            data.addColumn('number', 'ViBe & PBAS Algorithms');
";

echo "
            data.addRows([
";

while ($type_row = $type_result->fetch_assoc()) {
    $type_id = $type_row['id'];
    $type_name = $type_row['name'];

    $timed_query = "SELECT id FROM timed_observations AS t WHERE ";
    if ($sample == "everyone") {
        // Don't add anything to the query here.
    } elseif ($sample == "experts") {
        $timed_query = $timed_query . "expert = 1 AND ";
    } elseif ($sample == "users") {
        $timed_query = $timed_query . "expert = 0 AND ";
    }
    $timed_query = $timed_query . "event_id = $type_id AND species_id <> $species AND start_time_s > 10 AND start_time_s <= end_time_s AND EXISTS (SELECT * FROM computed_events AS comp INNER JOIN event_algorithms AS alg ON alg.id = comp.algorithm_id AND comp.version_id = ";
    if ($beta) {
        $timed_query = $timed_query . "alg.beta_version_id ";
    } else {
        $timed_query = $timed_query . "alg.main_version_id ";
    }
    $timed_query = $timed_query . "WHERE comp.video_id = t.video_id)";

    $timed_result = query_wildlife_video_db($timed_query);

    $consensus_num = 0;
    $consensus_any_matches = 0;
    $consensus_all_matches = 0;
    $consensus_avg_vibe_matches = 0;
    $consensus_avg_pbas_matches = 0;
    $consensus_vibe_pbas_matches = 0;

    while ($timed_row = $timed_result->fetch_assoc()) {
        $obs_id = $timed_row['id'];

        $any_match_start = FALSE;
        $any_match_end = FALSE;
        $avg_match_start = FALSE;
        $avg_match_end = FALSE;
        $vibe_match_start = FALSE;
        $vibe_match_end = FALSE;
        $pbas_match_start = FALSE;
        $pbas_match_end = FALSE;
        $all_match_start = 0;
        $all_match_end = 0;
        foreach($algs as $a_id => $a_name) {
            list($start_match, $end_match) = getBufferAccuracy($obs_id, $a_id, $buffer $beta);

            if($start_match) {
                $any_match_start = TRUE;
                $all_match_start += 1;
                if ($a_id == '4') {
                    $avg_match_start = TRUE;
                } elseif ($a_id == '2') {
                    $vibe_match_start = TRUE;
                } elseif ($a_id == '3') {
                    $pbas_match_start = TRUE;
                }
            }
            if($end_match) {
                $any_match_end = TRUE;
                $all_match_end += 1;
                if ($a_id == '4') {
                    $avg_match_end = TRUE;
                } elseif ($a_id == '2') {
                    $vibe_match_end = TRUE;
                } elseif ($a_id == '3') {
                    $pbas_match_end = TRUE;
                }
            }
        }
        $consensus_num += 2;
        if ($any_match_start) {
            $consensus_any_matches += 1;
        }
        if ($any_match_end) {
            $consensus_any_matches += 1;
        }

        if($all_match_start == count($algs)) {
            $consensus_all_matches += 1;
        }
        if($all_match_end == count($algs)) {
            $consensus_all_matches += 1;
        }

        if ($avg_match_start AND $vibe_match_start) {
            $consensus_avg_vibe_matches += 1;
        }
        if ($avg_match_end AND $vibe_match_end) {
            $consensus_avg_vibe_matches += 1;
        }

        if ($avg_match_start AND $pbas_match_start) {
            $consensus_avg_pbas_matches += 1;
        }
        if ($avg_match_end AND $pbas_match_end) {
            $consensus_avg_pbas_matches += 1;
        }

        if ($vibe_match_start AND $pbas_match_start) {
            $consensus_vibe_pbas_matches += 1;
        }
        if ($vibe_match_end AND $pbas_match_end) {
            $consensus_vibe_pbas_matches += 1;
        }
    }

    if ($consensus_num > 0) {
        echo "[";
        echo "'$type_name'";
        echo ",";
        echo "$consensus_any_matches / $consensus_num * 100";
        echo ",";
        echo "$consensus_all_matches / $consensus_num * 100";
        echo ",";
        echo "$consensus_avg_vibe_matches / $consensus_num * 100";
        echo ",";
        echo "$consensus_avg_pbas_matches / $consensus_num * 100";
        echo ",";
        echo "$consensus_vibe_pbas_matches / $consensus_num * 100";
        echo "],";
    }
}

echo "
                ]);

";
echo "
            var options = {
                title: 'Computer accuracy with consensus vs experts on tern and plover nests',
                hAxis: {title: 'Event Type'},
                vAxis: {
                    title: 'Accuracy',
                    maxValue: 100,
                    minValue: 0,
                }
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

            chart.draw(data, options);
        }
    </script>

            <h1>Computer Accuracy with Consensus vs Experts on Tern and Plover Nests</h1>

            <div id='chart_div' style='margin: auto; width: 90%; height: 500px;'></div>

            <button onclick='downloadChart()'>Download as CSV</button>

            <h2>Parameters: (portion of the URL after a '?')</h2>
            <dl>
                <dt>buffer=</dt>
                <dd>The error (seconds) in either direction allowed for two events to be matched. The default value is 30.</dd>
            </dl>
            

            <h2>Description:</h2>
            <p>This bar chart shows the percentage of expert observations that have a matching computed event for each event type and algorithm type.</p>

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
