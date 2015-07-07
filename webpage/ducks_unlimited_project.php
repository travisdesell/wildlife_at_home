<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

print_header("Wildlife@Home: Ducks Unlimited Project", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>

            <section id='title' class='well'>
                <div class='page-header'>
                <h2>Predation and Parental Care at Blue-Winged Teal Nests in North Dakota <small>by John Palarski and Nickolas Conrad</small></h2>
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

            <section id='identification' class='well'>
                <div class='page-header'>
                    <h2>Project Summary</h2>
                </div>

                <p>
                Cameras have become common and powerful tools in the field of avian ecology allowing for behavioral observations to be made that were previously impossible to collect. In collaboration with Ducks Unlimited biologists, we will conduct nest searches during the 2015 summer and install 24-hour surveillance cameras at the nests of blue-winged teal (<i>Anas discors</i>).  Nests will be monitored until hatch or failure when the camera will be removed and relocated to a new nest. Videos will be reviewed for behaviors of the incubating hen, predator events, and nest fate (hatch or failed).  Videos will also be uploaded for citizen scientists to review and assist in nest behavioral classification through the interdisciplinary, web-based citizen science project Wildlife@Home.  Findings from this study will include the opportunity to expand our knowledge on basic behaviors of nesting waterfowl that are currently unknown, facilitate a new research collaboration between UND and Ducks Unlimited, and conduct collection of preliminary data for grants to allow for comparisons of nesting behaviors and life history strategies across different taxonomic orders of birds. Further, this project will enhance UND’s archive of nesting videos that is being used to: 1) educate K-12 students in STEM disciplines, 2) educate the public on conservation issues, 3) test computer vision algorithms that can be used to filter through large ecological datasets, and 4) provide undergraduates with research experience.   
                </p>
            </section>


            <section id='figures' class='well'>
                <div class='row'>
                    <div class='col-sm-6'>
                        <img style='width:100%;' src='images/nest_dragging.png'></img>
                        <p>Nest dragging to flush hens.</p>
                    </div>

                    <div class='col-sm-6'>
                        <img style='width:100%;' src='images/camera_installation.png'></img>
                        <p>Installing cameras.</p>
                    </div>

                    <div class='col-sm-6'>
                        <img style='width:100%;' src='images/camera_installation_2.png'></img>
                        <p>Inspecting a camera.</p>
                    </div>
                </div>
            </section>


            <section id='distribution' class='well'>
                <div class='row'>
                    <div class='col-sm-4'>
                        <img src='./images/least_tern_distribution.png' style='width:100%;'></img>
                        Yearly Distribution of the Least Tern (<a href='http://www.allaboutbirds.org/guide/least_tern/id'><i>Cornell Lab of Ornithology - All About Birds</i></a>)
                    </div>

                    <div class='col-sm-8'>
                        <div class='page-header'>
                            <h2>How Do We Find the Nests?</h2>
                        </div>

                        <p>
                        We use an established protocol called <i>nest dragging</i> where two ATVs pull a long chain between them in order to flush hens from their nests (see image below).  The chain (yellow arrow pointing to where you can see it in the grass) goes over the grasses and as it approaches a nest, the hen flushes giving the researchers a chance to see where she was sitting and hopefully nesting.  Because the chain goes over the grasses, it does not hit the eggs and thus just allows us to locate nests.  
                        </p>

                        <p>
                        Once the nest has been located, we record information about the nesting hen such as GPS location and how old the eggs are.  We do this using a method called candling the eggs.  Using a piece of radiator hose, you can hold the egg up to the sun and see the development of the embryo to approximate how many days into incubation the nest is and when it might hatch.  
                        </p>

                        <p>
                        We then install our cameras at the nest to watch what is going on with the hens during incubation, see if any predators destroy nests, and hopefully watch many of the ducklings hatch.  
                        </p>

                    </div>
                </div>
            </section>


            <section id='identification' class='well'>
                <div class='page-header'>
                    <h2>Role of Citizen Scientists - We Need Your Help!</h2>
                </div>

                <p>
                We need your help to watch nesting video and help us classify nesting behaviors, such as when the hen is on the nest attending her eggs versus when she out foraging and maintaining herself (i.e., \"mommy time!\").  We will also be interested in how blue-winged teal (and eventually other ducks we hope) respond to different predators, which predators are most common at the Ducks Unlimited Coteau Ranch, and how often nests hatched.  In addition, providing us about when the teal is in the field of view of the camera (whether on or off the nest) will be helpful to create a database of training videos to test out computer vision algorithms for our computer science team.  
                </p>

                <p>
                To do this, we will have you select behaviors and their associated start and stop times.  Please review the <a href='http://csgrid.org/csg/wildlife/sharptailed_grouse_training.php'>training pages for sharp-tailed grouse</a> to learn how to classify behaviors in our web interface.  As we accumulate nesting videos and you watch the videos, tell us about what you see by selecting the “Discuss this Video” button.  This will allow our project scientists to review questions you have and to use video you have for training pages specific to blue-winged teal.  
                </p>

                <p>
                As you watch video, you will earn badges for how much you watch and how many behaviors you classify.  We will compare your observations to our expert scientists, other citizen scientists, and our computer vision algorithms to validate them. 
                </p>

                <p>
                We find that citizen scientists are extremely good at helping due some coarse filtering for primary behaviors such as on and off nest events and nest fate classification (hatch versus predation) so know that your contributions are leading to a better understanding of waterfowl ecology AND computer science methods.  
                </p>

                <p>
                We encourage you to fill out the surveys for our citizen scientists so we can continue to learn how to better provide services to our volunteers and important information about who are volunteers are! 
                </p>
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
