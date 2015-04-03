<?php

require_once("wildlife_db.php");

mysql_connect("localhost", $wildlife_user, $wildlife_pw);
mysql_select_db($wildlife_db);

//$query = "SELECT id, watermarked_filename FROM video_2 WHERE processing_status != 'UNWATERMARKED' AND (md5_hash IS NULL OR size IS NULL)";
$query = "SELECT id, watermarked_filename FROM video_2 WHERE processing_status != 'UNWATERMARKED'";
$results = mysql_query($query);
if (!$results) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");

while ($row = mysql_fetch_assoc($results)) {
    echo "processing: '" . $row['watermarked_filename'] . "\n";

    $md5_hash = md5_file($row['watermarked_filename'] . ".mp4");
    $size = filesize($row['watermarked_filename'] . ".mp4");

    $ogv_size = filesize($row['watermarked_filename'] . ".ogv");

    echo "mp4 size: $size, ogv size: $ogv_size\n";

    $needs_reconversion = 0;
    if ($size > (1.25 * $ogv_size)) $needs_reconversion = 1;


    $query_2 = "UPDATE video_2 SET md5_hash = '$md5_hash', size = $size, needs_reconversion = $needs_reconversion WHERE id = " . $row['id'];
    echo $query_2 . "\n";

    $result_2 = mysql_query($query_2);
    if (!$result_2) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query_2\n");
}

?>
