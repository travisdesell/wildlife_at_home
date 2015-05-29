<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");


//The function that calls everything
//-------------------------------[outputStats]-------------------------------
function outputStats($species, $nest)
{
	fetchEventsDay($species, $nest);
	fetchEventsDuration($species, $nest);
	fetchEventsTime($species, $nest);
}
//-------------------------------[/outputStats]-------------------------------

//-------------------------------[fetchEventsDay]-------------------------------
function fetchEventsDay($species, $nest) //Fetching and outputting the stats for events/day
{
	/*-----------------------------------------------
	DATA FETCHING
	-----------------------------------------------*/
	
	//This part: to get the data we need to visualize.
	$items = array();
	
	$string = "SELECT video_2.id, video_2.start_time, video_2.species_id FROM video_2 RIGHT OUTER JOIN expert_observations ON video_2.id = expert_observations.video_id WHERE expert_observations.event_type = 'parent behavior - not in frame' AND video_2.species_id=" . $species;
	
	//This code block exists because the Sharp-Tailed Grouse are the only species with multiple nesting sites
	if(!empty($nest) && $species == 1)
	{
		$string .= " AND video_2.location_id=" . $nest;
	}
	
	$string .= " ORDER BY video_2.start_time";
	$query = query_wildlife_video_db($string);
	
	echo mysql_error(); //Debug
	
	//Putting the days into "items"
	while($row = $query->fetch_array())
	{
		$day = explode(" ", $row['start_time']);
		
		$trigger = false;
		foreach($items as $date=>$count)
		{
			if($day[0] == $date)
			{
				$items[$date] = $count + 1;
				$trigger = true;
				break;
			}
		}
		
		if($trigger == false)
		{
			$items[$day[0]] = 1;
		}
	}
	
	/*-----------------------------------------------
	GRAPH CALCULATION
	-----------------------------------------------*/
	//Items is now full of data.
	asort($items, floatval("SORT_NATURAL"));
	
	//Assembling array of possible event numbers
	
	$numarray = array();
	$trigger = false;
	
	foreach($items as $date=>$count)
	{
		$trigger = false;
		foreach($numarray as $num=>$daycount) //Format is num of events=>number of days with that number of event (ex: "there are 5 days that had 1 event, three days that had eight events, etc"). 
		{
			if($count == $num)
			{
				$temp = $daycount + 1;
				$numarray[$num] = $temp;
				$trigger = true;
				break;
			}
		}
		
		if($trigger == false)
		{
			$numarray[strval($count)] = 1;
		}
	
	}
	
	//Find final item in numarray
	$iterator = count($numarray);
	
	foreach($numarray as $num=>$daycount)
	{
		$iterator--;
		
		if($iterator == 0)
		{
			$final = $num;
		}
	}
	
	//NOTE: Graph type is Column Chart!!
		
	//Adding zero columns to graphing data...
	
	for($i = 1; $i < $final; $i++)
	{
		if(!array_key_exists((string)$i, $numarray))
		{
			$numarray[(string)$i] = 0;
		}
	}
	
	//Items in array now sorted.
	ksort($numarray, floatval("SORT_NATURAL"));
	
	$basestring = array();
	
	//Final formatting before json conversion
	$temp = 0;
	foreach($numarray as $num=>$daycount)
	{
		$basestring[]=array((string)$num, $daycount);
	}
	
	$fun = json_encode($basestring);
	
	/*-----------------------------------------------
	MATH STUFF
	-----------------------------------------------*/
	asort($items, floatval("SORT_NATURAL"));
	
	$iteration = 0; //For the minimum, median, and maximum
	$total = 0; //For the mean
	$split = (intval(count($items) / 2)); //For the median, first quartile and third quartile
	$splitfirst = (intval($split / 2)); //Iteration for first quartile
	$splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
	
	foreach($items as $date=>$count)
	{
		if(empty($iteration))
		{
			$minimum = $count;
		}
		else if($iteration == $splitfirst)
		{
			$quartilefirst = $count;
		}
		else if($iteration == $split)
		{
			$median = $count;
		}
		else if($iteration == $splitthird)
		{
			$quartilethird = $count;
		}
		else if($iteration == (count($items) - 1))
		{
			$maximum = $count;
		}
		$total = $total + $count;
		$iteration++;
	}
	
	$mean = round($total / count($items), 2);
		//The following is for the calculation of the standard deviation
		
	$ongoing = 0; //The total of the squared numbers
	foreach($data as $date=>$count)
	{
		$temp = pow(($count - $mean), 2);
		$ongoing = $ongoing + $temp;
	}
	$standard = sqrt(($ongoing / count($items)));
	
	//Now calculating 95% confidence interval
	
	$conflow = ($mean - (1.96 * ($standard/sqrt(count($items))))); //Lower endpoint
	$confhigh = ($mean + (1.96 * ($standard/sqrt(count($items))))); //Higher endpoint
	
	/*-----------------------------------------------
	OUTPUT
	-----------------------------------------------*/
	
	//Correcting output for lack of information
	if(empty($minimum))
	{
		$minimum = "N/A";
	}
	if(empty($quartilefirst))
	{
		$quartilefirst = "N/A";
	}
	if(empty($median))
	{
		$median = "N/A";
	}
	if(empty($quartilethird))
	{
		$quartilethird = "N/A";
	}
	if(empty($maximum))
	{
		$maximum = "N/A";
	}
	
	
	//Now outputting everything
	echo "<div id=\"perdaystats\" class=\"well\">
	<div class=\"row-fluid\">
	<h3>Stats for Recess Events per Day</h3>
	<div class=\"datatable\" id=\"perdaydt\">
	<div id=\"perdaydtcol1\"></div>
	<div id=\"perdaydtcol2\"></div>
	<div id=\"perdaydata\">";
	
	echo "<h5>Minimum: " . $minimum . "</h5>
	<h5>First Quartile: " . $quartilefirst . "</h5>
	<h5>Median: " . $median . "</h5>
	<h5>Third Quartile: " . $quartilethird . "</h5>
	<h5>Maximum: " . $maximum . "</h5>
	<h5>Standard Deviation: " . round($standard, 2) . "</h5>
	<h5>Confidence Interval (95%) Low Endpoint: " . round($conflow, 2) . "</h5>
	<h5>Mean: " . $mean . "</h5>
	<h5>Confidence Interval (95%) High Endpoint: " . round($confhigh, 2) . "</h5>
	";
	
	echo "</div>";
	
	echo "<div id=\"perdaygraphcon\"><div id=\"perdaygraph\"></div></div>";
	
	echo "<script type=\"text/javascript\">numtimeschart(" . $fun . ", " . $species . ");</script>";
	
	/*-----------------------------------------------
	TABLE OUTPUT
	-----------------------------------------------*/
	echo "
	</div>
	<div class=\"tableoutputouter\">View Data as Table: <button id=\"but0\" onclick=\"showTable(0); return false;\">Show</button>
	<div id=\"tbloutputinner0\" style=\"display: none;\">
		Table of Raw Output:
		<table class=\"datatable\">";
		
	foreach($items as $date=>$count)
	{
		echo "<tr><td>" . $date . ": " . $count . "</td></tr>";
	}
	
	echo "</table>
	</div>
	</div>";
	
	echo "</div></div>";
	
}

