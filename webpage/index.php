<?php
// This file is part of BOINC.
// http://boinc.berkeley.edu
// Copyright (C) 2008 University of California
//
// BOINC is free software; you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation,
// either version 3 of the License, or (at your option) any later version.
//
// BOINC is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with BOINC.  If not, see <http://www.gnu.org/licenses/>.

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

/*
 * THIS IS REALLY BAD!
 * But the BOINC include suck and use relative paths
 */
chdir("/projects/wildlife/html/user/"); 

require_once("/projects/wildlife/html/inc/db.inc");
require_once("/projects/wildlife/html/inc/util.inc");
require_once("/projects/wildlife/html/inc/news.inc");
require_once("/projects/wildlife/html/inc/cache.inc");
require_once("/projects/wildlife/html/inc/uotd.inc");
require_once("/projects/wildlife/html/inc/sanitize_html.inc");
require_once("/projects/wildlife/html/inc/translation.inc");
require_once("/projects/wildlife/html/inc/text_transform.inc");

require_once("/projects/wildlife/html/project/project.inc");

require_once($cwd . "/navbar.php");
require_once($cwd . "/footer.php");

$caching = false;

if ($caching) {
    start_cache(INDEX_PAGE_TTL);
}

$stopped = web_stopped();
$rssname = "Wildlife@Home RSS 2.0" ;
$rsslink = "http://volunteer.cs.und.edu/wildlife/rss_main.php";

header("Content-type: text/html; charset=ISO-8859-1");

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");

echo "<html>
    <head>
        <meta charset='utf-8'>
        <title>Wildlife@Home</title>

        <link rel='alternate' type='application/rss+xml' title='Wildlife@Home RSS 2.0' href='http://volunteer.cs.und.edu/wildlife/rss_main.php'>
        <link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
        <link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>

        <style>
        hr.news_line {
            border: 0;
            border-bottom: 1px solid rgb(200, 200, 200);
        }

        td.news {
            background-color: #dff0ff;
            border-color: #add8e6;
        }

        span.news_title {
            font-weight: bold;
        }

        span.news_date {
            color: rgb(100,100,100);
            font-size: 0.9em;
            float: right;
        }

        body {
            padding-top: 60px;
        }
        @media (max-width: 979px) {
            body {
                padding-top: 0px;
            }
        }
        </style>

        $bootstrap_scripts

        <script>
          !function ($) {
            $(function(){
              // carousel demo
              $('.item').eq(Math.floor((Math.random() * $('.item').length))).addClass('active');
              $('#myCarousel').carousel({ interval: false })
            })
          }(window.jQuery)
        </script>
";

include 'schedulers.txt';
echo "
    </head><body>
";

//Let's implement the locale stuff!
$locale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

//Test line
//$locale = "de";

if($locale == "de")
{
	require("site_de.php");
}
else
{
	require("site_en.php");
}
$content = fetchIndex();

$active_items = array(
                    'home' => 'active',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => ''
                );

print_navbar($active_items);

echo "
    <div class='container'>
        <div class='row'>
            <div class='span6'>
                <h3>" . $content["welcome"] . "</h3>
				<p>" . $content["firstpara"] . "</p>
                <p>" . $content["secondpara"] . "</p>

                <div class='row-fluid'>
                    <a class='btn btn-large btn-primary span6' href='video_selector.php'>" . $content["vidbutton"] . "</a>
                    <a class='btn btn-large btn-primary span6' href='boinc_instructions.php'>" . $content["vidbutton2"] . "</a>
                </div>
            </div>

            <div class='span6'>
                <!-- Carousel
                ================================================== -->
                <div id='myCarousel' class='carousel slide'>
                  <div class='carousel-inner'>
                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/HenCapture2.JPG' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Each spring we capture hens on the lek or dancing grounds where males display for females.  We fit hens with a necklace-style radio transmitter that emits a unique radio frequency which we can use to relocate the bird and find her nest.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/RadioTracking1.JPG' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>We track grouse using radio telemetry receivers and various types of antennas such as this handheld antenna and receiver.  Photo courtesy of Chris Felege.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/Truck.JPG' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>We track grouse using radio telemetry receivers and various type so antennas such as this truck mounted antenna.  Photo courtesy of Anna Mattson.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/camera_system_1.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Once we find a nest, we install a miniature surveillance camera near the nest.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/camera_system_2.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Nest cameras are powered by cable, from about 75 feet away with a 12 volt battery and a waterproof box housing a DVR that will record to SD cards.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/grouse_nest.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>A sharp-tailed grouse nest.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/hen_on_nest.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Hens will incubate a nest for about 23 days unless a predator finds and destroys the nest.  This is a snapshot from our nest cameras of hen incubating her nest.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/hatch.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Sharp-tailed grouse chicks hatching.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/hawk2_resized.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Various predators such as this Swainson’s Hawk will attack the hen while she is sitting on the nest.  This http://volunteer.cs.und.edu/wildlife/images is from one of our nest cameras.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/badger1.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Ground predators, such as this American Badger, will destroy the nest.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/chick_capture.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>After the peak of hatch, we follow hens with broods via radio signals.  Once the chicks are about a month old, we will relocate the brood and catch them in big net during the night.  Chicks are then fitted with their own necklace-style radio transmitters so we monitor their survival and reproduction over the next year.</p>
                        </div>
                      </div>
                    </div>

                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/lightning.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>This image found in the collected video shows a lightning crash behind a piping plover incubating a nest on the Missouri River in North Dakota.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/plover_chick.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>This image found in the collected video shows a piping plover chick walking across the video screen with an adult brooding the remaining chicks on the nest in the background.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/plover_in_flight.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>The leg bands used by wildlife biologists for bird identification are clearly visible on the flying adult piping plover.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/tern_chick.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>A tern chick yawns in front of the camera while the adult tern incubates the remaining egg on the nest. This image was found in the collected video.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/tern_nest_exchange.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>Interior least terns exhibit biparental investment in their nests. This image found in the collected video shows a nest exchange event between two flying tern parents.</p>
                        </div>
                      </div>
                    </div>


                    <div class='item'>
                      <img src='http://volunteer.cs.und.edu/wildlife/images/tern_feeding_tern.png' alt=''>
                      <div class='container'>
                        <div class='carousel-caption'>
                          <p>An adult tern feeds a fish to a tern incubating a nest. This image was found in collected video.</p>
                        </div>
                      </div>
                    </div>

                  </div>
                  <a class='left carousel-control' href='#myCarousel' data-slide='prev'>&lsaquo;</a>
                  <a class='right carousel-control' href='#myCarousel' data-slide='next'>&rsaquo;</a>
                </div><!-- /.carousel -->
            </div>
        </div>
    </div>

