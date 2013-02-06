<?php
require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');

require_once('/projects/wildlife/html/inc/cache.inc');

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

echo "
<!DOCTYPE html>
<html>
<head>
        <meta charset='utf-8'>
        <title>Wildlife@Home: Video Selection</title>

        <link rel='alternate' type='application/rss+xml' title='Wildlife@Home RSS 2.0' href='http://volunteer.cs.und.edu/wildlife/rss_main.php'>
        <link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
        <link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>

        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta name='description' content=''>
        <meta name='author' content=''>

        <!-- Le styles -->
        <link href='assets/css/bootstrap.css' rel='stylesheet'>
        <link href='assets/css/bootstrap-responsive.css' rel='stylesheet'>

        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src='http://html5shim.googlecode.com/svn/trunk/html5.js'></script>
        <![endif]-->

        <!-- Fav and touch icons -->
        <link rel='apple-touch-icon-precomposed' sizes='144x144' href='assets/ico/apple-touch-icon-144-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' sizes='114x114' href='assets/ico/apple-touch-icon-114-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' sizes='72x72' href='assets/ico/apple-touch-icon-72-precomposed.png'>
        <link rel='apple-touch-icon-precomposed' href='assets/ico/apple-touch-icon-57-precomposed.png'>
        <link rel='shortcut icon' href='assets/ico/favicon.png'>

        <!-- Le javascript
        ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
        <script src='assets/js/jquery.js'></script>
        <script src='assets/js/bootstrap-transition.js'></script>
        <script src='assets/js/bootstrap-alert.js'></script>
        <script src='assets/js/bootstrap-modal.js'></script>
        <script src='assets/js/bootstrap-dropdown.js'></script>
        <script src='assets/js/bootstrap-scrollspy.js'></script>
        <script src='assets/js/bootstrap-tab.js'></script>
        <script src='assets/js/bootstrap-tooltip.js'></script>
        <script src='assets/js/bootstrap-popover.js'></script>
        <script src='assets/js/bootstrap-button.js'></script>
        <script src='assets/js/bootstrap-collapse.js'></script>
        <script src='assets/js/bootstrap-carousel.js'></script>
        <script src='assets/js/bootstrap-typeahead.js'></script>

        <script type='text/javascript'>
";

function get_count($table_name, $where_clause, $db) {
    $query = "SELECT count(*) FROM $table_name WHERE $where_clause";
    $results = mysql_query($query, $db);
    if (!$results) die ("MYSQL Error (" . mysql_errno($db) . "): " . mysql_error($db) . "\nquery: $query\n");

    $row = mysql_fetch_assoc($results);

    return $row['count(*)'];
}
/**
 *  Getting the number of videos available is slow, so caching it is the way to go
 */

$grouse_belden_total_videos = 0;
$grouse_belden_processed_videos = 0;
$grouse_belden_validated_videos = 0;

$grouse_blaisdell_total_videos = 0;
$grouse_blaisdell_processed_videos = 0;
$grouse_blaisdell_validated_videos = 0;

$grouse_lostwood_total_videos = 0;
$grouse_lostwood_processed_videos = 0;
$grouse_lostwood_validated_videos = 0;

$least_tern_total_videos = 0;
$least_tern_processed_videos = 0;
$least_tern_validated_videos = 0;

$piping_plover_total_videos = 0;
$piping_plover_processed_videos = 0;
$piping_plover_validated_videos = 0;

$cache_args = "video_counts";
$cached_data = get_cached_data(600 /*10 minute long cache*/, $cache_args);

