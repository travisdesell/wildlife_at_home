<?php

if (count($argv) != 3) {
    die("Error, invalid arguments. usage: php $argv[0] <project_id> <species_id>\n");
}

$project_id = $argv[1];
$species_id = $argv[2];

if ($project_id <= 0) {
    die("Must have a project_id\n");
}

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

function fill_database($num, $p_id, $s_id) {
    global $wildlife_db;

    $where = "i.project_id=$p_id";

    if ($s_id > 0) {
        $where .= " AND i.species=$s_id";
    }

    $where .= " AND i.views < i.needed_views AND NOT EXISTS(SELECT * FROM images_queue AS iq WHERE iq.image_id = i.id)";

    $query = "INSERT INTO images_queue (image_id, project_id, species) SELECT i.id, i.project_id, i.species FROM images AS i WHERE $where ORDER BY rand() LIMIT $num";
    $result = query_wildlife_video_db($query);

    return $wildlife_db->affected_rows;
}

$where = "iq.project_id=$project_id";
if ($species_id > 0) {
    $where .= " AND iq.species=$species_id";
}

# add if there aren't any in the database for this combo
$query = "SELECT * FROM images_queue AS iq WHERE $where";
$result = query_wildlife_video_db($query);
if ($result && $result->num_rows == 0) {
    $num = fill_database(500, $project_id, $species_id);
    die("Added $num new entries for $project_id and $species_id\n");
}

$query = "DELETE iq FROM images_queue as iq INNER JOIN images AS i on iq.image_id = i.id WHERE $where AND i.views >= i.needed_views";
$result = query_wildlife_video_db($query);

# fill in our affected rows
if ($result && $wildlife_db->affected_rows > 0) {
    $num = fill_database($wildlife_db->affected_rows, $project_id, $species_id);
    die("Deleted and replaced $num entries for $project_id and $species_id\n");
}

die("Nothing changed for $project_id and $species_id.\n");

?>
