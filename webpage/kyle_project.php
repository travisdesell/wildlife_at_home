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
<title>UND Wildlife@Home: Automated Wildlife Detection in Uncontrolled Environments</title>

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
                <h2>Automated Wildlife Detection in Uncontrolled Environments <small>by Kyle Goehner</small></h2>
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
                        We studying the ability of computers to detect non-rigid, camouflaged objects in uncontrolled settings. Computer vision is a popular topic with many active research areas. Many scientists use computer vision in controlled settings to monitor very specific behaviors. Computer vision ca  used for pedestrian detection in security cameras, autonomous vehicles, etc. The detection of camouflaged wildlife is difficult problem for computers and at times even humans.
                        </p>

                        <p>
                        The Wildlife@Home footage, and user collected data is being used to create a set of classification data. Specifically we using the Parent Behavior - On Nest and Parent Behavior - Not in Video events to create two distinct classes of video. These classes of video can be compared and the areas unique to the On Nest behavior can hopefully be used to teach the computer which frames most likely have a bird present in them and which most likely do not.
                        </p>

                        <p>
                        There are many different ways to approach this problem. With a variation in species and habitat we can see different computer vision techniques perform very differently. Other factors in algorithm performance can include image clarity, changes in lighting, environment movement, etc. Taking all of these factors into consideration make the problem of wildlife detection very difficult to be both accurate and consistent
                        </p>

                        </hr>

                        <p>
                        Current computer vision classification problems use techniques such a feature detection, normalization, and some type of classifier such as a support vector machine, neural network, or naive bayes classifier. Image pre-processing is used to help the feature detection/extraction algorithms as they are typically very sensitive to image noise and other artifacts such as lossy compression by-product.
                        </p>

                        <p>
                        Two feature types we are working with right now are SURF (Speeded-Up Robust Features) and HoG (Histogram of Gradients). SURF features are localized blobs detected using the determinant of Hessian which uses scale space extrema to determine features. Scale space extrema are pixels in sequentially blurred images that are either a maximum or minimum between their neighboring blurred images. HoG features takes a different approach and measures the image gradients in many small bins. As the name suggests, it counts different gradient orientations in very localized regions of the image. These localized regions are then normalized with other regions. HoG features are sensitive to object orientation as matches are based directly on gradient orientations.
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
