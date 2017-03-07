#!/usr/bin/env php

<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . '/../../citizen_science_grid/my_query.php');

// first update the queue
$query = "SELECT DISTINCT mi.id AS mosaic_id, io.user_id AS user_id FROM mosaic_images AS mi INNER JOIN mosaic_split_images AS msi on msi.mosaic_image_id = mi.id INNER JOIN image_observations AS io ON io.image_id = msi.image_id WHERE (SELECT COUNT(*) FROM mosaic_user_status WHERE user_id=io.user_id AND mosaic_image_id=mi.id) = 0";
$result = query_wildlife_video_db($query);
while ($result && ($row = $result->fetch_assoc()) != null) {
    $mosaic_id = $row['mosaic_id'];
    $user_id = $row['user_id'];
    if (!query_wildlife_video_db("INSERT INTO mosaic_user_status (mosaic_image_id, user_id) VALUES ($mosaic_id, $user_id)")) {
        echo "Failed to add data: $mosaic_id, $user_id\n";
    }
}

$query = "SELECT mus.* FROM mosaic_user_status AS mus WHERE (SELECT io.id FROM image_observations AS io WHERE io.image_id = (SELECT msi.image_id AS image_id FROM mosaic_split_images AS msi WHERE msi.mosaic_image_id = mus.mosaic_image_id ORDER BY number DESC LIMIT 1) AND io.user_id = mus.user_id) IS NOT NULL";
$result = query_wildlife_video_db($query);
if (!$result) {
    echo "Query failed. Exiting.\n";
    exit();
}

while (($row = $result->fetch_assoc())) {
    $query = "UPDATE mosaic_user_status SET completed = 1 WHERE mosaic_image_id = ${row['mosaic_image_id']} AND user_id = ${row['user_id']}";
    if (!query_wildlife_video_db($query)) {
        echo "Failed to update ${row['mosaic_image_id']} // ${row['user_id']}\n";
    }
}

$query = "SELECT * FROM mosaic_user_status WHERE completed = 0 AND starttime <= NOW() - INTERVAL 1 MONTH";
$result = query_wildlife_video_db($query);
if (!$result) {
    echo "Query failed. Exiting.\n";
    exit();
}

while (($row = $result->fetch_assoc())) {
    $query = "UPDATE mosaic_user_status SET inactive = 1 WHERE mosaic_image_id = ${row['mosaic_image_id']} AND user_id = ${row['user_id']}";
    if (!query_wildlife_video_db($query)) {
        echo "Failed to update ${row['mosaic_image_id']} // ${row['user_id']}\n";
    }
}

?>
