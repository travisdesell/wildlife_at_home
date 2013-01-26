<?php
/*
Add4.php is the fourth version of the video adding script for this server.
Methodology:
- Upon given a target folder, will search for video files recursively in that folder and in folders contained within
- The found video files will be added to an array.
- When the search(es) has ended, the following operations will be applied to the video files:
	- A search for any children videos in the video database will be executed
	- If not found, they will be watermarked with the resulting parent video being put in the corresponding "watermarked" folder
	- This parent folder will then be used to make "children" videos of 3:00 in length. These will be put in the corresponding "streaming" folder.
- Upon given a video file, will apply the aforementioned operations to said video file.
*/

$item = $argv[2];
$passwd = $argv[1];

mysql_connect("localhost", "wildlife_user", $passwd);
mysql_select_db("wildlife_video");

$original = getcwd();
$videolist = array();
$i = 0;
$finaliteration = 0;
$end = 0;

function sequence($item, $cwd) {
    if(!sqlcheck($item)) {
        echo "This item is already in the database.\n";
        return 0;
    }

    splitter($item, $cwd);
}

function sqlcheck($item) {
    $query = "Select id from archive_video where location LIKE '" . $item . "'"; //We're looking for the splitted versions of this vid in the database
    $results = mysql_query($query);
    $num = mysql_num_rows($results);

    if(!empty($num)) {
        return false;
    } else {
        return true;
    }
}

/*
 * Should be able to apply the watermark during the splitting.
function watermark($item) {
    global $original;

    $check = shell_exec("ls");
    $check = explode("\n", $check);
    for($i = 0; $i < count($check); $i++) {
        if(strstr($check[$i], "watermark.png")) {
            $tripper = true;
            break;
        }
    }

    if(empty($tripper)) {
        die("No watermark found. Ending script...\n");
    }

    $name = explode(".", $item);
    $newname = $name[0] . "_PARENT.flv";
    $address = str_replace("archive", "watermarked", $newname);
    $address = str_replace("testing", "watermarked", $newname);

    $string = "ffmpeg -y -i " . $item . " -ar 44100 -vb 400000 -qmax 5 -vf \"movie=watermark.png [watermark]; [in] [watermark] overlay=10:10 [out]\" " . $address;
    shell_exec($string);
    return $address;
}
 */

// function to explode on multiple delimiters, used in the splitter function.
function multi_explode($pattern, $string, $standardDelimiter = ':') {
    // replace delimiters with standard delimiter, also removing redundant delimiters
    $string = preg_replace(array($pattern, "/{$standardDelimiter}+/s"), $standardDelimiter, $string);

    // return the results of explode
    return explode($standardDelimiter, $string);
}