<hr>

    <div class='container'>
        <div class='row'>
            <div class='span6'>
";

if ($stopped) {
    echo "
        <b>Wildlife@Home is temporarily shut down for maintenance.
        Please try again later</b>.
    ";
} else {
    db_init();
}


/*
if (!$stopped) {
    $profile = get_current_uotd();
    if ($profile) {
        echo "
            <div class='well well-large'>
            <td class=\"uotd\">
            <h2>".tra("User of the day")."</h2>
        ";
        show_uotd($profile);
        echo "</td></tr></div>\n";
    }
}
*/

echo "
    <div class='well well-small'>
    <tr><td class=\"news\">
    <h2>News</h2>
    <p>
";
include("motd.php");
show_news(0, 5);
echo "
    </td>
    </tr></table>
    </div>
";


echo"
            </div> <!-- span -->

            <div class='span6'>
                    <section id='contact'>
                      <div class='page-header'><h2>" . $content['activehead'] . "</h2></div>
                       <ul>" . $content['activecontent'] . "
                       </ul>

                    </section>

                    <section id='contact'>
                        <div class='page-header'><h2>" . $content['publicationhead'] . "</h2></div>
                        <p>" . $content['publicationbody'] . "</p>
                    </section>

                    <section id='contact'>
                        <div class='page-header'><h2>" . $content['contacthead'] . "</h2></div>
                        <p>" . $content['contactbody1'] . "</p>
                        <p>" . $content['wildlifeteamhead'] . "</p>
                        <ul>" . $content['wildlifeteambody'] . "</ul>

                        <p>" . $content['agencyhead'] . "</p>

                        <ul>" . $content['agencybody'] . "</ul>
						
						<p>" . $content['prevhead'] . "</p>
						
                        <ul>" . $content['prevbody'] . "</ul>
                    </section>

                    <section id='support'>
                        <div class='page-header'><h2>" . $content['supporthead'] . "</h2></div>

                        <div class='row-fluid'>
                            <div class='span2'>
                                <p align=center>
                                <a href='http://www.nsf.gov/'><img class ='floating' border='0' src='http://volunteer.cs.und.edu/wildlife/images/nsf1.png' width ='83' height='83' alt='Funded through a grant from the National Science Foundation (NSF).' /></a>
                                </p>
                            </div>
                            <div class='span10'>
                                <p>" . $content["supportbody1"] . "</p>
                            </div>
                        </div>

                        <div class='row-fluid'>
                            <div class='span2'>
                                <p align=center>
                                <a href='http://und.edu'><img src='http://volunteer.cs.und.edu/wildlife/images/und_logo.png'></a>
                                </p>
                            </div>
                            <div class='span10'>
                            <p>" . $content["supportbody2"] . "</p>
                            </div>
                        </div>
                        <br>

                        <div class='row-fluid'>
                            <div class='span2'>
                                <a href=\"http://gf.nd.gov\"><img src=\"http://volunteer.cs.und.edu/wildlife/images/ndgf_logo.png\"></a>
                            </div>
                            <div class='span10'>" . $content["supportbody3"] . "</div>
                        </div>
                        <br>

                        <div class='row-fluid'>
                            <div class='span2'>
                                <a href=\"http://www.npwrc.usgs.gov\"><img src=\"http://volunteer.cs.und.edu/wildlife/images/usgs_logo.png\"></a>
                            </div>
                            <div class='span10'>" . $content["supportbody4"] . "</div>
                        </div>
                        <br>

                        <div class='row-fluid'>
                            <div class='span2'>
                                <a href=\"http://boinc.berkeley.edu/\" style='display:block; margin-left:auto; margin-right:auto;'><img src=\"http://volunteer.cs.und.edu/wildlife/img/pb_boinc.gif\" alt=\"Powered by BOINC\"></a>
                            </div>
                            <div class='span10'>" . $content["supportbody5"] . "</div>
                        </div>

                    </section>
            </div> <!-- span -->

        </div> <!-- row -->
    </div>  <!-- container -->
";


if ($caching) {
//    page_tail_main(true);
    end_cache(INDEX_PAGE_TTL);
} else {
//    page_tail_main();
}

echo "<hr>";
print_footer();

echo "</body></html>";

?>
