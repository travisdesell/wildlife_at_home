<?php

require_once("wildlife_db.php");

mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);

$query = "SELECT id, watermarked_filename FROM video_2 WHERE processing_status != 'UNWATERMARKED' AND (md5_hash IS NULL OR size IS NULL)";
$results = mysql_query($query);
if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

while ($row = mysql_fetch_assoc($results)) {
    $md5_hash = md5_file($row['watermarked_filename']);
    $size = filesize($row['watermarked_filename']);

    $query_2 = "UPDATE video_2 SET md5_hash = '$md5_hash', size = $size WHERE id = " . $row['id'];
    echo $query_2 . "\n";

    $result_2 = mysql_query($query_2);
    if (!$result_2) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query_2\n");
}

?>