//-------------------------------[fetchEventsDuration]-------------------------------
function fetchEventsDuration($species, $nest)
{
	/*-----------------------------------------------
	DATA FETCHING
	-----------------------------------------------*/
	
	$dura = array();
	
	$string = "SELECT exp.event_type, exp.start_time, exp.end_time FROM expert_observations AS exp LEFT OUTER JOIN video_2 AS v2 ON (v2.id = exp.video_id) WHERE exp.event_type = 'parent behavior - not in frame' AND v2.species_id = " . $species;
	
	if(!empty($nest) && $species == 1) //For grouse, having multiple nesting sites
	{
		$string .= " AND v2.location_id=" . $nest;
	}
	
	$query = query_wildlife_video_db($string);
	
	while($row = $query->fetch_array())
	{
		$tempstart = explode(":", $row['start_time']);
		$tempend = explode(":", $row['end_time']);
		
		//Actually calculating the duration here
		$durasec = $tempend[2] - $tempstart[2];
		$duramin = $tempend[1] - $tempstart[1];
		$durahour = $tempend[0] - $tempstart[0];
		
		if($durasec < 0)
		{
			$duramin = $duramin - 1;
			if($duramin < 0)
			{
				$durahour = $durahour - 1;
				if($durahour < 0)
				{
					$durahour = 0;
				}
			
				$duramin = 60 - abs($duramin);
			}
			$durasec = 60 - abs($durasec);
		}
		
		$durahrcon = $durahour * 60;
		//Duration is in format minutes-seconds
		
		//Formating...things.
		$duration = $durahrcon + $duramin . "-" . $durasec;
		
		$dura[count($dura)] = $duration;
		
		//I have no clue why this line is here in the original: $temp = explode("-", $duration);
	}
	
	/*-----------------------------------------------
	MATH AND TEXT OUTPUT
	-----------------------------------------------*/
	
	sort($dura, floatval("SORT_NATURAL"));
		
	$total = 0; //For the mean
	$split = (intval(count($dura) / 2)); //For the median, first quartile and third quartile
	$splitfirst = (intval($split / 2)); //Iteration for first quartile
	$splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
	
	//Calculating median, first quartile, and third quartile. Also converting to seconds and adding for mean
	for($i = 0; $i < count($dura); $i++)
	{
		if($i == 0)
		{
			$minimum = $dura[$i];
		}
		else if($i == $splitfirst)
		{
			$quartilefirst = $dura[$i];
		}
		else if($i == $split)
		{
			$median = $dura[$i];
		}
		else if($i == $splitthird)
		{
			$quartilethird = $dura[$i];
		}
		else if(!$data[$i + 1])
		{
			$maximum = $dura[$i];
		}
		
		//Converting to seconds and adding
		$temp = explode("-", $dura[$i]);
		$contosec = ($temp[0] * 60) + $temp[1];
		$total = $total + $contosec;
	}
	
	$minimum = explode("-", $minimum);
	$quartilefirst = explode("-", $quartilefirst);
	$median = explode("-", $median);
	$quartilethird = explode("-", $quartilethird);
	$maximum = explode("-", $maximum);
	$meanmin = intval(($total / count($dura))/ 60);
	$meansec = ($total / count($dura)) % 60;
	
	//Calculating standard deviation. Since $meanmin is an int, I'll be using that for consistency.
	
	//Converting to seconds
	$meanassec = ($meanmin * 60) + $meansec;
	
	$ongoing = 0; //Total of the squared numbers
	for($i = 0; $i < count($dura); $i++)
	{
		$datatemp = explode("-", $dura);
		$totaltemp = ($datatemp[0] * 60) + $datatemp[1];
		
		$temp = pow(($totaltemp - $meanassec), 2);
		$ongoing = $ongoing + $temp;
	}
	$standard = sqrt(($ongoing / count($dura))); //Still in seconds
	$standardmin = intval($standard / 60); //Convert to minutes
	$standardsec = $standard % 60; //Getting seconds
	
	//Calcualting 95% confidence interval. Will be in seconds
	
	$conflow = ($meanassec - (1.96 * ($standard/sqrt(count($dura))))); //Lower endpoint
	$confhigh = ($meanassec + (1.96 * ($standard/sqrt(count($dura))))); //Higher endpoint
	
	$conflowasmin = intval($conflow / 60);
	$conflowassec = $conflow % 60;
	
	$confhighasmin = intval($confhigh / 60);
	$confhighassec = $confhigh % 60;
	
	
	//Text output
	echo "<div id=\"durationstats\" class=\"well\">
	<div class=\"row-fluid\">
	<h3>Stats for Duration of Events</h3>
	<div class=\"datatable\" id=\"durationdt\">
	<div id=\"durationdtcol1\"></div>
	<div id=\"durationdtcol2\"></div>
	<div id=\"durationdata\">";
	
	echo "<h5>Minimum: " . $minimum[0] . "min " . $minimum[1] . "sec </h5>
	<h5>First Quartile: " . $quartilefirst[0] . "min " . $quartilefirst[1] . "sec </h5>
	<h5>Median: " . $median[0] . "min " . $median[1] . "sec </h5>
	<h5>Third Quartile: " . $quartilethird[0] . "min " . $quartilethird[1] . "sec </h5>
	<h5>Maximum: " . $maximum[0] . "min " . $maximum[1] . "sec </h5>
	<h5>Standard Deviation: " . $standardmin . "min " . $standardsec . "sec </h5>
	<h5>Confidence Interval (95%) Low Endpoint: " . $conflowasmin . "min " . $conflowassec . "sec</h5>
	<h5>Mean: " . $meanmin . "min " . $meansec . "sec </h5>
	<h5>Confidence Interval (95%) High Endpoint: " . $confhighasmin . "min " . $confhighassec . "sec</h5>
	
	</div>
	</div>
	</div>
	</div>";
}
//-------------------------------[/fetchEventsDuration]-------------------------------