if ($cached_data) { //counts were in the cache, use them
    $data = unserialize($cached_data);

    $grouse_belden_total_videos = $data->grouse_belden_total_videos;
    $grouse_belden_processed_videos = $data->grouse_belden_processed_videos;
    $grouse_belden_validated_videos = $data->grouse_belden_validated_videos;

    $grouse_blaisdell_total_videos = $data->grouse_blaisdell_total_videos;
    $grouse_blaisdell_processed_videos = $data->grouse_blaisdell_processed_videos;
    $grouse_blaisdell_validated_videos = $data->grouse_blaisdell_validated_videos;

    $grouse_lostwood_total_videos = $data->grouse_lostwood_total_videos;
    $grouse_lostwood_processed_videos = $data->grouse_lostwood_processed_videos;
    $grouse_lostwood_validated_videos = $data->grouse_lostwood_validated_videos;

    $least_tern_total_videos = $data->least_tern_total_videos;
    $least_tern_processed_videos = $data->least_tern_processed_videos;
    $least_tern_validated_videos = $data->least_tern_validated_videos;

    $piping_plover_total_videos = $data->piping_plover_total_videos;
    $piping_plover_processed_videos = $data->piping_plover_processed_videos;
    $piping_plover_validated_videos = $data->piping_plover_validated_videos;
} else { //counts were too old or not in the cache, regenerate them

    $wildlife_db = mysql_pconnect("wildlife.und.edu", $wildlife_user, $wildlife_passwd);
    mysql_select_db("wildlife_video", $wildlife_db);

    /**
     *  Get the progress of the videos for each species at each site.
     */

    $grouse_belden_total_videos = get_count("video_segment_2", "species_id = 1 and location_id = 1", $wildlife_db);
    $grouse_belden_processed_videos = get_count("video_segment_2", "processing_status = 'DONE' and species_id = 1 and location_id = 1", $wildlife_db);
    $grouse_belden_validated_videos = get_count("video_segment_2", "crowd_status = 'VALIDATED' and species_id = 1 and location_id = 1", $wildlife_db);

    $grouse_blaisdell_total_videos = get_count("video_segment_2", "species_id = 1 and location_id = 2", $wildlife_db);
    $grouse_blaisdell_processed_videos = get_count("video_segment_2", "processing_status = 'DONE' and species_id = 1 and location_id = 2", $wildlife_db);
    $grouse_blaisdell_validated_videos = get_count("video_segment_2", "crowd_status = 'VALIDATED' and species_id = 1 and location_id = 2", $wildlife_db);

    $grouse_lostwood_total_videos = get_count("video_segment_2", "species_id = 1 and location_id = 3", $wildlife_db);
    $grouse_lostwood_processed_videos = get_count("video_segment_2", "processing_status = 'DONE' and species_id = 1 and location_id = 3", $wildlife_db);
    $grouse_lostwood_validated_videos = get_count("video_segment_2", "crowd_status = 'VALIDATED' and species_id = 1 and location_id = 3", $wildlife_db);

    $least_tern_total_videos = get_count("video_segment_2", "species_id = 2 and location_id = 4", $wildlife_db);
    $least_tern_processed_videos = get_count("video_segment_2", "processing_status = 'DONE' and species_id = 2 and location_id = 4", $wildlife_db);
    $least_tern_validated_videos = get_count("video_segment_2", "crowd_status = 'VALIDATED' and species_id = 2 and location_id = 4", $wildlife_db);

    $piping_plover_total_videos = get_count("video_segment_2", "species_id = 3 and location_id = 4", $wildlife_db);
    $piping_plover_processed_videos = get_count("video_segment_2", "processing_status = 'DONE' and species_id = 3 and location_id = 4", $wildlife_db);
    $piping_plover_validated_videos = get_count("video_segment_2", "crowd_status = 'VALIDATED' and species_id = 3 and location_id = 4", $wildlife_db);

    $data->grouse_belden_total_videos = $grouse_belden_total_videos;
    $data->grouse_belden_processed_videos = $grouse_belden_processed_videos;
    $data->grouse_belden_validated_videos = $grouse_belden_validated_videos;

    $data->grouse_blaisdell_total_videos = $grouse_blaisdell_total_videos;
    $data->grouse_blaisdell_processed_videos = $grouse_blaisdell_processed_videos;
    $data->grouse_blaisdell_validated_videos = $grouse_blaisdell_validated_videos;

    $data->grouse_lostwood_total_videos = $grouse_lostwood_total_videos;
    $data->grouse_lostwood_processed_videos = $grouse_lostwood_processed_videos;
    $data->grouse_lostwood_validated_videos = $grouse_lostwood_validated_videos;

    $data->least_tern_total_videos = $least_tern_total_videos;
    $data->least_tern_processed_videos = $least_tern_processed_videos;
    $data->least_tern_validated_videos = $least_tern_validated_videos;

    $data->piping_plover_total_videos = $piping_plover_total_videos;
    $data->piping_plover_processed_videos = $piping_plover_processed_videos;
    $data->piping_plover_validated_videos = $piping_plover_validated_videos;


    set_cached_data(600 /* 10 minute cache*/, serialize($data), $cache_args);
}

$grouse_belden_available = 100 * ($grouse_belden_processed_videos / $grouse_belden_total_videos);
$grouse_belden_validated = 100 * ($grouse_belden_validated / $grouse_belden_total_videos);
$grouse_blaisdell_available = 100 * ($grouse_blaisdell_processed_videos / $grouse_blaisdell_total_videos);
$grouse_blaisdell_validated = 100 * ($grouse_blaisdell_validated / $grouse_blaisdell_total_videos);
$grouse_lostwood_available = 100 * ($grouse_lostwood_processed_videos / $grouse_lostwood_total_videos);
$grouse_lostwood_validated = 100 * ($grouse_lostwood_validated / $grouse_lostwood_total_videos);
$least_tern_available = 100 * ($least_tern_processed_videos / $least_tern_total_videos);
$least_tern_validated = 100 * ($least_tern_validated / $least_tern_total_videos);
$piping_plover_available = 100 * ($piping_plover_processed_videos / $piping_plover_total_videos);
$piping_plover_validated = 100 * ($piping_plover_validated / $piping_plover_total_videos);

echo "var grouse_belden_total = $grouse_belden_total_videos;\n";
echo "var grouse_belden_processed = $grouse_belden_processed_videos;\n";
echo "var grouse_belden_validated = $grouse_belden_validated_videos;\n";

echo "var grouse_blaisdell_total = $grouse_blaisdell_total_videos;\n";
echo "var grouse_blaisdell_processed = $grouse_blaisdell_processed_videos;\n";
echo "var grouse_blaisdell_validated = $grouse_blaisdell_validated_videos;\n";

echo "var grouse_lostwood_total = $grouse_lostwood_total_videos;\n";
echo "var grouse_lostwood_processed = $grouse_lostwood_processed_videos;\n";
echo "var grouse_lostwood_validated = $grouse_lostwood_validated_videos;\n";

echo "var least_tern_total = $least_tern_total_videos;\n";
echo "var least_tern_processed = $least_tern_processed_videos;\n";
echo "var least_tern_validated = $least_tern_validated_videos;\n";

echo "var piping_plover_total = $piping_plover_total_videos;\n";
echo "var piping_plover_processed = $piping_plover_processed_videos;\n";
echo "var piping_plover_validated = $piping_plover_validated_videos;\n";


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
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);

$thumbnails = array('thumbnail_list' => array(
                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_sharptailed_grouse.png',
                            'species_name' => 'Sharptailed Grouse',
                            'species_id' => '1',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => '<p>Species description...</p> <p><a href=\'http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_info.php\'>Learn more about the sharptailed grouse.</a></p>',
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

$thumbnail_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/thumbnail_template.html");

$m = new Mustache_Engine;
echo $m->render($thumbnail_template, $thumbnails);

print_footer();

echo "
</body>
</html>
";

?>
