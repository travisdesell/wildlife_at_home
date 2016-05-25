<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . '/../../citizen_science_grid/user.php');

$user = csg_get_user();
$user_id = $user['id'];

print_header("Wildlife@Home: Species Identification Help",  "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

$project_id = 1;
if (isset($_GET['p'])) {
    $project_id = $boinc_db->real_escape_string($_GET['p']);
}

$projects_template = file_get_contents($cwd[__FILE__] . "/templates/species_description.html");

require_once($cwd[__FILE__] . "/image_species.php");

$project_objects = NULL;
if (array_key_exists($project_id, $project_species)) {
    $project_objects = $project_species[$project_id];
}

if ($project_objects) {
    $m = new Mustache_Engine;
    $project_objects['project_id'] = $project_id;
    echo $m->render($projects_template, $project_objects);
} else {
    echo '<p>Help for this project coming soon!</p>';
}

print_footer();
?>
