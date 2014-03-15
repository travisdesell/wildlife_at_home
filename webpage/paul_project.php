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
<title>UND Wildlife@Home: Sharp-tailed Grouse Nest Predation Relative to Gas and Oil Development in North Dakota</title>

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
                <h2>Sharp-tailed Grouse Nest Predation Relative to Gas and Oil Development in North Dakota <small>by Paul Burr</small></h2>
                </div>
            </section>

            <section id='figures' class='well'>
                <div class='row-fluid'>
                    <div class='span4'>
                        <img style='width:100%;' src='images/paul_project_map.png'></img>
                        <p>Belden and Blaisdell study sites located in Mountrail county, ND. Active oil wells are shown with red dots.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/paul_project_tag.png'></img>
                        <p>Female sharp-tailed grouse being fitted with a radio collar so we can monitor her activities during the nesting season.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/paul_project_coyote.png'></img>
                        <p>Photo captured of a coyote by a trail camera during one of our predator surveys.</p>
                    </div>
                </div>
            </section>

            <section id='text' class='well'>
                <div class='row-fluid'>
                    <div class='span12'>
                        <p>
                        Western North Dakota has been experiencing an extreme expansion of gas and oil development in recent years. Although this energy development aids in economic stability and creates jobs opportunities, it is also having major impacts on the environment. Through the construction of thousands of oil pads, the prairie ecosystem of western North Dakota is experiencing large scale changes. This same prairie habitat is home to the sharp-tailed grouse (<i>Tympanuchus phasianellus</i>), a popular game bird species that relies on large expanses of North Dakota's grasslands. The purpose of this study was to estimate differences in nest success of sharp-tailed grouse in an area of intense oil development compared to an area of minimal oil development. In addition, we also monitored the predator communities in both areas, as nest predation is the primary cause of nest failure for this species.
                        </p>

                        <p>
                        We created two study sites in western North Dakota for this project. Our first site, Belden, is an area of intense energy development with numerous oil wells within and around its boundary. Our second site, Blaisdell, is an area of minimal energy development with only one oil well within and very few oil wells around its boundary. We trapped and radio collared female grouse at both sites during the breeding season of 2012 and 2013. These hens were tracked and their nests were monitored during the summer months. Some of these nests were also monitored using surveillance cameras to capture hen behaviors and nest depredations. 
                        </p>

                        <p>
                        During the same time we also conducted predator surveys using motion activated field cameras placed throughout each site. These surveys were implemented to determine relative differences in the predator community between the sites. Target predators included the coyote (<i>Canis latrans</i>), red fox (<i.Vulpes vulpes</i>), badger (<i>Taxidea taxus</i>), skunk (<i>Mephitis mephitis</i>), and raccoon (<i>Procyon lotor</i>).
                        </p>

                        <p>
                        In total we monitored have 163 sharp-tailed grouse nests between both sites during 2012 and 2013. Of these, 92 were also monitored using our surveillance cameras. Nest success was 62% at our Belden site (intense development), and only 45.2% at our Blaisdell site (minimal development). Badgers and skunks were found to be the primary nest predator, accounting for 56% of all recorded nest depredations. Predator surveys indicate that Blaisdell is 4.9 times more likely to be occupied by a nest predator suggesting a negative relationship between energy development and predator occurrence. These results reinforce our nest success findings, and illustrate the possible indirect influence of energy development on nest success through alterations of the local predator community. 
                        </p>

                    </div>
                </div>
            </section>

            <section id='figures2' class='well'>
                <div class='row-fluid'>
                    <div class='span4'>
                        <img style='width:100%;' src='images/paul_project_release.png'></img>
                        <p>Female sharp-tailed grouse being flushed from her nest using radio telemetry.</p>
                    </div>

                    <div class='span4'>
                        <img style='width:100%;' src='images/paul_project_tracking.png'></img>
                        <p>Installation of a camera on a sharp-tailed grouse nest.</p>
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
