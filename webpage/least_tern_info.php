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
<title>UND Wildlife@Home: Interior Least Tern Ecology and Information</title>

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
                    <h2>Interior Least Tern Ecology and Information <small>by Alicia Andes</small></h2>
            </section>

            <section id='identification' class='well'>
                <div class='page-header'>
                    <h2>Identification</h2>
                </div>

                <p>
                Least Terns (<i>Sterna antillarum</i>) are the smallest member of the terns found in North America with a length of 21-23 cm and a wingspan of 48-53 cm. Adults usually weigh 30 to 45 g. Sexes are alike in plumage throughout the year. Permanent adult plumage consists of a gray back, rump and upper wings with outer 2-3 black primaries, white underparts and a deeply forked white tail.  During breeding season, adults exhibit a black cap, white triangle on their forehead, white strip through the eye, a yellow black tipped bill and yellow to orange legs.  The black cap reduces to a wide black stripe that connects both eyes and extends to the back, upper part of the head and forehead white with black specks while the beak turns black for the winter adult plumage.  Juveniles are distinguished by U-shaped patterns on gray to yellowish brown back and resemble adults in winter plumage. 
                </p>
            </section>

            <section id='distribution' class='well'>
                <div class='row-fluid'>
                    <div class='span4'>
                        <img src='http://volunteer.cs.und.edu/wildlife/images/least_tern_distribution.png' style='width:100%;'></img>
                        Yearly Distribution of the Least Tern (<i>Cornell Lab of Ornithology - All About Birds</i>)
                    </div>

                    <div class='span8'>
                        <div class='page-header'>
                            <h2>Distribution</h2>
                        </div>

                        <p>
                        Least terns have a wide breeding range that encompass both the North American Pacific and Atlantic coasts as well as along interior rivers, bays, lakes and alkali wetlands (Figure 1). Least terns migrate south in the fall and winter on coastal areas of Central and South America (Figure 1). 
                        </p>

                        <p>
                        Least terns build nests on the ground in sparsely vegetated sand, gravel and mud habitat on shorelines, sandbars, islands and wetlands. Recently, least terns were recorded building nests on flat gravel rooftops in areas where there was a decrease in suitable natural habitat. Nest bowl construction consists of a shallow depression or scrape in the substrate, usually adjacent to various sizes of woody debris. In some cases, adults will lay eggs in depressions caused by human or mammal footprints. Adults will occasionally decorate the nest bowl edges with small pebbles and shells. Since ground-nesting birds are more susceptible to mammalian and avian predators as well as damage from human recreation and livestock presence, least terns nest in colonies for additional defense against predation and destruction. Least terns often compete for nest sites with other birds such as Piping Plovers (<i>Charadrius melodus</i>), Snowy Plover (<i>Charadrius alexandrines</i>), Black Skimmer (<i>Rynchops niger</i>), Killdeer (<i>Charadrius vociferous</i>) and Spotted Sandpipers (<i>Actitis macularius</i>) that nest in the same areas and habitats.  
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
                Least tern adults, juveniles and chicks feed primarily on small fish, shrimp and occasionally invertebrates. Adults forage in various shallow water habitats. Foraging behavior consists of searching for prey by flying and hovering over water until the adult dives into the surface to grasp fish with their beak. The adults will rise from the water and shake off excessive wetness before either swallowing the fish in flight or carrying it to an incubating mate or chicks. Adults sometimes catch invertebrates in flight and shrimp while standing in shallow water off sandbars, shorelines, islands and wetlands. Since chicks are semi-precocial and are unable to feed themselves until capable of flight, adults call to them in flight and they are fed on the ground.   
                </p>

                <h3>Reproduction and Mating</h3>
                <p>
                Least terns are monogamous and form nesting colonies with various sizes that range from 2 to more than 2,000 pairs; however, the average colony size is less than 25 pairs. Least terns form pair bonds shortly before or at arrival to the breeding grounds. Courtship behavior consists of 2 stages: 1) aerial calling during flights and glides usually with an associated fish and 2) ground feeding, posturing and copulation with an exchange of fish between mates. 
                </p>

                <p>
                Both parents are involved in nest site selection and scrape formation. Several scrapes are formed before the female ultimately selects the final nest site.  Females lay 2-3 eggs in a clutch, one every day, that are oval, beige in color and speckled to camouflage against the nest substrate. Incubation begins at the start of the egg laying stage and lasts approximately 19-25 days.  Unless the nest is depredated or destroyed, pairs only breed once a season. Both adults share parental responsibilities; however, the female parent contributes the most time to incubation and chick-rearing duties.          
                </p>

                <p>
                Least tern chicks are born semi-precocial (meaning they are mobile but unable to feed themselves within a few hours of hatch), wet and covered with beige, spotted down well camouflaged to blend into the ground substrate. All chicks hatch within a few days, leave the nest permanently and are brooded by both parents until thermoregulation (i.e., the ability to maintain their body temperature) is achieved sometime before fledgling age at 20 days. 
                </p>

                <h3>Predation</h3>
                <p>
                Various types of predators were reported to depredate adults, chicks and nest such as the American Crow (<i>Corvus brachyrhynchos</i>), Great Horned Owl (<i>Bubo virginianus</i>), gulls (<i>Larus spp.</i>), Great Blue Heron (<i>Ardea Herodias</i>), coyote (<i>Canus latrans</i>), raccoon (<i>Procyon lotor</i>) and striped skunk (<i>Mephitis mephitis</i>). Adults utilize a variety of defensive behaviors to protect nests and chicks against predators, humans and livestock like alarm calls, aerial dive bombing, aerial defecation. The dive bomb behavior consists of adults flying above the threat, dropping into a sharp dive at the head, in many cases defecating or striking and finally flying up overhead again to repeat the process until the predator, human or competing shorebird vacates the area.
                </p>
            </section>

            <section id='conservation' class='well'>
                <div class='page-header'>
                    <h2>Conservation</h2>
                </div>

                <p>
                The interior populations of least terns were federally listed as endangered on May 28, 1985. Under the Endangered Species Act, least tern adults, nests, eggs and chicks are protected from collection, removal, destruction or any type of damage by human activities. States where the interior population is protected include Arkansas, Colorado, Iowa, Illinois, Indiana, Kansas, Kentucky, Louisiana, Mississippi, Missouri, Montana, North Dakota, Nebraska, New Mexico, Oklahoma, South Dakota, Tennessee and within  miles of the Texas coast. 
                </p>

                <p>
                The establishment of dams to control water flow on interior rivers disrupted the natural flood process that created habitat favorable for terns to successfully create nests, hatch chicks and fledge juveniles. The decline of suitable breeding habitat is the primary cause for the dangerous decrease in population size for terns. Increased predation rates associated with the lack of suitable nesting habitat is another factor that influenced the decline in interior least tern population abundance. Finally, least tern nesting habitat also happens to be prime areas for human recreation and development. Increased human presence and disturbance during the nesting season is also a major contributor to the declines in interior least tern abundance. 
                </p>

                <p>
                Management efforts to increase least tern population numbers focus on protecting, improving or maintaining nesting habitat. The use of signs and fences to exclude human activity near nesting areas is a popular management practice.  Habitat construction is a management practice utilized on the Missouri river with success to increase tern productivity. For the interior populations that nest on and along rivers, the most effective management practice to increase the amount of suitable nesting habitat includes restoring the natural flooding process that annually removes vegetation from islands, sandbars and shorelines.   
                </p>
            </section>

            <section id='literature' class='well'>
                <div class='page-header'>
                    <h2>Literature Cited</h2>
                </div>

                <ul>

                <li>Bent, A. C. 1929. Life histories of North American shorebirds. U.S. National Mus. Bulletin.143: 236-246.</li>

                <li>Boyd, R.L. 1993. Site tenacity, philopatry, longevity, and population trends of Least Terns In Kansas and northwestern Oklahoma. Pp 196-205 in Proc. Missouri River and its tributaries: Piping Plover and Least Tern Symposium (K.F. Higgins and M.R. Brashier eds.) South Dakota State University, Brookings, SD.</li>

                <li>Britton, E.E. 1982. Least Tern management by protection of nesting habitat. Trans. Northeastern Section Wildl. Soc. 39: 87-92.</li>

                <li>Burger, J. 1988. Social attraction in nesting Least Terns: effects of numbers, spacing, and Pair ponds. Condor 90: 575-582.</li>

                <li>Cornell Lab of Ornithology. 2014. Least Tern.  <a href='http://www.allaboutbirds.org/guide/least_tern/id'>http://www.allaboutbirds.org/guide/least_tern/id</a>. Accessed 22 February 2014.</li>
                
                <li>DeVault, T.L., M.B. Douglas, J.S. Castrale, C.E. Mills, T. Hayes and O.E. Rhodes, Jr. 2005. Identification of nest predators at a least tern colony in Southwestern Indiana. Waterbirds: The International Journal of Waterbird Biology 28: 445-449.</li>

                <li>Kaufman, K. Lives of North American Birds. Alfred A. Knopf, Inc., New York, 2000.</li>

                <li>Kirsch, E.M. 1996. Habitat selection and productivity of Least Terns on the lower Platte River, Nebraska. Wildl. Monogr. No. 132.</li>

                <li>Kirsch, E.M. and J.G. Sidle. 1999. Status of the interior population of least tern. The Journal of Wildlife Management 63: 470-483.</li>

                <li>Koenen, M.T., R.B. Utych, D.M. Leslie, Jr. 1996. Methods used to improve Least Tern and Snowy Plover nesting success on alkaline flats. J. Field Ornithol. 67: 281-291.</li>

                <li>Sherfy, M.H., J.H. Stucker and D.A. Buhl. 2012. Selection of nest-site habitat by interior least terns in relation to sandbar construction. The Journal of Wildlife Management 76: 363-371.</li>

                <li>Sibley, D.A. The Sibley Guide to Birds. Houghton Mifflin Company, New York, 2000.</li>

                <li>Smith, J.W. and R.B. Renken. Reproductive success of Least Terns in the Mississippi River Valley. Colon. Waterbirds 16: 39-44.</li>

                <li>Texas Parks and Wildlife. Interior Least Tern (Sterna antillarum athalassos). <a href='http://www.tpwd.state.tx.us/huntwild/wild/species/leasttern/'>http://www.tpwd.state.tx.us/huntwild/wild/species/leasttern/</a>. Accessed 22 February 2014.</li>

                <li>Thompson, B.C., J.A. Jackson, J. Burger, L.A. Hill, E.M. Kirsch, and J.L. Atwood. 1997. Least Tern (Sterna antillarum). In The Birds of North America, No. 290 (A. Poole and F. Gill, eds.) The Academy of Natural Sciences, Philadelphia, PA, and The American Ornithologists’ Union, Washington, D.C.</li>

                <li>Tomkins, I.R. 1959. Life history notes on the Least Tern. Wilson Bulletin 71: 313-322.</li>

                <li>U.S. Fish and Wildlife Service. 2014. Least Tern (<i>Sterna antillarum</i>) Species Profile. <a href='http://ecos.fws.gov/speciesProfile/profile/speciesProfile.action?spcode=B07N'>http://ecos.fws.gov/speciesProfile/profile/speciesProfile.action?spcode=B07N</a>. Accessed 22 February 2014.</li>

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
