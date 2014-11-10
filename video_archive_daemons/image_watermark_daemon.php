<?php
	require_once("wildlife_db.php");

	mysql_connect("localhost", $wildlife_user, $wildlife_pw);
	mysql_select_db($wildlife_db);

	$iteration = 0;
	$images_not_found = 0;

	$query = "Select archive_filename, watermarked_filename, watermarked from images where watermarked != 1 limit 1";
	$result = mysql_query($query);
	if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");


	$row = mysql_fetch_assoc($result);

	if (!$row) {
		echo("No images left to watermark, attempt: $images_not_found");
		$images_not_found++;
		if ($images_not_found >= 5) die("No images left to watermark.");
	} else $images_not_found = 0;



	echo $row['archive_filename']."\n";
	echo $row['watermarked_filename']."\n";

	$archive_filename = $row['archive_filename'];
	$watermarked_filename = str_replace("/archive/","/watermarked/", $archive_filename);

	if ($row['watermarked'] == 0) {
		$base_directory = substr($watermarked_filename, 0, strrpos($watermarked_filename, "/"));
		mkdir($base_directory, 0755, true);

		$watermark_file = "/photo/watermarkhudson.png";

		$command = "convert $archive_filename --resize 1024x768 $watermark_file -composite -quality 100 $watermarked_filename";

		shell_exec($command);

		$query = "update images set watermarked = 0, watermarked_filename = '$watermark_filename' where id =" . $row['id'];

?>
