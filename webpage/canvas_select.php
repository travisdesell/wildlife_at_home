<?php
$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

$result = array();
$project_id = 1;

error_log("POST: " . print_r($_POST, 1));
if (isset($_POST['p'])) $project_id = $_POST['p'];
error_log("PROJECT: $project_id");

/*
if ($project_id == 2) {
    $res = query_uas_db("SELECT name, speciesId FROM tblSpecies");

    while ($row = $res->fetch_assoc()) {
        $phase = query_uas_db("SELECT name, phaseId FROM tblPhases");

        if ($prow = $phase->fetch_assoc()) {
            do {
                $results[] = array(
                    "name" => $row['name'] . ' - '.$prow['name'],
                    "id" => $row['speciesId'],
                    "phaseId" => $prow['phaseId']
                );
            } while ($prow = $phase->fetch_assoc());
        } else {
            $results[] = array(
                "name" => $row['name'],
                "id" => $row['speciesId'],
                "phaseId" => 0
            );
        }
    }
}*/

$res = query_wildlife_video_db("SELECT sl.species, spl.species_id FROM species_project_lookup AS spl INNER JOIN species_lookup AS sl ON sl.species_id = spl.species_id WHERE project_id=$project_id");
while ($res && ($row = $res->fetch_assoc()) != null) {
    $result[$row['species']] = $row['species_id'];
}

echo json_encode($result);
?>
