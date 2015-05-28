<?php


$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

//Registration survey stuff

function outputRegis()
{
	global $connection;
	$date = date("m-d-y h:i:s:a");
	echo "<h3>Registration Survey Results as of " . $date . "</h3>";
	
	echo "<table id=\"regisout\">";
	echo "<tr class=\"rowhead\">
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Entry ID</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">User ID</td>
	<td class=\"rowheadcell\" style=\"max-width: 150px;\">Country</td>
	<td class=\"rowheadcell\" style=\"min-width:50px;\">Age</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Sex</td>
	<td class=\"rowheadcell\" style=\"min-width: 100px;\">Education</td>
	<td class=\"rowheadcell\">Profession</td>
	<td class=\"rowheadcell\">Profession Elaboration</td>
	<td class=\"rowheadcell\">English</td>
	<td class=\"rowheadcell\">English Fluency</td>
	<td class=\"rowheadcell\">City Population</td>
	<td class=\"rowheadcell\" style=\"min-width: 200px;\">How did you hear about wildlife@home?</td>
	<td class=\"rowheadcell\" style=\"min-width: 300px;\">What made you decide to join?</td>
	<td class=\"rowheadcell\">How do you plan on participating?</td>
	<td class=\"rowheadcell\" style=\"min-width: 150px\">Do you participate in any of the following activities?</td>
	</tr>";

//Get data
$query = "SELECT * FROM registration ORDER BY id ASC";
$result = query_wildlife_video_db($query);

$evebit = 0;

while($row = $result->fetch_array())
{
	
	if($evebit == 0)
	{
		echo "<tr>";
		$evebit++;
	}
	else
	{
		echo "<tr class=\"roweven\">";
		$evebit = 0;
	}
	
	$user = csg_get_user_from_id($row['u_id']);
	
	echo "<td class=\"rownormal\">" . $row['id'] . "</td>
	<td class=\"rownormal\">". $row['u_id'] . "</td>
	<td class=\"rownormal\">" . $user['country'] . "</td>
	<td class=\"rownormal\">" . $row['age'] . "</td>
	<td class=\"rownormal\">" . $row['sex'] . "</td>
	<td class=\"rownormal\">" . $row['education'] . "</td>
	<td class=\"rownormal\">";
	
	$temp = explode(";", $row['profession']);
	echo $temp[0];
	echo "</td>
	<td class=\"rownormal\">";
	
	if(empty($temp[1]))
	{
		echo "--";
	}
	else
	{
		echo $temp[1];
	}
	 
	echo "</td>
	<td class=\"rownormal\">" . $row['english'] . "</td>
	<td class=\"rownormal\">" . $row['eng_fluent'] . "</td>
	<td class=\"rownormal\">" . $row['population'] . "</td>
	<td class=\"rownormal\">" . str_replace("|", "<br />", $row['heard']) . "</td>
	<td class=\"rownormal\">" . str_replace("|", "<br />", $row['joined']) . "</td>
	<td class=\"rownormal\">" . $row['participation'] . "</td>
	<td class=\"rownormal\">" . str_replace("|", "<br />", $row['activities']) . "</td>";
	echo "</tr>";
}

echo "</table>";


}

