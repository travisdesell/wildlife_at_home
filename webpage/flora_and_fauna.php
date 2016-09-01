<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);

require_once($cwd[__FILE__] . "/../../citizen_science_grid/header.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/navbar.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/news.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/footer.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");
require_once($cwd[__FILE__] . "/../../citizen_science_grid/csg_uotd.php");


$css_header = "<link rel='stylesheet' type='text/css' href='wildlife_css/flora_and_fauna.css'/>";

print_header("Wildlife@Home: Teaching & Learning", "$css_header", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<!DOCTYPE html>
<html lang='en'>
<head>
	<title>Bootstrap Theme Company</title>
	<meta charset='utf-8'>
	<meta name='viewport' content='width=device-width, initial-scale=1'>
</head>
<body>
	<div class='jumbotron'>
		<center>
  		<h1>Flora and Fauna</h1> 
 	 		<p>Bringing wildlife to you.</p> 
			<form class='form-inline'>
    <input type='email' class='form-control' size='50' placeholder='Search'>
    <button type='button' class='btn btn-danger'>Go</button>
  </form>
		</center>
	</div><!--jumbotron-->
<div class='container-fluid'>
  <div class='row'>
    <div class='col-sm-8'>
      <h2>About the Photographers</h2>
      <h4>Very important things..</h4> 
      <p>Lorem ipsum..</p>
      <button class='btn btn-default btn-lg'>Get in Touch</button>
    </div>
    <div class='col-sm-4'>
      <span class='glyphicon glyphicon-camera logo'></span>
    </div>
  </div>
</div>

<div class='container-fluid bg-grey'>
  <div class='row'>
    <div class='col-sm-4'>
      <span class='glyphicon glyphicon-globe logo'></span> 
    </div>
    <div class='col-sm-8'>
      <h2>More Info?</h2>
      <h4><strong>MISSION:</strong> Our mission lorem ipsum..</h4> 
      <p><strong>VISION:</strong> Our vision Lorem ipsum..</p>
    </div>
  </div>
</div>
</body>
</html>
";

print_footer('Travis Desell, Susan Ellis-Felege, Cheyenne Letourneau, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

?>
