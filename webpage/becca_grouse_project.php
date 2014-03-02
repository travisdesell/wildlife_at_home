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
<title>UND Wildlife@Home: Nest Cameras and Citizen Science: Implications for evaluating Sharp-tailed Grouse Nesting Ecology</title>

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
            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Nest Cameras and Citizen Science: Implications for evaluating Sharp-tailed Grouse Nesting Ecology <small>by Rebecca Eckroad</small></h2>
                </div>
            </section>

            <section id='figures' class='well'>
                <div class='row-fluid'>
                    <div class='span4'>
                        <img style='width:100%;' src='images/becca_grouse_morning_recess.png'></img>
                        <p>Hen leaving the nest for morning recess.  Notice that both the radio collar and leg band are visible.  The band gives the bird a unique identifying number, and the collar sends out a signal allowing researchers to locate her using radio telemetry.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/becca_grouse_inspection.png'></img>
                        <p>Hen visually inspecting the camera.</p>
                    </div>
                    <div class='span4'>
                        <img style='width:100%;' src='images/becca_grouse_nest_defense.png'></img>
                        <p>Hen posturing, a behavior associated with nest defense.  The arrow points out the upright and pointed position of her tail, which gave the bird its common name.</p>
                    </div>
                </div>
            </section>

            <section id='text' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <p>
                        I am evaluating the behaviors female sharp-tailed grouse (<i>Tympanuchus phasianellus</i>) exhibit during incubation for hens nesting in areas of high and low natural gas and oil development.  Nest recesses are when the hen leaves the nest for her own self-maintenance such as feeding.  I look at when the hens leave, how long they are gone, and how many times they leave per day.  Information about these events can provide researchers with information about basic incubation patterns, and determine if disturbances related to gas and oil production are correlated with the way hens allocate their time. 
                        </p>

                        <p>
                        Nest defense events are any time the hen exhibits behaviors that puts her in danger in the attempt to save her eggs.  For these events I am interested in what the hen is defending against, what defense behaviors she is displaying (posturing, pecking, attacking the predator, etc.), time of day, and duration of the event.  Results from this data will help researchers determine under what circumstances hens will be more willing to risk their lives for the safety of the nest, which may influence the way biologists manage the habitat where these birds live.
                        </p>

                        <p>
                        We have observed hens interacting with the cameras used to monitor them; therefore, I am also evaluating the potential impacts of camera technology on hen behaviors.  Interactions with camera include visual observation, physical inspection (pecking), and attack.  I will be looking at the frequency of these events and how much time they spend interacting with the camera.  This data will help us quantify research disturbance, and give suggestions on how to reduce research disturbance for future studies using nest cameras. 
                        </p>

                        <p>
                            Nest camera studies provide great insight to the day to day lives of these birds; however 24-hour surveillance of multiple nests results in large quantities of data which can take years to filter.  The use of citizen scientists to initially filter data allows researchers to quickly pinpoint the time in which certain events have happened.  My goal is to determine how efficient citizen scientists are at filtering through these large quantities of video data.  I will be doing this by examining how volunteers and researchers classify behaviors for sharp-tailed grouse, piping plovers (<i>Charadrius melodus</i>) and interior least terns (<i>Sternula antillarum</i>).  Results from this may increase the number of camera studies that utilize citizen scientists allowing not only researchers to gain knowledge of particular species and biological systems, but also to bring awareness of ongoing environmental issues to the public. It will also help wildlife researchers find solutions to filtering through large video datasets to be more efficient at providing information that can assist in making management decisions.  
                        </p>
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