function outputGold()
{
	global $connection;
	
	$date = date("m-d-y h:i:s:a");
	echo "<h3>Gold Badge Survey Results as of " . $date . "</h3>";
	
	echo "<table id=\"goldout\">";
	echo "<tr class=\"rowhead\">
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Entry ID</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">User ID</td>
	<td class=\"rowheadcell\" style=\"max-width: 150px;\">Country</td>
	<td class=\"rowheadcell\">How do you Participate?</td>
	<td class=\"rowheadcell\" style=\"min-width: 200px;\">Species Ranking</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Hrs of Video per Week</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Hrs of Video in Sitting</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Video Speed</td>
	<td class=\"rowheadcell\" style=\"max-width: 75px;\">Primary Web Browser Used</td>
	<td class=\"rowheadcell\" style=\"max-width: 75px;\">Are you Doing Other Activities While Watching?</td>
	<td class=\"rowheadcell\" style=\"max-width: 50px;\">Do You Find This Project Interesting?</td>
	<td class=\"rowheadcell\" style=\"min-width: 200px; max-height: 15px;\">If so, Why?</td>
	<td class=\"rowheadcell\" style=\"min-width: 100px;\">What New Things Have You Learned?</td>
	<td class=\"rowheadcell\" style=\"min-width: 150px;\">Briefly Explain Your User Experience.</td>
	<td class=\"rowheadcell\" style=\"min-width: 200px;\">Has This Project Motivated You To...</td>
	<td class=\"rowheadcell\" style=\"min-width: 100px;\">What Other Species Would You Like to See?</td>
	<td class=\"rowheadcell\" style=\"min-width: 50px;\">Would you recommend this project to others?</td>
	</tr>";
	
	$query = "SELECT * FROM goldbadge ORDER BY id ASC";
	$result = query_wildlife_video_db($query);
	
	$evebit = 0;
	
	while($row = $result->fetch_array())
	{
		if($evebit == 0)
		{
			echo "<tr>";
			$evebit++;
		}
		else
		{
			echo "<tr class=\"roweven\">";
			$evebit = 0;
		}
		
		$user = csg_get_user_from_id($row['u_id']);
		
		echo "<td class=\"rownormal\">" . $row['id'] . "</td>
		<td class=\"rownormal\">" . $row['u_id'] . "</td>
		<td class=\"rownormal\">" . $user['country'] . "</td>
		<td class=\"rownormal\">" . $row['gold_participation'] . "</td>
		<td class=\"rownormal\">" . $row['species_rank'] . "</td>
		<td class=\"rownormal\">" . str_replace("-", " to ", $row['hours_week']) . "</td>
		<td class=\"rownormal\">" . str_replace("-", " to ", $row['hours_sitting']) . "</td>
		<td class=\"rownormal\">" . $row['video_speed'] . "</td>
		<td class=\"rownormal\">" . $row['browser'] . "</td>
		<td class=\"rownormal\">" . $row['other_activities'] . "</td>
		<td class=\"rownormal\">" . $row['interesting'] . "</td>
		<td class=\"rownormal\">" . str_replace("|", "<br />", $row['int_elaboration']) . "</td>
		<td class=\"rownormal\">" . $row['learned'] . "</td>
		<td class=\"rownormal\">" . $row['experience'] . "</td>
		<td class=\"rownormal\">" . str_replace("|", "<br />", $row['motivation']) . "</td>
		<td class=\"rownormal\">" . $row['other_species'] . "</td>
		<td class=\"rownormal\">" . $row['recommendation'] . "</td>
		</tr>";
		//$basestring .= $row['id'] . ", " . $row['u_id'] . ", " . $row['gold_participation'] . ", " . $row['species_rank'] . ", " . $row['hours_week'] . ", " . $row['hours_sitting'] . ", " . $row['video_speed'] . ", " . $row['browser'] . ", " . $row['other_activities'] . ", " . $row['interesting'] . ", " . $row['int_elaboration'] . ", " . $row['learned'] . ", " . $row['experience'] . ", " . $row['motivation'] . ", " . $row['other_species'] . ", " . $row['recommendation'] . "\n";
	}
	
	echo "</table>";
}

//Gold Survey stuff
//Creating file for this runthrough

/*$date = date("m-d-y-h-i-s-a");
$temp = "goldsur-" . $date . ".csv";

$basestring = "Entry ID, User ID, How do you participate?, Please rank the species below in terms of which are your favorite to watch (1 = favorite, 3 = least favorite) and why, How many hours on average do you spend watching video per week?, How many hours on average do you spend watching video in one sitting?, What is the speed (on average) that you watch video?, Which browser do you primarily use to watch video?, Are you doing other activities, such as watching television or surfing the internet, while watching video?, Do you find this project interesting?, If you find this project interesting[c] why?, Briefly describe if you have learned anything new from this project., Briefly explain how we can improve your user experience., Has this project motivated you to..., What other species (birds, mammals, amphibians, reptiles etc) would you like to see added to the website?, Would you recommend this project to others?\n";

//Get data
$query = "SELECT * FROM goldbadge ORDER BY id ASC";
$result = mysql_query($query, $connection);

while($row = mysql_fetch_array($result))
{
	$row = commaReplacement($row, "gold");
	$basestring .= $row['id'] . ", " . $row['u_id'] . ", " . $row['gold_participation'] . ", " . $row['species_rank'] . ", " . $row['hours_week'] . ", " . $row['hours_sitting'] . ", " . $row['video_speed'] . ", " . $row['browser'] . ", " . $row['other_activities'] . ", " . $row['interesting'] . ", " . $row['int_elaboration'] . ", " . $row['learned'] . ", " . $row['experience'] . ", " . $row['motivation'] . ", " . $row['other_species'] . ", " . $row['recommendation'] . "\n";
}

file_put_contents($temp, $basestring);*/


