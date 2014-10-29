<?php

//git push origin master (sends changes to server proper)
//git commit -a [filename] ('saves' files)

//chdir("/projects/wildlife/html/user"); //Only for testing

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/user.php");

print_header("Wildlife@Home", "", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

//require ('/../../../home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php');
//Mustache_Autoloader::register();

try
{
	$user = csg_get_user();
	$is_special = csg_is_special_user($user, true);
	
}
catch(Exception $e)
{
	echo "Error: " . $e->getMessage();
}


echo "
<style type=\"text/css\">
	.rowheadcell {font-size: 10px; padding: 5px; border: 1px solid #000000; background-color: #e7e7e7; max-height: 15px; text-align: center;}
	.roweven td {background-color: #e7e7e7; text-align: center;}
		.rownormal {font-size: 12px; padding: 5px; text-align: center;}
</style>";

if(!empty($user) && $is_special)
{
	echo "
		<div id=\"mainbody\" style=\"margin: 20px;\">
		<h2>Survey Results</h2>
		<div class=\"pgmes\">These are the results for the surveys, both registration and gold.</div>";
		require("suroutend.php");
		outputRegis();
		outputGold();
	
	echo "</div>";
}
else
{
	echo "You don't seem to be logged in or have the appropriate privileges to view this page.";
}
echo "</body></html>";
?>
