<?php
require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

require_once('/projects/wildlife/html/inc/cache.inc');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
        <title>Wildlife@Home: Video Selection</title>

        <link rel='alternate' type='application/rss+xml' title='Wildlife@Home RSS 2.0' href='http://volunteer.cs.und.edu/wildlife/rss_main.php'>
        <link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
        <link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>

        $bootstrap_scripts

        <script type='text/javascript'>
";

function get_count($table_name, $where_clause, $db) {
    $query = "SELECT count(*) FROM $table_name WHERE $where_clause";
    $results = attempt_query_with_ping($query, $db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($results);

    return $row['count(*)'];
}
/**
 *  Getting the number of videos available is slow, so caching it is the way to go
 */
ini_set("mysql.connect_timeout", 300);
ini_set("default_socket_timeout", 300);

$wildlife_db = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
mysql_select_db("wildlife_video", $wildlife_db);

function get_video_progress(&$validated, &$available, &$total, $query, $db) {
    $results = attempt_query_with_ping($query, $db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($results);
    $validated = $row['validated_video_s'];
    $available = $row['available_video_s'];
    $total = $row['total_video_s'];
}
/**
 *  Get the progress of the videos for each species at each site.
 */

get_video_progress($grouse_belden_validated_s, $grouse_belden_processed_s, $grouse_belden_total_s, "SELECT validated_video_s, available_video_s, total_video_s FROM progress WHERE species_id = 1 and location_id = 1", $wildlife_db);
get_video_progress($grouse_blaisdell_validated_s, $grouse_blaisdell_processed_s, $grouse_blaisdell_total_s, "SELECT validated_video_s, available_video_s, total_video_s FROM progress WHERE species_id = 1 and location_id = 2", $wildlife_db);
get_video_progress($grouse_lostwood_validated_s, $grouse_lostwood_processed_s, $grouse_lostwood_total_s, "SELECT validated_video_s, available_video_s, total_video_s FROM progress WHERE species_id = 1 and location_id = 3", $wildlife_db);
get_video_progress($least_tern_validated_s, $least_tern_processed_s, $least_tern_total_s, "SELECT validated_video_s, available_video_s, total_video_s FROM progress WHERE species_id = 2 and location_id = 4", $wildlife_db);
get_video_progress($piping_plover_validated_s, $piping_plover_processed_s, $piping_plover_total_s, "SELECT validated_video_s, available_video_s, total_video_s FROM progress WHERE species_id = 3 and location_id = 4", $wildlife_db);

$grouse_belden_available = 100 * ($grouse_belden_processed_s / $grouse_belden_total_s);
$grouse_belden_validated = 100 * ($grouse_belden_validated_s / $grouse_belden_total_s);
$grouse_blaisdell_available = 100 * ($grouse_blaisdell_processed_s / $grouse_blaisdell_total_s);
$grouse_blaisdell_validated = 100 * ($grouse_blaisdell_validated_s / $grouse_blaisdell_total_s);
$grouse_lostwood_available = 100 * ($grouse_lostwood_processed_s / $grouse_lostwood_total_s);
$grouse_lostwood_validated = 100 * ($grouse_lostwood_validated_s / $grouse_lostwood_total_s);
$least_tern_available = 100 * ($least_tern_processed_s / $least_tern_total_s);
$least_tern_validated = 100 * ($least_tern_validated_s / $least_tern_total_s);
$piping_plover_available = 100 * ($piping_plover_processed_s / $piping_plover_total_s);
$piping_plover_validated = 100 * ($piping_plover_validated_s / $piping_plover_total_s);

echo "var grouse_belden_total = $grouse_belden_total_s;\n";
echo "var grouse_belden_processed = $grouse_belden_processed_s;\n";
echo "var grouse_belden_validated = $grouse_belden_validated_s;\n";

echo "var grouse_blaisdell_total = $grouse_blaisdell_total_s;\n";
echo "var grouse_blaisdell_processed = $grouse_blaisdell_processed_s;\n";
echo "var grouse_blaisdell_validated = $grouse_blaisdell_validated_s;\n";

echo "var grouse_lostwood_total = $grouse_lostwood_total_s;\n";
echo "var grouse_lostwood_processed = $grouse_lostwood_processed_s;\n";
echo "var grouse_lostwood_validated = $grouse_lostwood_validated_s;\n";

echo "var least_tern_total = $least_tern_total_s;\n";
echo "var least_tern_processed = $least_tern_processed_s;\n";
echo "var least_tern_validated = $least_tern_validated_s;\n";

echo "var piping_plover_total = $piping_plover_total_s;\n";
echo "var piping_plover_processed = $piping_plover_processed_s;\n";
echo "var piping_plover_validated = $piping_plover_validated_s;\n";

echo "</script>

        <script src='video_selector.js'></script>
";



echo "
    <style>
    body {
        padding-top: 60px;
    }
    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
    }
    </style>

</head>
<body>";

$active_items = array(
                    'home' => '',
                    'watch_video' => 'active',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);


echo "
    <div class='well well-small'>
        <div class='container'>
            <div class='row-fluid'>
                <div class='span12'>
                <p>Select the species and site you want to watch video for, and click the watch video button to get started. You will have to <a href='create_account_form.php'>create an account</a> first if you do not have one. Please take a look at the training videos for each species first, because telling if the bird is at its nest or not can be challenging! You can also click the progress bars to see how much video is available and how much has been watched already. There is also a list of who has watched the most video <a href='http://volunteer.cs.und.edu/wildlife/top_bossa_users.php'>here</a>. 
                </div>
            </div>
        </div>
    </div>
";



$thumbnails = array('thumbnail_list' => array(
                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_sharptailed_grouse.png',
                            'species_name' => 'Sharp-Tailed Grouse',
                            'species_id' => '1',
                            'training_webpage' => 'http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_training.php',
                            'info_webpage' => 'http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_info.php',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => '<p>Species description...</p>',
                            'site' => array(
                                array (
                                    'enabled' => ($grouse_belden_available > 0),
                                    'site_name' => 'Belden, ND',
                                    'progress_id' => 'grouse_belden_progress',
                                    'site_description' => 'Site description...',
                                    'site_id' => '1',
                                    'validated_percentage' => $grouse_belden_validated,
                                    'available_percentage' => $grouse_belden_available - $grouse_belden_validated
                                ), 
                                array (
                                    'enabled' => ($grouse_blaisdell_available > 0),
                                    'site_name' => 'Blaisdell, ND',
                                    'progress_id' => 'grouse_blaisdell_progress',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'validated_percentage' => $grouse_blaisdell_validated,
                                    'available_percentage' => $grouse_blaisdell_available - $grouse_blaisdell_validated
                                ), 
                                array (
                                    'enabled' => ($grouse_lostwood_available > 0),
                                    'site_name' => 'Lostwood Wildlife Refuge, ND',
                                    'progress_id' => 'grouse_lostwood_progress',
                                    'site_description' => 'Site description...',
                                    'site_id' => '3',
                                    'validated_percentage' => $grouse_lostwood_validated,
                                    'available_percentage' => $grouse_lostwood_available - $grouse_lostwood_validated
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_least_tern.png',
                            'species_name' => 'Interior Least Tern',
                            'species_id' => '2',
                            'species_latin_name' => 'Sternula antillarum',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'enabled' => ($least_tern_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'progress_id' => 'least_tern_progress',
                                    'site_description' => 'Site description...',
                                    'site_id' => '4',
                                    'validated_percentage' => $least_tern_validated,
                                    'available_percentage' => $least_tern_available - $least_tern_validated
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_piping_plover.png',
                            'species_name' => 'Piping Plover',
                            'species_id' => '3',
                            'species_latin_name' => 'Charadrius melodus',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'enabled' => ($piping_plover_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'progress_id' => 'piping_plover_progress',
                                    'site_description' => 'Site description...',
                                    'site_id' => '4',
                                    'validated_percentage' => $piping_plover_validated,
                                    'available_percentage' => $piping_plover_available - $piping_plover_validated
                                )
                            )
                        )
                    )
                );

$projects_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/projects_template.html");

$m = new Mustache_Engine;
echo $m->render($projects_template, $thumbnails);

print_footer();

echo "
</body>
</html>
";

?>
