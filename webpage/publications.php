<?php

$cwd = __FILE__;
if (is_link($cwd)) $cwd = readlink($cwd);
$cwd = dirname($cwd);

require_once($cwd . "/navbar.php");
require_once($cwd . "/footer.php");

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";

$bootstrap_scripts = file_get_contents($cwd . "/bootstrap_scripts.html");


echo "
<html>
<head>
<title>UND Wildlife@Home: Publications and Presentations</title>

<link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
<link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
<link rel='stylesheet' type='text/css' href='style.css'>

$bootstrap_scripts

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
";

$active_items = array(
                'home' => '',
                'watch_video' => '',
                'message_boards' => '',
                'preferences' => '',
                'about_wildlife' => 'active',
                'community' => ''
            );

print_navbar($active_items);

echo "
<div class='container'>
    <div class='row-fluid'>
        <div class='span12'>
            <section id='press' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <h3>Press</h3>

                        <ul>
                            <li><b>Watching Wildlife at Home</b>. <i>UND Arts and Sciences Feature</i>. <a href='http://arts-sciences.und.edu/features/2014/01/watching-wildlife.cfm'>[html]</a></li>
                        </ul>
                    </div>
                </div>
            </section>
                        
            <section id='publications' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <h3>Journal Articles</h3>

                        <ul>
                            <li>Susan N. Ellis-Felege, Travis Desell, Christopher J. Felege. <b>A Bird's Eye View of... Birds: Combining Technology and Citizen Science for Conservation</b>. <i>Wildlife Professional</i>. <a href='./publications/birds_eye_view.pdf'>[pdf]</a> courtsey of <a href='http://www.wildlife.org/publications/twp'>The Wildlife Professional.</a></li>
                        </ul>
                    </div>
                </div>
            </section>
                        
            <section id='publications' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>

                        <h3>Conference Proceedings</h3>

                        <ul>
                            <li>Travis Desell, Robert Bergman, Kyle Goehner, Ronald Marsh, Rebecca VanderClute, and Susan Ellis-Felege. <b>Wildlife@Home: Combining Crowd Sourcing and Volunteer Computing to Analyze Avian Nesting Video</b>. <i>In the 2013 IEEE 9th International Conference on e-Science</i>. Beijing, China. October 23-25, 2013. <a href='http://people.cs.und.edu/~tdesell/papers/2013_escience_wildlife.pdf'>[pdf]</a></li>
                        </ul>

                    </div>
                </div>
            </section>

            <section id='oral_presentations' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <h3>Oral Presentations</h3>

                        <ul>
                            <li>Susan N. Ellis-Felege, Travis Desell, and Christopher J. Felege. <b>Wildlife@Home: Conservation Outreach Using Nest Cameras, Citizen Science and Computer vision</b>. <i>The North Dakota Chapter of the Wildlife Society Conference</i>. 12-14 February 2014, Mandan, ND. <a href='publications/felege_conservation_outreach_talk.pdf'>[pdf]</a> </li>

                            <li>Travis Desell, Robert Bergman, Kyle Goehner, Ronald Marsh, Rebecca VanderClute, and Susan Ellis-Felege. <b>Wildlife@Home: Combining Crowd Sourcing and Volunteer Computing to Analyze Avian Nesting Video</b>. <i>The 9th International Conference on E-Science (e-Science 2013)</i>. Beijing, China. October 23, 2013. <a href='http://people.cs.und.edu/~tdesell/talks/2013_october_23_escience/index.html'>[html]</a> </li>

                            <li>Travis Desell and Susan N. Ellis-Felege. <b>Wildlife@Home</b>. <i>The 8th International BOINC Workshop</i>. University of Westminster, London, UK. September 27, 2012. <a href='http://people.cs.und.edu/~tdesell/talks/2012_boinc_workshop.ppt.zip'>[ppt]</a> <a href='http://people.cs.und.edu/~tdesell/talks/2012_boinc_workshop.key'>[keynote]</a> </li>

                            <li>Travis Desell and Susan N. Ellis-Felege. <b>Wildlife@Home</b>. <i>The UND Digital Media Showcase</i>. Fire Hall Theatre, Grand Forks, North Dakota, USA. April 11, 2012. <a href='http://people.cs.und.edu/~tdesell/talks/2012_und_digital_media_showcase.ppt.zip'>[ppt]</a> <a href='http://people.cs.und.edu/~tdesell/talks/2012_und_digital_media_showcase.key'>[keynote]</a> </li>


                        </ul>
                    </div>
                </div>
            </section>

            <section id='poster_presentations' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>

                        <h3>Poster Presentations</h3>
                        <ul>
                            <li>J. P. Johnson, Rebecca A. Eckroad,  Aaron C. Robinson, and Susan N. Ellis-Felege.  <b>Nest attendance patterns in sharp-tailed grouse in western North Dakota</b>.  <i>The North Dakota Chapter of the Wildlife Society Conference</i>. 12-14 February 2014, Mandan, ND. <a href='publications/RecessPoster_NDCTWS2014_Finalpdf.pdf'>[pdf]</a> </li>

                            <li>Rebecca A. Eckroad, Paul C. Burr, Aaron C. Robinson, and Susan N. Ellis-Felege. <b>Impact of camera installation on nesting sharp-tailed grouse (Tympanuchus phasianellus) behavior in western North Dakota</b>.  <i>The North Dakota Chapter of the Wildlife Society Conference</i>. 12-14 February 2014, Mandan, ND. <a href='publications/Becca_ND_TWS2014_small.pdf'>[pdf]</a> </li>

                            <li>Alicia K. Andes, Susan N. Ellis-Felege, Terry L. Shaffer, and Mark H. Sherfy.  <b>A video camera technique to monitor piping plover and least tern nests on the Missouri River in North Dakota</b>. <i>The North Dakota Chapter of the Wildlife Society Conference</i>. 12-14 February 2014, Mandan, ND. <b>Won most outstanding student poster award</b>. <a href='publications/Andes_ND_TWS_Poster_2014_small.pdf'>[pdf]</a> </li>

                            <li>Leila Mohsenian, Alicia K. Andes, and Susan N. Ellis-Felege. <b>The mysterious life of piping plovers: nesting behaviors of a threatened species</b>. <i>The North Dakota Chapter of the Wildlife Society Conference</i>. 12-14 February 2014, Mandan, ND. <a href='publications/mysterious_life_poster.pdf'>[pdf]</a></li>

                            <li>Julia P. Johnson, Rebecca A. Eckroad, Aaron C. Robinson, and Susan N. Ellis-Felege. <b>Nest attendance patterns in sharp-tailed grouse in western North Dakota</b>. <i>The Wildlife Society’s 20th Annual Conference</i>. 4 – 10 October 2013, Milwaukee, Wisconsin (Student- In- Progress Poster Presentation; won 2nd place in undergraduate presentation category). <a href='publications/2013_10_poster_nest_attendence_patterns.pdf'>[pdf]</a></li>
                        </ul>

                    </div>
                </div>
            </section>
        </div>
    </div>
</div>";

print_footer();

echo "
</body>
</html>
";


?>
