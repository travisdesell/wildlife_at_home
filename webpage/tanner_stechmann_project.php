<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Common Eider Research", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Common Eider Research <small>by Tanner Stechmann</small></h2>
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
                        Tanner will be joining the team in Fall 2016. As his role becomes more defined, this page will be filled with relevant research information.
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
