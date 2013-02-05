<?php
require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');

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
    </script>

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
                            'species_id' => '0',
                            'species_latin_name' => 'Tympanuchus phasianellus',
                            'project_description' => '<p>Species description...</p> <p><a href=\'http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_info.php\'>Learn more about the sharptailed grouse.</a></p>',
                            'site' => array(
                                array (
                                    'site_name' => 'Belden, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '0',
                                    'progress_percentage' => '50'
                                ), 
                                array (
                                    'site_name' => 'Blaisdell, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '1',
                                    'progress_percentage' => '10'
                                ), 
                                array (
                                    'site_name' => 'Lostwood Wildlife Refuge, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '30'
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_least_tern.png',
                            'species_name' => 'Interior Least Tern',
                            'species_id' => '1',
                            'species_latin_name' => 'Sternula antillarum',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'site_name' => 'Missouri River, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '0'
                                )
                            )
                        ),

                        array(
                            'thumbnail_image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_piping_plover.png',
                            'species_name' => 'Piping Plover',
                            'species_id' => '2',
                            'species_latin_name' => 'Charadrius melodus',
                            'project_description' => 'Species description...',
                            'site' => array(
                                array (
                                    'site_name' => 'Missouri River, ND',
                                    'site_description' => 'Site description...',
                                    'site_id' => '2',
                                    'progress_percentage' => '0'
                                )
                            )
                        )
                    )
                );

$thumbnail_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/thumbnail_template.html");

$m = new Mustache_Engine;
echo $m->render($thumbnail_template, $thumbnails);


echo "
    <!-- Footer
    ================================================== -->
    <footer class='footer'>
        <div class='container'>
            <center>
            <p>Designed by <a href='http://people.cs.und.edu'>Travis Desell</a> with much help from <a href='http://twitter.github.com/bootstrap/getting-started.html'>Twitter's Bootstrap</a>.</p>
            <p>&copy; Travis Desell, Susan Ellis-Felege and the University of North Dakota 2013</p>
            </center>
        </div>
    </footer>
    ";


echo "
</body>
</html>
";

?>
