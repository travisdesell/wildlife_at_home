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


//$css_header = "<link rel='stylesheet' type='text/css' href='wildlife_css/education_style.css'/>";

print_header("Wildlife@Home: Teaching & Learning", "$css_header", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo "
<div class='container'>
	<div class='row'>
		<h1><center>North Dakota Flora and Fauna</center></h1>
	</div><!--row-->
	<div class='row'>
		<div class='col-sm-6'>
			<div class='well'>
				<p>This is a well!</p>
			</div><!--well-->
		</div><!--col-sm-6-->
		<div class='col-sm-6'>
			<div class='well'>
				<p>This is another well!</p>
			</div><!--well-->
		</div><!--col-->
		<div class='col-sm-12'>
			<div class='well'>
				The third well.
			</div><!--well-->
			<center><button type='button' class='btn btn-default'>
				Button
			</button></center>	
		</div><!--col-->
	</div><!--row-->
</div>
";

print_footer('Travis Desell, Susan Ellis-Felege, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

?>
