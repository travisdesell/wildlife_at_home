<?php


function create_filter($filters, &$filter, &$reported_filter) {
    $filter = '';
    $reported_filter = '';

    foreach ($filters as $key => $value) {
        //    error_log("    '$key' => '$value'");

        if ($key == 'report_status') {
            $reported_filter .= " vs2.report_status = '" . mysql_real_escape_string($value) . "' AND ";
        } else if ($key == 'instructional') {
            $reported_filter .= " vs2.instructional = true AND ";
        } else {
            if ($value == 'VALID or CANONICAL') {
                $filter .= " AND (observations." . mysql_real_escape_string($key) . " = 'VALID' OR observations." . mysql_real_escape_string($key) . " = 'CANONICAL') ";
            } else if (!is_numeric($value)) {
                $filter .= " AND observations." . mysql_real_escape_string($key) . " = '" . mysql_real_escape_string($value) . "' ";
            } else {
                $filter .= " AND observations." . mysql_real_escape_string($key) . " = " . mysql_real_escape_string($value) . " ";
            }
        }
    }

}

?>
