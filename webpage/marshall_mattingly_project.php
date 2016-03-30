<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Using Computer Vision Algorithms to Detect Animals in UAS Imagery", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Using Computer Vision Algorithms to Detect Animals in UAS Imagery <small>by Marshall Mattingly</small></h2>
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
                        Marshall is working on using computer vision algorithms to automate the detection of animals in UAS imagery for the UAS Estimating Snow Geese project. To provide the algorithms with enough positive samples, Marshall has also developed the image review webpage, so that citizen scientists can help go through the 3 million plus images collected and create the training data set.
                        </p>

                        <p>
                        More details on the specific computer vision algorithms, particularly neural networks, coming soon.
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
