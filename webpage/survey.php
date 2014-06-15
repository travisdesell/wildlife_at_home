<?php

//git push origin master (sends changes to server proper)
//git commit -a [filename] ('saves' files)

//chdir("/projects/wildlife/html/user"); //Only for testing

require_once('/../../../home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/../../../home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/../../../home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/../../../home/tdesell/wildlife_at_home/webpage/my_query.php');
require_once('/../../../home/tdesell/wildlife_at_home/webpage/user.php');
require_once('/../../../home/tdesell/wildlife_at_home/webpage/boinc_db.php');
//require_once("../inc/bossa.inc");
//require_once("../inc/bossa_impl.inc");


//require_once('/projects/wildlife/html/inc/cache.inc');

require ('/../../../home/tdesell/wildlife_at_home/mustache.php/src/Mustache/Autoloader.php');
Mustache_Autoloader::register();

try
{
	$user = get_user();
	
}
catch(Exception $e)
{
	echo "Error: " . $e->getMessage();
}

$bootstrap_scripts = file_get_contents("/../../../home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html>
<head>
        <meta charset='utf-8'>
        <title>Wildlife@Home: Registration Survey</title>

        <link rel='alternate' type='application/rss+xml' title='Wildlife@Home RSS 2.0' href='/rss_main.php'>
        <link rel='icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
        <link rel='shortcut icon' href='wildlife_favicon_grouewjn3.png' type='image/x-icon'>
		
		<style type=\"text/css\">
			#mainbody {margin: 50px;}
			.pgmes {margin-top: -10px;}
		</style>

        $bootstrap_scripts

  
";
echo "</head><body>";
$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => 'active'
                );

print_navbar($active_items);

//if(is_special_user__fixme($user['id']) || intval($user['id']) == 197 || intval($user['id']) == 1)

//Testing echoing of registration questions

echo "<div id=\"mainbody\">";
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
