<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Refined Monitoring Techniques to Understand Least Tern and Piping Plover Nest Dynamics", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Refined Monitoring Techniques to Understand Least Tern and Piping Plover Nest Dynamics <small>by Alicia Andes</small></h2>
                </div>
            </section>

            <section id='figures' class='well'>
                <div class='row'>
                    <div class='col-sm-4'>
                        <img style='width:100%;' src='images/alicia_plover_adult.png'></img>
                        <p>A piping plover adult.</p>
                    </div>

                    <div class='col-sm-4'>
                        <img style='width:100%;' src='images/alicia_plover_tern_habitat.png'></img>
                        <p>A suitable nesting habitat for Least Terns and Piping Plovers on the Upper Missouri River.</p>
                    </div>

                    <div class='col-sm-4'>
                        <img style='width:100%;' src='images/alicia_tern_chicks_eggs.png'></img>
                        <p>Two newly hatched Least Tern chicks and one egg in a nest bowl.</p>
                    </div>
                </div>
            </section>

            <section id='text' class='well'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <p>
                        Interior Least Terns (<i>Sternula antillarum</i>; \"terns\") and Piping Plovers (<i>Charadrius melodus</i>; \"plovers\") are small shorebirds that nest on unvegetated sand habitat, such as temporary sandbars and permanent islands, on the Missouri River during the summer months. The establishment of dams to control water flow on the Missouri River disrupted the natural flood process that created habitat favorable for terns and plovers to successfully create nests, hatch chicks and fledge juveniles. The decline of suitable breeding habitat is the primary cause for the dangerous decrease in population size for terns and plovers.
                        </p>

                        <p>
                        Both species are protected by the federal government because they are classified as endangered (terns) and threatened (plovers). A species is considered endangered if it is at risk of extinction in the near future. An endangered species classification indicates that the number of individuals is too low to sustain a viable population and genetic diversity without intervention. A threatened species has a declining population size in jeopardy to reach levels low enough for an endangered classification. If the present conditions persist, these species will disappear without proper management that will increase population numbers. The Army Corp. of Engineers is responsible to manage the populations of terns and plovers that nest on the Missouri River. In order to successfully implement management plans, the Army Corp. collaborated with The University of North Dakota (UND) and the United States Geological Survey (USGS) to conduct research that improves our understanding about the relationship between the current conditions of the breeding environment available to the species and their population dynamics.
                        </p>

                        <p>
                        The University Of North Dakota initiated a project to conduct research on terns and plovers by installing miniature surveillance cameras and DVRs at nests on the Upper Missouri River.  We deployed 30 and 11 camera systems at plover and tern nests respectively out of 142 and 77 monitored throughout the 2013 reproductive season between the Garrison Dam and Bismarck on the Missouri River in North Dakota. For plovers, total camera nest fates included 17 successfully hatched, 8 depredations, 3 abandoned, 1 livestock destruction and 1 unknown fate due to camera failure. The nest predators identified on camera were 1 coyote (<i>Canis latrans</i>), 4 American crows (<i>Corvus brachyrhynchos</i>) and 3 black-billed magpies (<i>Pica hudsonia</i>). For terns, 11 of the 77 nests found and monitored received cameras. None of the tern nests with cameras were predated; instead, nest fates included 8 successfully hatched, 2 abandoned and 1 unknown fate due to camera failure.
                        </p>

                        <p>
                         The ability to record video at tern and plover nests provided the opportunities to identify predators of the nests, document the parental behavioral activities at the nest, to observe the possible impacts of researcher disturbance on parent attendance and the time differences between chick hatch events. The information obtained from this research will provide information about predators, behavior and research that will support the development of a management plan that will increase the populations of both terns and plovers. 
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>";

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";


?>
