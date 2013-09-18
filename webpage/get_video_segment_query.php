<?php


function create_filter($filters, &$filter, &$reported_filter) {
    $filter = '';
    $reported_filter = '';

    foreach ($filters as $key => $value) {
        error_log("    '$key' => '$value'");

        if ($key == 'report_status') {
            $reported_filter .= " AND vs2.report_status = '" . mysql_real_escape_string($value) . "'";
        } else if ($key == 'instructional') {
            $reported_filter .= " AND vs2.instructional = true ";
        } else {
            if ($value == 'VALID or CANONICAL') {
                $filter .= " AND (o." . mysql_real_escape_string($key) . " = 'VALID' OR o." . mysql_real_escape_string($key) . " = 'CANONICAL') ";
            } else if (!is_numeric($value)) {
                $filter .= " AND o." . mysql_real_escape_string($key) . " = '" . mysql_real_escape_string($value) . "' ";
            } else {
                $filter .= " AND o." . mysql_real_escape_string($key) . " = " . mysql_real_escape_string($value) . " ";
            }
        }
    }

    if (strlen($reported_filter) > 5) $reported_filter = substr($reported_filter, 4);
    if (strlen($filter) > 5) $filter = substr($filter, 4);

}

?>
