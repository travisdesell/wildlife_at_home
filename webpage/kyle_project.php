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
<title>UND Wildlife@Home: ???</title>

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
                <h2>???? <small>by Kyle Goehner</small></h2>
                </div>
            </section>

            <section id='figures' class='well'>
                <div class='row-fluid'>
                    <div class='span4'>
                        <img style='width:100%;' src='images/SampleTestFrame.png'></img>
                        <p>Example frame with an Interior Least Tern on its nest.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/SampleTestBegin.png'></img>
                        <p>Sample frame processed with SURF where the red dots represent non-matching features, blue represent matching features, and green represent matching-learned matching features.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/SampleTestEnd.png'></img>
                        <p>Over the course of a one hour video the blue and green features are compiled and the result is show in this image. Clusters of features are found around the nesting location of the Tern.</p>
                    </div>
                </div>
            </section>

            <section id='text' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <p>
                        I am studying the ability of computers to detect non­rigid, camouflaged objects in uncontrolled settings. Computer vision is a popular topic with many active research areas. Many scientists use computer vision in controlled settings to monitor very specific behaviors. Computer vision can be used for pedestrian detection in security cameras, autonomous vehicles, etc. The detection of camouflaged wildlife is difficult problem for computers and sometimes even humans.
                        </p>

                        <p>
                        The Wildlife@Home footage, and user collected data is being used to create a set of classification data. Specifically I am using the Parent Behavior ­ On Nest and Parent Behavior ­ Not in Video events to create two distinct classes of video. These classes of video can be compared and the areas unique to the On Nest behavior can hopefully be used to teach the computer which frames most likely have a bird present in them and which most likely do not.
                        </p>

                        <p>
                        There are many different ways to approach this problem. With a variation in species and habitat we can see different computer vision techniques perform very differently. Other factors in algorithm performance can include image clarity, changes in lighting, environment movement, etc. Taking all of these factors into consideration make the problem of wildlife detection very difficult to be both accurate and consistent.
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
