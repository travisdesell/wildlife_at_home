<?php

function create_filter($filter_text, &$query) {
    error_log("filter text: '$filter_text'");
    $filters = explode("##", $filter_text);
    $with = true;
    foreach ($filters as $f) {
        error_log("   filter: '$f'");
        if (0 == strcmp($f, "with") || 0 == strcmp($f, "from")) {
            $with = true;
        } else if (0 == strcmp($f, "without") || 0 == strcmp($f, "not from")) {
            $with = false;
        } else if (0 == strcmp($f, "and")) {
            $query .= " AND ";
        } else if (0 == strcmp($f, "or")) {
            $query .= " OR ";
        } else if (0 == strcmp($f, "")) {
            break;
        } else {
            $parts = explode(" ", $f);

            $eq = "=";
            if (!$with) $eq = "!=";

            if (0 == strcmp($parts[0], "event")) {
                $query .= "obs.event_id $eq " . $parts[1] . "";
            } else if (0 == strcmp($parts[0], "animal_id")) {
                $query .= "v2.animal_id $eq '" . $parts[1] . "'";
            } else if (0 == strcmp($parts[0], "year")) {
                $query .= "DATE_FORMAT(v2.start_time, '%Y') $eq " . $parts[1];
            } else if (0 == strcmp($parts[0], "location")) {
                $query .= "v2.location_id $eq '" . $parts[1];
            } else if (0 == strcmp($parts[0], "species")) {
                $query .= "v2.species_id $eq " . $parts[1];
            } else if (0 == strcmp($parts[0], "other")) {
                $query .= " '$f' ";
            }
        }
    }
}

?>
