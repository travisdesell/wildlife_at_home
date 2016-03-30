<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
connect_uas_db();

$flights = query_uas_db("select * from tblFlights");
if ($flights->num_rows < 1)
    die("No data retrieved from tblFlights");

// go through all the flights
while ($flight = $flights->fetch_assoc()) {
    $result = query_wildlife_video_db("select * from uas_flights where name='".$flight['name']."' and directory='".$flight['directory']."'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $flight_id = $row['id'];
    } else {
        $result = query_wildlife_video_db("insert into uas_flights (timestamp, name, directory, latitude_n, latitude_s, longitude_e, longitude_w) values ('".$flight['timestamp']."', '".$flight['name']."', '".$flight['directory']."', ".$flight['latitudeN'].", ".$flight['latitudeS'].", ".$flight['longitudeE'].", ".$flight['longitudeW'].")");

        // we can't continue if we didn't insert
        if (!$result)
            continue;

        // we use the newly created id to insert the rest
        $flight_id = $wildlife_db->insert_id;
    }

    // go through all the images
    $images = query_uas_db("select * from tblImages where flightId=".$flight['flightId']);
    while ($image = $images->fetch_assoc()) {
        $result = query_wildlife_video_db("select * from uas_flight_images where flight_id=$flight_id and name='".$image['name']."'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $image_id = $row['id'];
        } else {
            $result = query_wildlife_video_db("insert into uas_flight_images (flight_id, timestamp, name, latitude, longitude, height, yaw, pitch, roll, img_width, img_height) values ($flight_id, '".$image['timestamp']."', '".$image['name']."', ".$image['latitude'].", ".$image['longitude'].", ".$image['height'].", ".$image['yaw'].", ".$image['pitch'].", ".$image['roll'].", ".$image['img_width'].", ".$image['img_height'].")");

            // we can't continue if we didn't insert
            if (!$result)
                continue;

            $image_id = $wildlife_db->insert_id;
        }

        // go through all the split images
        $split_images = query_uas_db("select * from tblSplitImages where imageId=".$image['imageId']);
        while ($split_image = $split_images->fetch_assoc()) {
            $filename = "/share/uas_wildlife/images/".$split_image['name'];
            $result = query_wildlife_video_db("select * from images where archive_filename='$filename'");
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $new_image_id = $row['id'];
            } else {
                // first insert the image
                $result = query_wildlife_video_db("insert into images (datetime, archive_filename, camera_id, species, year, project_id) values ('".$image['timestamp']."', '".$filename."', '0', 999999, YEAR('".$image['timestamp']."'), 3)");

                // can't continue if we didn't insert
                if (!$result)
                    continue;

                $new_image_id = $wildlife_db->insert_id;
            }

            // make sure it isn't already inserted
            $result = query_wildlife_video_db("select * from uas_flight_split_images where uas_flight_image_id=$image_id and image_id=$new_image_id");
            if ($result->num_rows > 0)
                continue;

            // insert the new split image
            query_wildlife_video_db("insert into uas_flight_split_images (uas_flight_image_id, image_id, x, y, width, height) values ($image_id, $new_image_id, ".$split_image['left'].", ".$split_image['top'].", ".$split_image['width'].", ".$split_image['height'].")");
        }
    }
}
?>
