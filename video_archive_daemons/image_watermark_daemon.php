<?php
	//Ben Carpenter
	require_once("wildlife_db.php");

	//Specify the number of processes to use from cmd, or just use one
	if (count($argv) < 2) $number_of_processes = 1;
	else $number_of_processes = $argv[1];

	$images_not_found = 0;
	$modulo = -1;
	$child_pids = array();

	//Fork off the new processes
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
		//Child process, connects to the database
		mysql_connect("localhost", $wildlife_user, $wildlife_pw);
		mysql_select_db($wildlife_db);

		while(true) {
			//Select the next unmarked image from the database
			$query = "Select archive_filename, id, watermarked from images where watermarked != 1 and (id % $number_of_processes) = $modulo limit 1";
			$result = mysql_query($query);
			if (!$result) die ("MYSQL Error (" . mysql_errno() . "): " . mysql_error() . "\nquery: $query\n");


			$row = mysql_fetch_assoc($result); //Get the row from the result of the query

			if (!$row) { //If there was nothing from the database, try again a few more times
				echo("No images left to watermark, attempt: $images_not_found");
				$images_not_found++;
				if ($images_not_found >= 5) die("No images left to watermark.");
			} else $images_not_found = 0;



			echo $row['archive_filename']."\n";

			$archive_filename = $row['archive_filename'];
			$watermarked_filename = str_replace("/archive/","/watermarked/", $archive_filename); //The watermarked file structure is the same as the archive, but using
													     //watermarked instead of archive

			if ($row['watermarked'] == 0) { //Image has not been watermarked
				$base_directory = substr($watermarked_filename, 0, strrpos($watermarked_filename, "/")); //Extract the current folder structure, and create it if it doesn't exist
				echo "\n$base_directory\n";
				mkdir($base_directory, 0755, true);

				if (strpos($archive_filename, 'hudson_bay_project') !== false) //Hudson bay has its own watermark
					$watermark_file = "/photo/watermarkhudson.png";
				else $watermark_file = "/photo/undwatermark.png"; //For everthing else, there's this one (for now)

				$command = "convert '$archive_filename' -resize 1024x768 $watermark_file -composite -quality 100 '$watermarked_filename'"; //Shell command for image conversion

				echo "\nExecuting $command\n";
				$return = shell_exec($command); //TODO Find better way of dealing with conversion failing
				if ($return) die("Convert failed on image: ". $archive_filename."\n"); //If conversion failed, quit and print error

				//Get MD5 and filesize for boinc
				$md5_hash = md5_file($watermarked_filename);
				$filesize = filesize($watermarked_filename);
				//Update database with the new info and set watermarked to 1
				$query = "update images set watermarked = 1, watermarked_filename = '$watermarked_filename', size = $filesize, md5_hash = '$md5_hash' where id =" . $row['id'];
				$result = mysql_query($query);
				if(!$result) die("MYSQL Error (". mysql_errno() ."): " . mysql_error() . "\nquery: $query\n");
			}
		}
	} else {
		//This is the parent, wait for the children to finish
		for($i = 0;$i < $number_of_processes;$i++) {
			pcntl_waitpid($child_pids[$i], $status);
		}
	}

?>