//function splitter($item, $new) {
function splitter($item, $cwd) {
    $vidlisting = array();

    echo "splitting: " . $item . "\n";

    ob_start();
    passthru("ffmpeg -y -i {$item} 2>&1");
    $info = ob_get_contents();
    ob_end_clean();

    $pattern = "/Duration:\s+([0-9][0-9]:[0-9][0-9]:[0-9][0-9]\.[0-9][0-9]?)/";
    $exists = preg_match($pattern, $info, $matches);
    if(!$exists) {
        die("No duration found.\n");
    }

    $timetotalarray = multi_explode("/[:\.]/", $matches[1]);

    echo "timetotalarray[0]: " . $timetotalarray[0] . "\n";
    echo "timetotalarray[1]: " . $timetotalarray[1] . "\n";
    echo "timetotalarray[2]: " . $timetotalarray[2] . "\n";
    echo "timetotalarray[3]: " . $timetotalarray[3] . "\n";

    echo "timetotalarray: " . $timetotalarray[0] . ":" . $timetotalarray[1] . ":" . $timetotalarray[2] . "." . $timetotalarray[3] . "\n";

    $total_seconds = ($timetotalarray[0] * 3600) + ($timetotalarray[1] * 60) + $timetotalarray[2];

    echo "total_seconds: " . $total_seconds . "\n";

    $start_time = 0;
    $duration = 180;

    if ( substr($item, -4) != ".avi" && substr($item, -4) != ".wmv") {
        echo "Item was not a video file: '" . $item . "'\n";
        return;
    }

    $filename = substr($item, 0, -4); //this is the filename without the last 4 characeters (the .avi extension)
    $filename = str_replace("archive", "streaming", $filename);

    $iteration = 0;

    $long_flv = $filename . "_PARENT.flv";
    $watermarked_flv = $filename . "_WATERMARKED.flv";

    echo "creating PARENT: " . $long_flv . "\n";
    echo "creating WATERMARKED: " . $watermarked_flv . "\n";

    echo "target dirname: " . dirname($long_flv) . "\n";
    if (!is_dir(dirname($long_flv))) {
        echo "directory does not exist.\n";
        echo "creating directory.\n";
        mkdir( dirname($long_flv), 0774, true );
    }

    if(strstr($item, ".avi") || strstr($item, ".wmv")) {
        $resolutestring = "ffmpeg -i " . $item . " -sameq -copyts -ar 44100 -vb 400000 -qmax 5 " . $long_flv;
        shell_exec($resolutestring);
//        $item = $name[0] . ".flv";
    }
    $resolutestring = "ffmpeg -i " . $long_flv . " -sameq -copyts -ar 44100 -vb 400000 -qmax 5 -vf \"movie=" .$cwd . "/watermark.png [watermark]; [in] [watermark] overlay=10:10 [out]\" " . $watermarked_flv;
    shell_exec($resolutestring);

    //should add the archival video to the database here
    $query = "Insert into archive_video (location, add_date, duration) values ('$item', NOW(), '" . $timetotalarray[0] . ":" . $timetotalarray[1] . ":" . $timetotalarray[2] . "." . $timetotalarray[3] . "')";

    if(!mysql_query($query)) {
        die("Query failed inserting archival video entry.\n\tHere's why: " . mysql_error());
    }
    $archive_id = mysql_insert_id();

    while ($start_time < $total_seconds) {
        if (($start_time + 180) > $total_seconds)  {
            $duration = ($total_seconds - $start_time);
        }

        $s_h = (int) ($start_time / 3600);
        $s_m = (int) (($start_time - ($s_h * 3600)) / 60);
        $s_s = (int) ($start_time - ($s_h * 3600) - ($s_m * 60));

        $d_h = 0;
        $d_m = (int) ($duration / 60);
        $d_s = (int) ($duration - ($d_m * 60));

        if ($s_h < 10) $s_h = "0" . $s_h;
        if ($s_m < 10) $s_m = "0" . $s_m;
        if ($s_s < 10) $s_s = "0" . $s_s;
        if ($d_h < 10) $d_h = "0" . $d_h;
        if ($d_m < 10) $d_m = "0" . $d_m;
        if ($d_s < 10) $d_s = "0" . $d_s;


        $outname = $filename . "_CHILD" . $iteration . ".flv";

        //Takes the video and splits it for the duration given by $start and $end
        $command = "ffmpeg -y -i " . $watermarked_flv . " -sameq -copyts -ar 44100 -ss " . $s_h . ":" . $s_m . ":" . $s_s . " -t " . $d_h . ":" . $d_m . ":" . $d_s . " " . $outname;

        echo $command . "\n";
        shell_exec($command);

        $command = "flvtool2 -UP " . $outname;
        echo $command . "\n";
        shell_exec($command);

        echo "created: " . $outname .  "\n";

        //add the streaming video to the database here
        $query = "Insert into streaming_video (archive_id, location, add_date, duration) values ($archive_id, '$outname', NOW(), '". $d_h . ":" . $d_m . ":" . $d_s . "')";

        if(!mysql_query($query)) {
            die("Query failed inserting streaming video entry.\n\tHere's why: " . mysql_error());
        }

        $start_time += 180;
        $iteration++;
    }

    echo "Removing: " . $long_flv . "\n";
    shell_exec("rm " . $long_flv);

    echo "Removing: " . $watermarked_flv . "\n";
    shell_exec("rm " . $watermarked_flv);
}


function search($loc) {
    global $current, $original, $videolist, $i, $finaliteration, $end;
    $check = getcwd();

    echo "check: " . $check . "\n";

    $list = shell_exec("ls");

    echo "result: " . $list . "\n";

    $list = explode("\n", $list);

    echo "loc: " . $loc . ", current: " . $current . "\n";

    if($loc == $current) {
        $end = count($list);
        $iterate = $finaliteration;
    } else {
        $iterate = 0;
    }

    while($list[$iterate]) {
        echo "list item: " . $list[$iterate] . "\n";

        if($loc == $current) {
            $finaliteration++;
        }

        if(is_dir($list[$iterate]) && $list[$iterate] != "FLVs" && $list[$iterate] != "Missouri_River_Project") {
            chdir($list[$iterate]);
            search($list[$iterate]);
        } else {
            if((strstr($list[$iterate], ".flv") || strstr($list[$iterate], ".avi") || strstr($list[$iterate], ".wmv")) && !strstr($list[$iterate], "filepart")) {
                $temp = getcwd() . "/" . $list[$iterate];
                $videolist[$i] = $temp;
                $i++;
            }
        }

        if($finaliteration == $end) {
            return 0;
        }
        $iterate++;
    }

    chdir("..");
    if($check == $original) {
        return 0;
    }
}

if(is_dir($item)) {
    $cwd = getcwd();

	if(!chdir($item)) {
		die("Unable to change to the specified directory.\n");
	}

    /**
     *  Get a recursive listing of every file in the specified directory
     */
    $current = getcwd();
	search( $current );

    echo "files to process:\n";
	for($f = 0; $f < count($videolist); $f++) {
		echo $videolist[$f] . "\n";
    }
    echo "\n\n";

	for($f = 0; $f < count($videolist); $f++) {
		echo $videolist[$f] . "\n";
		sequence($videolist[$f], $cwd);
    }

} else {
	$current = getcwd();
	if(strstr($item, ".flv") || strstr($item, ".avi") || strstr($item, ".wmv")) {
		sequence($item, $cwd);
	}
}

?>
