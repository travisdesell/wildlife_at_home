<?php
	require_once("wildlife_db.php");


	if (count($argv) < 2) $number_of_processes = 1;
	else $number_of_processes = $argv[1];

	$images_not_found = 0;
	$modulo = -1;
	$child_pids = array();

	for($i = 0;$i < $number_of_processes;$i++) {
		$pid = pcntl_fork();

		if($pid == -1) {
			die("Error, could not fork. Dying.\n");
		} else if (!$pid) {
			$modulo = $i;
			break;
		} else $child_pids[] = $pid;
	}

	if($modulo > -1) {

		mysql_connect("localhost", $wildlife_user, $wildlife_pw);
		mysql_select_db($wildlife_db);

		while(true) {
			$query = "Select archive_filename, id, watermarked from images where watermarked != 1 and (id % $number_of_processes) = $modulo limit 1";
			$result = mysql_query($query);
			if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");


			$row = mysql_fetch_assoc($result);

			if (!$row) {
				echo("No images left to watermark, attempt: $images_not_found");
				$images_not_found++;
				if ($images_not_found >= 5) die("No images left to watermark.");
			} else $images_not_found = 0;



			echo $row['archive_filename']."\n";

			$archive_filename = $row['archive_filename'];
			$watermarked_filename = str_replace("/archive/","/watermarked/", $archive_filename);

			if ($row['watermarked'] == 0) {
				$base_directory = substr($watermarked_filename, 0, strrpos($watermarked_filename, "/"));
				echo "\n$base_directory\n";
				mkdir($base_directory, 0755, true);

				$watermark_file = "/photo/watermarkhudson.png";

				$command = "convert $archive_filename -resize 1024x768 $watermark_file -composite -quality 100 $watermarked_filename";

				echo "\nExecuting $command\n";
				shell_exec($command);

				$md5_hash = md5_file($watermarked_filename);
				$filesize = filesize($watermarked_filename);
				$query = "update images set watermarked = 1, watermarked_filename = '$watermarked_filename', size = $filesize, md5_hash = '$md5_hash' where id =" . $row['id'];
				$result = mysql_query($query);
				if(!$result) die("MYSQL Error (". mysql_errno() ."): " . mysql_error() . "\nquery: $query\n");
			}
		}
	} else {
		for($i = 0;$i < $number_of_processes;$i++) {
			pcntl_waitpid($child_pids[$i], $status);
		}
	}

?>
