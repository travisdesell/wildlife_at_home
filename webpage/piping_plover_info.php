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
<title>UND Wildlife@Home: Piping Plover Ecology and Information</title>

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
            <section id='identification' class='well'>
                    <h2>Piping Plover Ecology and Information <small>by Leila Mohsenian</small></h2>
            </section>

            <section id='identification' class='well'>
                <div class='page-header'>
                    <h2>Identification</h2>
                </div>

                <p>
                Piping Plovers (<it>Charadrius melodus</it>) are small federally threatened shorebirds that weigh an average of 43-63 g and can range in length from 14-18 cm. The typical wingspan of a Piping Plover is 38cm. In general they are pale grey, similar to beach sand. This coloration allows them to blend into their habitat and surroundings, making it more difficult for predators to identify them. Their underside and chest are white, and they sport a short yellow bill with a contrasting black tip. Along with their body coloration and bill, another distinguishing feature of this shorebird is their bare, orange legs. In the summer there are several coloration changes, which will diminish during the non-breeding season (winter months). Piping Plovers will develop a single black neck band and a black band on their forehead (spanning from one eye to the other). During the non-breeding season their bills will turn completely black. There are several features of the Piping Plover that distinguish males from females. Males are darker in plumage with more prominent neck bands than females. Females are smaller in size and have darker bills. These shorebirds are seen individually or traveling in a small flock.
                </p>
            </section>

            <section id='distribution' class='well'>
                <div class='row-fluid'>
                    <img class='span4' src='http://volunteer.cs.und.edu/wildlife/images/piping_plover_distribution.png'></img>

                    <div class='span8'>
                        <div class='page-header'>
                            <h2>Distribution</h2>
                        </div>

                        <p>
                        Piping Plovers are primarily found in North America. Their summer breeding areas include shorelines and beaches east of the Rocky Mountains, in the Northern Great Plains, and along the great lakes. In the winter months, Piping Plovers migrate south and occupy beaches and mudflats along the coasts of Southeast America and northeastern Mexico.
                        </p>

                        <div class='page-header'>
                            <h2>Nesting Habitat</h2>
                        </div>
                        <p>
                        The nesting habitat of Piping Plovers includes open areas on dry sand and near water. They tend to avoid areas with grasses and other vegetation. Common nesting grounds are waterfront and inland sand beaches, beaches along rivers or alkali lakes, and sand dunes. Having their nesting habitats in open areas make them particularly susceptible to human disturbances and destruction, flooding, and predation. Frequently, Piping Plovers will nest near colonies of Least Terns (Sternula antillarum), Common Terns (Sternula hirundo) and Arctic Terns (Sternula paradisaea).
                        </p>
                    </div>
                </div>
            </section>

            <section id='ecology' class='well'>
                <div class='page-header'>
                    <h2>Ecology</h2>
                </div>

                <h3>Diet</h3>
                <p>
                The diet of an adult Piping Plover generally consists of small invertebrates, such as marine worms, crustaceans, mollusks, insects like beetles, and fly larvae. The specific types of invertebrates consumed depend on nearby habitat and feeding locations.
                </p>

                <h3>Reproduction and Mating</h3>
                <p>
                Female Piping Plovers have an average clutch size of four eggs, laying one egg every other day. After the last egg is laid, incubation begins and lasts about 25 days. Both male and female Piping Plovers take equal turns incubating the eggs. The exchange between parents happens very quickly. Piping Plover eggs hatch all at the same time, and the young typically fledge 24 days later, when their wings have developed enough for flight. Before the young have matured enough to maintain their own body temperature, parent plovers will brood the chicks, where the parent will gather the chicks underneath their bodies and sheltering the young with their wings to provide warmth.
                </p>

                <p>
                Several factors may affect the birth rates of Piping Plovers. Predation, human disturbances, inadequate foraging habitat and adverse weather conditions all contribute to decreased nest success. Nest predation is higher on beaches with high human traffic, where humans and their pets may damage nests. Trash left over on beaches and waterfront may also attract predators.
                </p>

                <h3>Predation</h3>
                <p>
                Several different species of animals prey on Piping Plovers. Predator species include the Red Fox (<it>Vulpes vulpes</it>), Striped Skunk (<it>Mephitis mephitis</it>), Virginia Opossum (<it>Didelphis virginiana</it>), Raccoon (<it>Procyon lotor</it>), American Crow (<it>Corvus brachyrhynchos</it>), Black-billed Magpie (<it>Pica hudsonia</it>), Coyote (<it>Canis latrans</it>), and domestic dogs (<it>Canis familiaris</it>) in areas with high levels of human traffic. 
                </p>

                <p>
                To defend both themselves and their offspring against predators, Piping Plovers have adapted several behaviors. Parents often display the \"Broken wing\" behavior, which is an act where the shorebird crouches towards the ground and feigns injury when a predator approaches the nest. Once the predator is lured away from the nest, the parent flees. The appearance of easy prey draws predators away from the nest and their chicks, increasing the likelihood of their survival.
                </p>
            </section>

            <section id='conservation' class='well'>
                <div class='page-header'>
                    <h2>Conservation</h2>
                </div>

                <p>
                On January 10, 1986, Piping Plovers became a protected species under the Endangered Species Act, which established penalties if they are removed from their nest, harassed, or harmed in any way. Piping Plovers are listed as endangered species in Maine, New Hampshire, Iowa, Maryland, Indiana, Michigan, Pennsylvania, Minnesota, Wisconsin, and Ohio.  Other states in their breeding range, including North Dakota, consider them a federally threatened species. One of the reasons for the declining populations includes shoreline development for recreational uses and human disturbances. Over the past 50-60 years, increased human use and disturbance of shoreline habitats has decreased available nesting sites or reduced the quality of these areas.  High levels of human traffic along shorelines create more disturbances that may cause these birds to flee their nests and threaten nest success. There have also been increased efforts towards habitat protection (which may include installing predator exclosures around nests) and management programs to help sustain shorelines typically occupied by this endangered bird.
                </p>
            </section>

            <section id='literature' class='well'>
                <div class='page-header'>
                    <h2>Literature Cited</h2>
                </div>

                <ul>
                <li>Bent, A. C. 1929. Life histories of North American shorebirds. U.S. National Mus. Bulletin.143: 236-246.</li>

                <li>Cohen, J. B., Houghton, L. M., and J. D. Fraser. 2009. Nest Density and Reproductive Success of Piping Plovers in Response to Storm- and Human-Created Habitat Changes. Wildlife Monographs. 173: 1-24.</li>

                <li>Cuthbert, F. J., and T. Wiens. 1982. Status and Breeding Biology of the Piping Plover in Lake of the Woods County, Minnesota.</li>

                <li>Connecticut Department of Energy and Environmental Protection. DEEP: Piping Plover Fact Sheet. CT.gov Portal. <a href='http://www.ct.gov/deep/cwp/view.asp?A=2723&Q=326062'>http://www.ct.gov/deep/cwp/view.asp?A=2723&Q=326062</a>. Accessed 26 February 2013. </li>

                <li>Mabee, T. J., Plissner, J. H., Haig, S. M. and J. P. Goossens. 2001. Winter distributions of North American plover in the Laguna Madre regions of Tamaulipas. Wader Study Group Bulletin. 94: 39-43. </li>

                <li>U.S. Fish and Wildlife Service. 2012. Overview  Piping plover - Atlantic coast population Northeast Region. <a href='http://www.fws.gov/northeast/pipingplover/overview.html'>http://www.fws.gov/northeast/pipingplover/overview.html</a>. Web. Accessed 26 February 2013.</li>

                <li>NYS Dept. of Environmental Conservation. Piping Plover Fact Sheet. <a href='http://www.dec.ny.gov/animals/7086.html'>http://www.dec.ny.gov/animals/7086.html</a>.  Accessed 26 February 2013.</li>

                <li>Tern and Plover Conservation Partnership. Piping Plover. University of Nebraska-Lincoln. <a href='http://ternandplover.unl.edu/plover/index-plover.asp'>http://ternandplover.unl.edu/plover/index-plover.asp</a>. Accessed 26 February 2013. </li>

                <li>Rimmer, D. W., and R. D. Deblinger. 1990. Use of Predator Exclosures to Protect Piping Plover Nests. J. Field Ornithol. 61.2: 217-223.</li>

                <li>Shaffer, F., and P. Laporte. 1994. Diet of Piping Plovers on the Magdalen Islands, Quebec. Wilson Bulletin. 106: 531-536.</li>

                <li>Stukel, Eileen D. 1996. Piping Plover. South Dakota Department of Game, Fish and Parks, Wildlife Division. <a href='http://www3.northern.edu/natsource/ENDANG1/Piping1.htm'>http://www3.northern.edu/natsource/ENDANG1/Piping1.htm</a>. Accessed 26 February 2013.</li>

                </ul>

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
