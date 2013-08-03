<?php
require_once("/home/tdesell/wildlife_at_home/webpage/navbar.php");
require_once("/home/tdesell/wildlife_at_home/webpage/footer.php");

require '/home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");


echo "
<html>
<head>
<title>UND Wildlife@Home: Sharp-Tailed Grouse Video Monitoring Training</title>

<link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
<link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>

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
<body>
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

$videos = array(
                'page_intro' => "<p>
                Cameras have become common and powerful tools in the field of avian ecology, as they can be used to capture events that would be otherwise difficult to impossible to observe in any other way.  Cameras allow us to evaluate a variety of questions ranging from behaviors to interactions with other species.
                </p>
                <p>
                We need your help so we can answer the following questions:
                <ol>
                    <li> Which predators are responsible for destroying nests?</li>
                    <li> Female grouse must allocate their time between incubating the eggs and foraging; how often do they leave to forage?  How long are they away from the nest on foraging bouts?</li>
                    <li>How often do grouse defend against predators?</li>
                </ol>
                </p>
                <p>
                To do this, we will need to you to watch 3 minutes clips of video to identify when the grouse is on the nest, leaving the nest, off the nest, and returning the nest.  
                </p>",

                'video_explanation' => array(
                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/bird_leaving_nest',
                        'video_description' => "<p>As you will notice, it can be challenging to determine if the bird is on the nest. Her coloration helps her to blend in with her surroundings, and she prefers to nest in areas with plenty of grass to conceal her and the nest.  Use this video where the bird is leaving the nest to see how difficult is to locate her until she moves, and how exposed the eggs become when she leaves the nest.</p>"
                    ),

                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/bird_returning',
                        'video_description' => "<p>After the foraging event, she will return to the nest and resume incubation.</p>"
                    ),

                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/badger',
                        'video_description' => '<p>In addition, you will report when the presence of a predator (or any other species) is in the field of view. These may be difficult to identify to species because they can happen quickly or the predator can stand in front of the camera making it difficult to see anything but fur or feathers! Below is a video of a Badger eating eggs.</p>'
                    ),

                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/hawk',
                        'video_description' => "<p>And here is a Swainson's Hawk which captures the hen from the nest.</p>"
                    ),

                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/defense',
                        'video_description' => "<p>You also will help us find events of nest defense. <i>Nest defense</i> is defined as any behavior where the parent increases its probability of injury or mortality in order to increase the probability of the contents of the nest surviving.  Therefore, if a grouse is directing attacking a bird is nest defense, as well as when a bird may be doing broken wing displays or distraction displays.  Based on some preliminary video observations, sharp-tailed grouse appear to dip their heads, hold out their wings, and have their tails up in a similar fashion to displays observed at the lek (dancing grounds).</p>"
                    ),

                    array(
                        'video_href' => 'http://wildlife.und.edu/share/wildlife/Website_Training/hatch1',
                        'video_description' => '<p>Finally, we are interested in when a clutch hatches.  Below is an example of a clutch of grouse chicks leaving the nest.</p>'
                    )
                ),

                'page_end' => "<p>As you watch the videos, we will be interested at what time the event starts.  Please make note of the time from the time stamp in the video when you first notice the activity beginning. If it is already in progress (e.g., skunk sitting at the nest eating eggs), please make note of this in the comments section.  Any other interesting observation or details you wish to include, please include these in the comment section.</p><p>We appreciate your assistance in classifying nesting behaviors and predator interactions at the nest of sharp-tailed grouse!</p><p align=right> - The Wildlife@Home Team</p>"
            );

$training_template = file_get_contents("/home/tdesell/wildlife_at_home/webpage/training_template.html");

$m = new Mustache_Engine;
echo $m->render($training_template, $videos);

print_footer();

echo "
</body>
</html>";

?>
