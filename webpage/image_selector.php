<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

function get_count($table_name, $where_clause) {
    $results = query_wildlife_video_db("SELECT count(*) FROM $table_name WHERE $where_clause");
    if ($results->num_rows < 1)
        return 0;

    $row = $results->fetch_assoc();
    return $row['count(*)'];
}

print_header("Wildlife@Home: Image Selection", $additional_scripts);
print_navbar("Review Images", "Wildlife@Home", "..");


echo "
    <div class='container'>
        <div class='row'>
            <div class='col-sm-12'>
                <div class='well'>
                <p>Select the project (and species, if there is more than one species for the project) you'd like to review images for, and click the review images button to get started. You will have to <a href='../create_account_form.php'>create an account</a> first if you do not have one. Please take a look at the interface instructions and training images for each species first. Determining if a bird is on a nest can be particularly difficult! With your help, we'll be able to test different computer vision algorithms to automate the detection of animals within the images.
                </div>
            </div>
        </div>
    </div>
";

//$hbp_eider_total = get_count('images', 'project_id=1 and species=1');
//$hbp_eider_done  = get_count('images', 'project_id=1 and species=1 and views >= needed_views'); 
//$hbp_eider_ratio = ($hbp_eider_total != 0 ? $hbp_eider_done / $hbp_eider_total : 0) * 100;
$hbp_eider_total = 0;

//$hbp_lsg_total = get_count('images', 'project_id=1 and species=2');
//$hbp_lsg_done  = get_count('images', 'project_id=1 and species=2 and views >= needed_views');
//$hbp_lsg_ratio = ($hbp_lsg_total != 0 ? $hbp_lsg_done / $hbp_lsg_total : 0) * 100;
$hbp_lsg_total = 0;

//$uas_total = get_count('images', 'project_id=3');
//$uas_done = get_count('images', 'project_id=3 and views >= needed_views');
//$uas_ratio = ($uas_total != 0 ? $uas_done / $uas_total : 0) * 100;
$uas_ratio = 0;

$thumbnails = array('thumbnail_list' => array(
                        array(
                            'thumbnail_image' => './images/marshall_common_eider.png',
                            'species_name' => 'Common Eider',
                            'species_id' => '1',
                            'project_name' => 'Hudson Bay Project',
                            'project_id' => '1',
                            'species_latin_name' => 'Somateria mollissima',
                            'project_description' => '<p>We are using trail cameras with time-lapse photography coupled with motion sensor triggers to document nesting events of Common Eiders and Snow Geese at La Peruse Bay within Wapusk National Park, near Churchill, Manitoba.  Your help with facilitate us knowing what predators are in the nesting colonies, when predators are arriving at nests, and how the birds are behaving throughout incubation (time when birds tend their eggs).</p> <p>Active projects include: <ul><li>David Iles, Utah State University - <a href="david_iles_project.php">Polar Bear Predation of Waterfowl Nests in Western Hudson Bay</a></li><li>Tanner Stechmann - <a href="tanner_stechmann_project.php">Common Eider Research (full overview comming soon)</a></li></ul></p>',
                            'info_webpage' => 'species_help.php?p=1',
                            'site' => array(
                                'enabled' => true,
                                'site_name' => 'La Peruse Bay, Manitoba',
                                'year' => '2013-2016',
                                'done_ratio' => $hbp_eider_ratio,
                                'left_ratio' => 100 - $hbp_eider_ratio
                            )
                        ),

                        array(
                            'thumbnail_image' => './images/marshall_snow_goose.png',
                            'species_name' => 'Lesser Snow Goose',
                            'species_id' => '2',
                            'project_name' => 'Hudson Bay Project',
                            'project_id' => '1',
                            'species_latin_name' => 'Chen caerulescens caerulescens',
                            'project_description' => '<p>We are using trail cameras with time-lapse photography coupled with motion sensor triggers to document nesting events of Common Eiders and Snow Geese at La Peruse Bay within Wapusk National Park, near Churchill, Manitoba.  Your help with facilitate us knowing what predators are in the nesting colonies, when predators are arriving at nests, and how the birds are behaving throughout incubation (time when birds tend their eggs).</p><p>Active projects include: <ul><li>David Iles, Utah State University - <a href="david_iles_project.php">Polar Bear Predation of Waterfowl Nests in Western Hudson Bay</a></li></ul></p>',
                            'info_webpage' => 'species_help.php?p=1',
                            'site' => array(
                                'enabled' => true,
                                'site_name' => 'La Peruse Bay, Manitoba',
                                'year' => '2013-2016',
                                'done_ratio' => $hbp_lsg_ratio,
                                'left_ratio' => 100 - $hbp_lsg_ratio
                            )
                        ),
                        
                        array(
                            'thumbnail_image' => './images/marshall_snow_goose_blue.png',
                            'project_name' => 'UAS Estimating Snow Geese',
                            'project_id' => '3',
                            'project_description' => '<p><strong>Available soon. We\'re still creating some training documents.</strong> We are using an unmanned aerial survey (UAS) unit to take aerial imagery in the Hudson Bay, near Churchill, Manitoba.</p><p>Active projects include: <ul><li>Andrew Barnas</li><li>Marshall Mattingly - <a href="marshall_mattingly_project.php">Using Computer Vision Algorithms to Detect Animals in UAS Imagery</a></li></ul></p>',
                            'info_webpage' => 'species_help.php?p=3',
                            'site' => array(
                                'enabled' => false,
                                'site_name' => 'Hudson Bay, Manitoba',
                                'year' => '2014-2015',
                                'done_ratio' => $uas_ratio,
                                'left_ratio' => 100 - $uas_ratio
                            )
                        )
                    )
                );

shuffle($thumbnails['thumbnail_list']);

$projects_template = file_get_contents($cwd[__FILE__] . "/templates/image_projects_template.html");

error_log( "projects_template: " . $cwd[__FILE__] . "/templates/projects_template.html");

$m = new Mustache_Engine;
echo $m->render($projects_template, $thumbnails);

print_footer('Travis Desell, Susan Ellis-Felege and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "
</body>
</html>
";

?>
