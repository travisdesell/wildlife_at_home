<?php

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
	
}
catch(Exception $e)
{
	echo "Error: " . $e->getMessage();
}

//if(is_special_user__fixme($user['id']) || intval($user['id']) == 197 || intval($user['id']) == 1)

//Testing echoing of registration questions

echo "<div id=\"mainbody\" style=\"margin: 20px;\">";
echo "<h2>Surveys</h2>
<div class=\"pgmes\">These are the surveys that can be taken for this site.</div>";

if(!empty($user))
{
	require("surback.php");
	registration();
	goldsurvey();
}
else
{
	echo "You don't seem to be logged in.";
}

echo "</div>";
echo "</body></html>";
?>
