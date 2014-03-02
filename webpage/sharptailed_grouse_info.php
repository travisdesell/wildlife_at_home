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
<title>UND Wildlife@Home: Sharp-Tailed Grouse Ecology and Information</title>

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
                <h2>Sharp-Tailed Grouse Ecology and Information <small>by Adam Pach</small></h2>
            </section>

            <section id='identification' class='well'>
                <div class='page-header'>
                    <h2>Identification</h2>
                </div>

                <p>
                Sharp-tailed Grouse (<i>Tympanuchus phasianellus</i>) are medium sized, ground-nesting birds that
                average a length of 43 cm, a 63.5 cm wingspan, and an average weight of 880 g (Sibley 2000).
                The coloration is a drab gray-brown mottled with white (Figure 1). Both sexes have horizontal
                or v-shaped markings on the breast, a slight crest on the head, white spots on the wings, light
                colored flanks and belly, and feathered legs (Johnson and Knue 1989). Males have purple air
                sacs on the side of the neck and a yellow comb above the eyes, both of which are only visible
                during mating season. Wings are short and rounded, used for short bursts of flight followed by
                gliding. The tail is pale and pointed.
                </p>

                <p>
                You can also view the training videos for identifying sharp-tailed grouse, their nests, and their predators <a href='http://volunteer.cs.und.edu/wildlife/sharptailed_grouse_training.php'>here</a>.
                </p>
            </section>

            <section id='distribution' class='well'>
                <div class='row-fluid'>
                    <img class='span4' src='images/BirdsOfNorthAmericaOnline_Distribution.png'></img>

                    <div class='span8'>
                        <div class='page-header'>
                            <h2>Distribution</h2>
                        </div>

                        <p>
                        Sharp-tailed Grouse can be found from Alaska, east to East Central Canada, south
                        through Michigan, Minnesota and Wisconsin, and west to Southern Colorado and Utah (Figure
                        2). Sharp-tailed Grouse prefer open, fairly treeless areas that contain a mix of dense grasses,
                        forbs, and shrubs. Habitat is chosen based on openness and plant density (Marks 2007).
                        </p>

                        <p>
                        Nesting habitat of grouse depends on what is available to them. Some studies have
                        suggested that grouse prefer to nest near areas with taller, denser shrubs. This is not a necessity
                        for a successful nest, as many females will select areas with lower shrub cover but a higher level
                        of forbs (Goddard et al. 2009). The extra cover provided by the shrubs or dense forbs is most
                        important during the first 14 days after chicks hatch, which is when offspring survival rates are
                        the lowest due to predation and adverse weather (Goddard and Dawson 2009).
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
                The diet of adult grouse is comprised mainly of plant material, including buds, flowers,
                and seeds. A small portion of the adult diet consists of insects, including ants, beetles,
                grasshoppers and crickets (Marks 2007). The only months when grouse do not consistently feed
                on the ground are the winter months when snow covers these food sources. During these
                months, grouse will often forage in shrubs and trees.
                </p>

                <h3>Reproduction</h3>
                <p>
                Sharp-tailed Grouse are among four species of North American grouse that congregate in
                specific areas, known as leks, to engage in elaborate mating rituals known as 'dancing' (see lek
                videos). Mating is based on selection of suitable males, who take part in the displays, by females,
                who do not display but rather observe male displays and choose a mate. The size of a lek is
                relatively small, ranging from as small as a small house or as large as a baseball diamond. Leks
                are traditionally used multiple years, although if the habitat is no longer suitable a new lek can be
                formed elsewhere. A lek is usually found within 1-2 kilometers of denser grass cover, which is
                necessary for nesting materials and cover (Prose 1987). Leks often have low, sparse vegetation,
                allowing for clear vision in all directions and adequate space to engage in dancing, as well as
                vision to help escape and minimize predation. Areas that are often used for leks include
                rangeland, harvested or low cropland, low ridges and knolls, recent burns, and even abandoned
                runways (Prose 1987).
                </p>

                <p>
                Beginning in March, the males gather on the lek in the morning, usually arriving 30 to
                60 minutes before sunrise, and at its peak a lek may contain anywhere from 2 to 35 displaying
                males. Once on the lek, males usually remain there for 2 or 3 hours. This behavior of returning
                to the lek in the morning takes place on a lesser scale in the fall. The purpose of returning to a
                lek in the fall is to maintain the hierarchy and structure established in the spring, which can have
                a substantial impact on male mating success (Tsuji et. al. 1994).
                </p>

                <p>
                The dancing ritual of the males is highly elaborate and involves both active and relaxed
                phases. To begin, the male bends forward to the point that its body is nearly parallel with the
                ground. It spreads its wings and fans them a bit, so they are perpendicular with its body. The
                tail is held erect and is the only part of the body that is not held parallel to the ground, and the air
                sacs on the side of the neck are also inflated. This position is held for the duration of the dance
                (Prose 1987).
                </p>

                <p>
                The movements involved in the dance are very rapid and precise. The male will either
                rush forward or rapidly spin in a circle. No matter which the male chooses, while dancing it also
                rapidly stomps its feet. Additionally, the male moves its central tail feathers so they make a
                clicking noise, as well as producing hooting, cackling, cooing and gobbling sounds. Often there
                is more than one male dancing at the same time, and the males involved often start and stop
                specific movements at the exact same time, almost as if they were doing this on cue. These
                dancing bouts can last from roughly 30 to 50 seconds (Marks 2007).
                </p>

                <p>
                Within 1 to 3 days of mating the hen will lay the first egg, with an additional egg being
                laid every 1 to 2 days. Clutch sizes average 11 to 12 eggs and the incubation period lasts 21 to
                25 days, beginning when the last egg is laid. Although the eggs are laid days apart they all hatch
                at the same time due to incubation not occurring until the last egg is laid, and thus, the precocial
                (i.e., very well developed young with feathers, eyes open, and ready to leave the nest shortly
                after hatch) young will hatch within 24 hours of one another and leave the nest (Roersma 2001).
                </p>

                <h3>Predation</h3>
                <p>
                Common nest predators include coyote (<i>Canis latrans</i>), striped skunk (<i>Memphitis
                memphitis</i>), a variety of ground squirrels, common ravens (<i>Corvus corax</i>), and common
                predators of grouse include coyote, a variety of hawks, and great horned owl (<i>Bubo virginianus</i>,
                Schroeder and Baydack 2001). However, our study is examining this very issue and we hope to
                provide insight on specific rates of different predators in western North Dakota.
                </p>
            </section>

            <section id='conservation' class='well'>
                <div class='page-header'>
                    <h2>Conservation</h2>
                </div>

                <p>
                Sharp-tailed grouse populations are in decline in areas, but this decline is not considered
                to be a major concern yet. Many states and provinces that have Sharp-tailed Grouse populations
                have a hunting season on the bird.
                </p>

                <p>
                One major factor contributing to the declines noted is habitat loss. Two different factors
                play into this. First, many acres of grassland are being converted for agriculture. Second, grouse
                will move away from an area if too much woody vegetation is present. This is especially evident
                in areas with leks, where males will abandon a lek where even small increases of woody
                vegetation have occurred (Hanowski et. al. 2000). Furthermore, recent expansion of gas and oil
                development in western North Dakota may result in fragmentation of the vast grasslands, as well
                as frequent disturbances such as noise, additional lighting, and dust.
                </p>
            </section>

            <section id='literature' class='well'>
                <div class='page-header'>
                    <h2>Literature Cited</h2>
                </div>

                <ul>
                <li>
                Goddard, A. D., and R. D. Dawson.  2009.  Factors influencing the survival of neonate sharp
                tailed grouse (<i>Tympanuchus phasianellus</i>).  <i>Wildlife Biology</i> 15: 60-67.
                </li>

                <li>
                Goddard, A. D., R. D. Dawson, and M. P. Gillingham.  2009.  Habitat selection by nesting and
                brood-rearing sharp-tailed grouse.  <i>Canadian Journal of Zoology</i> 87: 326-336.
                </li>

                <li>
                Hanowski, J. M., D. P. Christian, and G. J. Niemi.  2000.  Landscape requirements of prairie
                sharp-tailed grouse (<i>Tympanuchus phasianellus campestris</i>) in Minnesota, USA.  <i>Wildlife 
                Biology</i> 6: 257-263.
                </li>

                <li>
                Johnson, M. D., and Knue, J.  1989.  Feathers from the Prairie.  North Dakota Game and Fish
                Department, Bismarck, USA.
                </li>

                <li>
                Marks, R.  2007.  Sharp-tailed Grouse (<i>Tympanuchus phasianellus</i>). U. S. Department of
                Agriculture Publication 40, Washington, D. C., USA. 
                </li>

                <li>
                Prose, B.L. 1987. Habitat suitability index models: plains sharp-tailed grouse. U.S. Fish and Wildlife
                Service Biology Report 82(10.142). 31 pp.
                </li>

                <li>
                Roersma, S. J.  2001.  Nesting and Brood Rearing Ecology of Plains Sharp-tailed Grouse
                (<i>Tympanuchus phasianellus jarnesi</i>) in a Mixed Grass/Fescue Ecoregion of Southern
                Alberta.  Thesis, University of Manitoba, Winnipeg, Canada.
                </li>

                <li>
                Schroeder, M. A., and R. K. Baydack.  2001.  Predation and the management of prairie grouse.  
                <i>Wildlife Society Bulletin</i> 29(1): 24-32.
                </li>

                <li>
                Sharp-tailed Grouse (<i>Tympanuchus phasianellus</i>).  <a href='http://sdakotabirds.com/species/sharp_tailed_grouse_info.htm'>South Dakota Birds and Birding Homepage</a>. Accessed 10 Apr 2012.
                </li>

                <li>
                Sibley, D. A.  2000.  The Sibley Guide to Birds.  Alfred A. Knopf, Dai Nippon, China.
                </li>

                <li>
                Tsuji, L. J. S., D. R. Kozlovic, M. B. Sokolowski, and R. I. C. Hansell.  1994.  Relationship of
                Body Size of Male Sharp-Tailed Grouse to Location of Individual Territories on Leks.
                <i>The Wilson Bulletin</i> Vol. 106, 2: 329-337.
                </li>
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