//-------------------------------[fetchEventsTime]-------------------------------
function fetchEventsTime($species, $nest)
{
	/*-----------------------------------------------
	DATA FETCHING
	-----------------------------------------------*/
	
	$time = array();
	
	$string = "SELECT exp.event_type, exp.start_time FROM expert_observations AS exp LEFT OUTER JOIN video_2 AS v2 ON (v2.id = exp.video_id) WHERE exp.event_type = 'parent behavior - not in frame' AND v2.species_id = " . $species;
	
	if(!empty($nest) && $species == 1)
	{
		$string .= " AND v2.location_id=" . $nest;
	}
	
	$query = query_wildlife_video_db($string);
	
	
	while($row = $query->fetch_array())
	{
		$time[count($time)] = $row['start_time'];
	}
	
	/*-----------------------------------------------
	MATH AND TEXT OUTPUT
	-----------------------------------------------*/
	
	sort($time, floatval("SORT_NATURAL"));
	
	/*The following are for the mean*/
	$totalhr = 0;
	$totalmin = 0;
	$totalsec = 0;
	/*[/mean variables]*/
	
	$split = (intval(count($time) / 2)); //For the median, first quartile and third quartile
	$splitfirst = (intval($split / 2)); //Iteration for first quartile
	$splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
	
	for($i = 0; $i < count($time); $i++)
	{
		if($i == 0)
		{
			$minimum = $time[$i];
		}
		else if($i == $splitfirst)
		{
			$quartilefirst = $time[$i];
		}
		else if($i == $split)
		{
			$median = $time[$i];
		}
		else if($i == $splitthird)
		{
			$quartilethird = $time[$i];
		}
		else if(!$data[$i + 1])
		{
			$maximum = $time[$i];
		}
		
		//Splitting time at semicolons and adding
		$temp = explode(":", $time[$i]);
		$totalhr = intval($temp[0]) + $totalhr;
		$totalmin = intval($temp[1]) + $totalmin;
		$totalsec = intval($temp[2]) + $totalsec;
	}
	
	//Calculating mean
	$meanhr = intval($totalhr / count($time));
	$meanmin = intval($totalmin / count($time));
	$meansec = intval($totalsec / count($time));
	
	//Calculating standard deviation
	$ongoinghr = 0;
	$ongoingmin = 0;
	$ongoingsec = 0;
	
	for($i = 0; $i < count($time); $i++)
	{
		$temp = explode(":", $time[$i]);
		
		$ongoinghr = intval(pow(($temp[0] - $meanhr), 2)) + $ongoinghr;
		$ongoingmin = intval(pow(($temp[1] - $meanmin), 2)) + $ongoingmin;
		$ongoingsec = intval(pow(($temp[2] - $meansec), 2)) + $ongoingsec;
	}
	
	$stdhr = intval(sqrt(($ongoinghr / count($time))));
	$stdmin = intval(sqrt(($ongoingmin / count($time))));
	$stdsec = intval(sqrt(($ongoingsec / count($time))));
	
	//Calculating 95% confidence interval
	//Low endpoint
	$conflowhr = intval($meanhr - (1.96 * ($stdhr/sqrt(count($time)))));
	$conflowmin = intval($meanmin - (1.96 * ($stdmin/sqrt(count($time)))));
	$conflowsec = intval($meansec - (1.96 * ($stdsec/sqrt(count($time)))));
	
	//High endpoint
	$confhighhr = intval($meanhr + (1.96 * ($stdhr/sqrt(count($time)))));
	$confhighmin = intval($meanmin + (1.96 * ($stdmin/sqrt(count($time)))));
	$confhighsec = intval($meansec + (1.96 * ($stdsec/sqrt(count($time)))));
	
	/*-----------------------------------------------
	OUTPUT
	-----------------------------------------------*/
	echo "<div id=\"eventstimestats\" class=\"well\">
	<div class=\"row-fluid\">
	<h3>Stats for Events Time of Day</h3>
	<div class=\"datatable\" id=\"eventstimedt\">
	<div id=\"eventstimedtcol1\"></div>
	<div id=\"eventstimedtcol2\"></div>
	<div id=\"eventstimedata\">";
	
	echo "<h5>Minimum: " . $minimum . " </h5>
	<h5>First Quartile: " . $quartilefirst . " </h5>
	<h5>Median: " . $median . " </h5>
	<h5>Third Quartile: " . $quartilethird . " </h5>
	<h5>Maximum: " . $maximum . " </h5>
	<h5>Standard Deviation: " . $stdhr . ":" . $stdmin . ":" . $stdsec . " </h5>
	<h5>Confidence Interval (95%) Low Endpoint: " . $conflowhr . ":" . $conflowmin . ":" . $conflowsec . " </h5>
	<h5>Mean: " . $meanhr . ":" . $meanmin . ":" . $meansec . " </h5>
	<h5>Confidence Interval (95%) High Endpoint: " . $confhighhr . ":" . $confhighmin . ":" . $confhighsec . "</h5>
	</div>
	</div>
	</div>
	";
}
//-------------------------------[/fetchEventsTime]-------------------------------

//-------------------------------[AJAX]-------------------------------
$switch = $_POST['action'];
$species = $_POST['species'];
$nest = $_POST['nestsite'];

if(empty($species))
{
	$species = 1;
}

if(!empty($switch) && $switch == "goforit")
{
	outputStats($species, $nest);
}
//-------------------------------[/AJAX]-------------------------------

?>
