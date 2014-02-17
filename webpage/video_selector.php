<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . '/navbar.php');
require_once($cwd . '/footer.php');
require_once($cwd . '/wildlife_db.php');
require_once($cwd . '/my_query.php');

require $cwd . '/../mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");

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

function get_video_progress($species_id, $location_id, &$available, &$validated) {
    global $wildlife_db;

    $results = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE location_id = $location_id AND species_id = $species_id", $wildlife_db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    $row = mysql_fetch_assoc($results);
    $total = $row['count(*)'];

    $results = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE location_id = $location_id AND species_id = $species_id AND processing_status != 'UNWATERMARKED' AND release_to_public = true", $wildlife_db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    $row = mysql_fetch_assoc($results);
    $available = $row['count(*)'];

    $results = attempt_query_with_ping("SELECT count(*) FROM video_2 WHERE location_id = $location_id AND species_id = $species_id AND crowd_status = 'VALIDATED'", $wildlife_db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($wildlife_db) . "): " . mysql_error($wildlife_db) . "\nquery: $query\n");
    $row = mysql_fetch_assoc($results);
    $validated = $row['count(*)'];

    $species = "";
    if ($species_id == 1)       $species = "grouse";
    else if ($species_id == 2)  $species = "least_tern";
    else if ($species_id == 3)  $species = "piping_plover";

    $location = "";
    if ($location_id == 1)       $location = "belden";
    else if ($location_id == 2)  $location = "blaisdell";
    else if ($location_id == 3)  $location = "lostwood";
    else if ($location_id == 4)  $location = "missouri_river";

    echo "var $species" . "_" . "$location" . "_total = " . $total . ";\n";
    echo "var $species" . "_" . "$location" . "_available = " . $available . ";\n";
    echo "var $species" . "_" . "$location" . "_validated = " . $validated . ";\n";

    $available = 100 * ($available / $total);
    $validated = 100 * ($validated / $total);
}
/**
 *  Get the progress of the videos for each species at each site.
 */
get_video_progress(1, 1, $grouse_belden_available, $grouse_belden_validated);
get_video_progress(1, 2, $grouse_blaisdell_available, $grouse_blaisdell_validated);
get_video_progress(1, 3, $grouse_lostwood_available, $grouse_lostwood_validated);
get_video_progress(2, 4, $least_tern_available, $least_tern_validated);
get_video_progress(3, 4, $piping_plover_available, $piping_plover_validated);

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
    <div class='container'>
        <div class='row-fluid'>
            <div class='span12'>
                <div class='well well-small'>
                <p>Select the species and site you want to watch video for, and click the watch video button to get started. You will have to <a href='create_account_form.php'>create an account</a> first if you do not have one. Please take a look at the training videos for each species first, because telling if the bird is at its nest or not can be challenging! You can also click the progress bars to see how much video is available and how much has been watched already. There is a list of who has watched the most video <a href='http://volunteer.cs.und.edu/wildlife/top_bossa_users.php'>here</a>, and you can go over the observations for videos you've already watched <a href='http://volunteer.cs.und.edu/wildlife/user_video_list.php'>here</a>. 
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
                            'info_webpage' => 'sharptailed_grouse_info.php',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => '<p>Sharp-tailed grouse are an important ground-nesting bird and a species that can serve as an indicator of grassland health. Cameras were placed in areas with different degrees of gas and oil development.</p> <p>Active projects include: <ul><li>Rebecca Eckroad - <a href="becca_grouse_project.php">Nest Cameras and Citizen Science: Implications for evaluating Sharp-tailed Grouse Nesting Ecology</a></li><li>Paul Burr - <a href="http://volunteer.cs.und.edu/wildlife/alpha/paul_project.php">Sharp-tailed Grouse Nest Predation Relative to Gas and Oil Development in North Dakota</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($grouse_belden_available > 0),
                                    'site_name' => 'Belden, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'grouse_belden_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in areas of intense gas and oil development.',
                                    'site_id' => '1',
                                    'validated_percentage' => $grouse_belden_validated,
                                    'available_percentage' => $grouse_belden_available - $grouse_belden_validated
                                ), 

                                array (
                                    'enabled' => ($grouse_blaisdell_available > 0),
                                    'site_name' => 'Blaisdell, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'grouse_blaisdell_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in areas of low intensity of gas and oil development.',
                                    'site_id' => '2',
                                    'validated_percentage' => $grouse_blaisdell_validated,
                                    'available_percentage' => $grouse_blaisdell_available - $grouse_blaisdell_validated
                                ), 

                                array (
                                    'enabled' => ($grouse_lostwood_available > 0),
                                    'site_name' => 'Lostwood Wildlife Refuge, ND',
                                    'year' => '2012',
                                    'progress_id' => 'grouse_lostwood_progress',
                                    'site_description' => 'Cameras were placed at grouse nests in this National Wildlife Refuge, representing a historic grassland.',
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
                            'project_description' => '<p>Interior least terns are federally listed as an endangered species. They nest on sandbars and islands along the Missouri River in western North Dakota.</p><p>Active projects include: <ul><li>Alicia Andes - <a href="http://volunteer.cs.und.edu/wildlife/alpha/alicia_project.php">NEEDS A TITLE</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($least_tern_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'least_tern_progress',
                                    'site_description' => 'Cameras were placed at least tern nests along the Missouri River in western North Dakota.',
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
                            'info_webpage' => 'piping_plover_info.php',
                            'project_description' => '<p>Northern great plains piping plovers are federally listed as threatened species. They nest on sandbars and islands along the Missouri River and Alkali lakes in North Dakota.</p><p>Active projects include: <ul><li>Alicia Andes - <a href="http://volunteer.cs.und.edu/wildlife/alpha/alicia_project.php">NEEDS A TITLE</a></li></ul></p>',
                            'site' => array(
                                array (
                                    'enabled' => ($piping_plover_available > 0),
                                    'site_name' => 'Missouri River, ND',
                                    'year' => '2012-2013',
                                    'progress_id' => 'piping_plover_progress',
                                    'site_description' => 'Cameras were placed at piping plover nests along the Missouri River in western North Dakota.',
                                    'site_id' => '4',
                                    'validated_percentage' => $piping_plover_validated,
                                    'available_percentage' => $piping_plover_available - $piping_plover_validated
                                )
                            )
                        )
                    )
                );

$projects_template = file_get_contents($cwd . "/templates/projects_template.html");

$m = new Mustache_Engine;
echo $m->render($projects_template, $thumbnails);

print_footer();

echo "
</body>
</html>
";

?>
