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


$css_header = "<link rel='stylesheet' type='text/css' href='wildlife_css/education_style.css'/>";

print_header("Wildlife@Home: Teaching & Learning", "$css_header", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo"
<div class='container'>
	<div class='row'>
		<div class='col-sm-6'>
			<div class='well well-sm'>
				<video width=100% height=40% id='wildlife_video' controls='controls'>
					<source src='clips_grouse/BEL_2012/148.954_48.11126_102.440147.mp4' type='video/mp4'>
					<source src='clips/grouse/BEL_2012/148.954_48.11126_102.440147.ogv' type='video/ogv'>
					<source src='clips/grouse/BEL_2012/148.954_48.11126_102.440147.wmv' type='video/wmv'>
				</video>
			</div>
			<div class='well well-sm'>	
				<video width=100% height=40% id='wildlife_video2' controls='controls'>
					<source src='clips_grouse/BEL_2012/149.085_48.06332_102.44829.mp4' type='video/mp4'>
					<source src='clips_grouse/BEL_2012/149.085_48.06332_102.44829.ogv' type='video/ogv'>
					<source src='clips/grouse/BEL_2012/149.085_48.06332_102.44829.wmv' type='video/wmv'>
				</video>
			</div>
		</div>
	</div>
</div>
";
/*class videos {
	public $page_intro = "templates nao funcionam muito bem pra mim";
	public $video = "clips_grouse/BEL_2012/148.954_48.11126_102.440147.mp4";
}

$k5 = file_get_contents($cwd[__FILE__] . "/templates/k5.html");

$m = new Mustache_Engine;
$videos = new videos;
echo $m->render($k5, $videos);	
*/
print_footer('Travis Desell, Susan Ellis-Felege, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";

?>