function commaReplacement($row, $mode) //Replacing commas, but also the replaced quotes for easier readability. I hope you like nested str_replaces
{
	if($mode == "regis")
	{
		$row['id'] = str_replace($replace, $original, str_replace(",", "[c]", $row['id']));
		$row['u_id'] = str_replace($replace, $original, str_replace(",", "[c]", $row['u_id']));
		$row['age'] = str_replace($replace, $original, str_replace(",", "[c]", $row['age']));
		$row['sex'] = str_replace($replace, $original, str_replace(",", "[c]", $row['sex']));
		$row['education'] = str_replace($replace, $original, str_replace(",", "[c]", $row['education']));
		$row['profession'] = str_replace($replace, $original, str_replace(",", "[c]", $row['profession']));
		$row['english'] = str_replace($replace, $original, str_replace(",", "[c]", $row['english']));
		$row['eng_fluent'] = str_replace($replace, $original, str_replace(",", "[c]", $row['eng_fluent']));
		$row['population'] = str_replace($replace, $original, str_replace(",", "[c]", $row['population']));
		$row['heard'] = str_replace($replace, $original, str_replace(",", "[c]", $row['heard']));
		$row['joined'] = str_replace($replace, $original, str_replace(",", "[c]", $row['joined']));
		$row['participation'] = str_replace($replace, $original, str_replace(",", "[c]", $row['participation']));
		$row['activities'] = str_replace($replace, $original, str_replace(",", "[c]", $row['activities']));
	}
	else if($mode == "gold")
	{
		$row['id'] = str_replace($replace, $original, str_replace(",", "[c]", $row['id']));
		$row['u_id'] = str_replace($replace, $original, str_replace(",", "[c]", $row['u_id']));
		$row['gold_participation'] = str_replace($replace, $original, str_replace(",", "[c]", $row['gold_participation']));
		$row['species_rank'] = str_replace($replace, $original, str_replace(",", "[c]", $row['species_rank']));
		$row['hours_week'] = str_replace($replace, $original, str_replace(",", "[c]", $row['hours_week']));
		$row['hours_sitting'] = str_replace($replace, $original, str_replace(",", "[c]", $row['hours_sitting']));
		$row['video_speed'] = str_replace($replace, $original, str_replace(",", "[c]", $row['video_speed']));
		$row['browser'] = str_replace($replace, $original, str_replace(",", "[c]", $row['browser']));
		$row['other_activities'] = str_replace($replace, $original, str_replace(",", "[c]", $row['other_activities']));
		$row['interesting'] = str_replace($replace, $original, str_replace(",", "[c]", $row['interesting']));
		$row['int_elaboration'] = str_replace($replace, $original, str_replace(",", "[c]", $row['int_elaboration']));
		$row['learned'] = str_replace($replace, $original, str_replace(",", "[c]", $row['learned']));
		$row['experience'] = str_replace($replace, $original, str_replace(",", "[c]", $row['experience']));
		$row['motivation'] = str_replace($replace, $original, str_replace("\n", "[c]", $row['motivation']));
		$row['other_species'] = str_replace($replace, $original, str_replace(",", "[c]", $row['other_species']));
		$row['recommendation'] = str_replace($replace, $original, str_replace(",", "[c]", $row['recommendation']));
	}
	
	return $row;
}

?>
