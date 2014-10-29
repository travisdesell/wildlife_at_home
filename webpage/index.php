<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/news.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/csg_uotd.php");

print_header("Wildlife@Home", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
";

$carousel_info['items'][] = array(
    'active' => 'true',
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_least_tern_resized.png',
    'text' => "<h4>Welcome to Wildlife@Home</h4><p>Wildlife@Home is a joint effort from <a href='http://und.edu'>University of North Dakota</a>'s <a href='http://www.cs.und.edu/'>Department of Computer Science</a> and <a href='http://www.und.edu/dept/biology/biology_main.htm'>Department of Biology</a>, aimed at analyzing video gathered from various cameras recording wildlife.  Currently the project is looking at video of <a href='sharptailed_grouse_info.php'>sharp-tailed grouse</a>, <i>Tympanuchus phasianellus</i> to examine their nesting habits and ecology. The nest cameras have been set up up both near western North Dakota's oil fields and also within protected state lands. We recently have also begun studying two federally protected species, interior least terns, <i>Sternula antillarum</i>, and piping plovers, <i>Charadruis melodus</i>. </p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/thumbnail_sharptailed_grouse_resized.png',
    'text' => "<h4>Wildlife@Home Goals</h4>
                    <p>We hope that your participation will help us determine the impact of the oil development on the sharp-tailed grouse, and better understand the behaviors of least terns and piping plovers to aid in their conservation, as well as provide some interesting video for everyone to watch and discuss.");


$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/HenCapture2_small.png',
    'text' => "<p>Each spring we capture hens on the lek or dancing grounds where males display for females.  We fit hens with a necklace-style radio transmitter that emits a unique radio frequency which we can use to relocate the bird and find her nest.</p>");


$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/RadioTracking1_small.png',
    'text' => "<p>We track grouse using radio telemetry receivers and various types of antennas such as this handheld antenna and receiver.  Photo courtesy of Chris Felege.</p>");


$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/Truck_small.png',
    'text' => "<p>We track grouse using radio telemetry receivers and various type so antennas such as this truck mounted antenna.  Photo courtesy of Anna Mattson.</p>");
 

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/camera_system_1.png',
    'text' => "<p>Once we find a nest, we install a miniature surveillance camera near the nest.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/camera_system_2.png',
    'text' => "<p>Nest cameras are powered by cable, from about 75 feet away with a 12 volt battery and a waterproof box housing a DVR that will record to SD cards.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/grouse_nest.png',
    'text' => "<p>A sharp-tailed grouse nest.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/hen_on_nest.png',
    'text' => "<p>Hens will incubate a nest for about 23 days unless a predator finds and destroys the nest.  This is a snapshot from our nest cameras of hen incubating her nest.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/hatch.png',
    'text' => "<p>Sharp-tailed grouse chicks hatching.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/hawk2_resized.png',
    'text' => "<p>Various predators such as this Swainson’s Hawk will attack the hen while she is sitting on the nest.  This image is from one of our nest cameras.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/badger1_small.png',
    'text' => "<p>Ground predators, such as this American Badger, will destroy the nest.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/chick_capture.png',
    'text' => "<p>After the peak of hatch, we follow hens with broods via radio signals.  Once the chicks are about a month old, we will relocate the brood and catch them in big net during the night.  Chicks are then fitted with their own necklace-style radio transmitters so we monitor their survival and reproduction over the next year.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/lightning.png',
    'text' => "<p>This image found in the collected video shows a lightning crash behind a piping plover incubating a nest on the Missouri River in North Dakota.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/plover_chick.png',
    'text' => "<p>This image found in the collected video shows a piping plover chick walking across the video screen with an adult brooding the remaining chicks on the nest in the background.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/plover_in_flight.png',
    'text' => "<p>The leg bands used by wildlife biologists for bird identification are clearly visible on the flying adult piping plover.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/tern_chick.png',
    'text' => "<p>A tern chick yawns in front of the camera while the adult tern incubates the remaining egg on the nest. This image was found in the collected video.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/tern_nest_exchange.png',
    'text' => "<p>Interior least terns exhibit biparental investment in their nests. This image found in the collected video shows a nest exchange event between two flying tern parents.</p>");

$carousel_info['items'][] = array(
    'image' => 'http://volunteer.cs.und.edu/wildlife/images/tern_feeding_tern.png',
    'text' => "<p>An adult tern feeds a fish to a tern incubating a nest. This image was found in collected video.</p>");



$projects_template = file_get_contents($cwd[__FILE__] . "/../../citizen_science_grid/templates/carousel.html");

$m = new Mustache_Engine;
echo $m->render($projects_template, $carousel_info);


echo "
            <div class='btn-group btn-group-justified' style='margin-top:20px;'>
                <a class='btn btn-primary' role='button' href='../csg/instructions.php'><h4>Volunteer Your Computer</h4></a>
                <a class='btn btn-primary' role='button' href='../wildlife/video_selector.php'><h4>Watch Wildlife Videos</h4></a>
            </div>

            </div> <!-- col-sm-12 -->
        </div> <!-- row -->

        <div class='row'>
            <div class='col-sm-6'>";

show_uotd(2, 10, "style='margin-top:20px;'", false);
csg_show_news();

echo "
            </div> <!-- col-sm-6 -->

            <div class='col-sm-6'>";

include $cwd[__FILE__] . "/templates/wildlife_info.html";

echo "
            </div> <!-- col-sm-6 -->
        </div> <!-- row -->
    </div> <!-- /container -->";


print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";

?>
