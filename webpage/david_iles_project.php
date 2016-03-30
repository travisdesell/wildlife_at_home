<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Polar Bear Predation of Waterfowl Nests in Western Hudson Bay", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Polar Bear Predation of Waterfowl Nests in Western Hudson Bay <small>by David Iles</small></h2>
                </div>
            </section>

            <!--
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
            -->

            <section id='text' class='well'>
                <div class='row'>
                    <div class='col-sm-12'>
                        <p>
                        In spring, the lush coast of western Hudson Bay supports vast numbers of breeding waterfowl, including lesser snow geese, Canada geese, Ross’s geese, and common eiders.  Over 50,000 pairs of snow geese nest in coastal salt marshes and freshwater sedge meadows and use these areas to raise goslings before migrating south in the fall.
                        </p>

                        <p>
                        Western Hudson Bay also supports one of the southern-most and best-studied populations of polar bears in the world.  Unlike more northerly populations, polar bears in western Hudson Bay spend several months onshore each summer following the annual breakup of Hudson Bay sea ice.  In recent years, increased temperatures have caused sea ice to break up sooner on average.  This has resulted in earlier arrival onshore by polar bears, which is associated with lower polar bear body condition and survival, likely due to fewer seal hunting opportunities.
                        </p>

                        <p>
                        Although warmer spring temperatures are causing waterfowl nesting and hatching to occur earlier, sea-ice breakup and polar bear arrival onshore is advancing up to 4 times faster.  The advanced arrival of polar bears onshore is increasingly causing overlap with the snow goose nesting period, providing “early bears” access to hundreds of thousands of eggs.  Our recent observations suggest that polar bears have the capacity to eat large quantities of eggs once on land and cause catastrophic nest failure.  However, many other Arctic predators also consume eggs, including grizzly bears (a newly discovered species in the area), gray wolves, arctic foxes, ravens, gulls, eagles, and sandhill cranes.  The potential impact of polar bears on waterfowl populations relative to other members of the diverse predator community remains unclear.
                        </p>

                        <p>
                        In 2013 and 2014, we placed over 150 wildlife game cameras in waterfowl colonies along the coast of Western Hudson Bay, allowing us to continuously monitor over 500 nests throughout incubation.  To date, our cameras have collected over 3 million images.  Of key importance, these camera images will also allow us to estimate attack rates by different predator species and evaluate the relative impact of polar bears on nesting waterfowl.
                        </p>

                        <p>
                        This study will help us understand predator-prey relationships within the Hudson Bay Lowlands and the degree to which nest predators control the growth of waterfowl populations.  In addition, it will provide crucial insights into the ability of polar bears to seek new prey during longer onshore periods, and the resulting effects on waterfowl species.
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
