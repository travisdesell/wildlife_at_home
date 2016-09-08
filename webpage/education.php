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

echo "
<div class='container'>
	<div class='row' id='test_for_font'>
		<div class='col-sm-12'><!--column allows all rows to match up in size--> 
			<p> </p>
			<p>  </p>
			<h1 id='title1'><center>Wildlife at Home - Education Modules</center></h1>
		</div>
	</div>
<div class='row row-centered'>
	<div class='col-sm-12'>	
		<p align='center'>
			<h3 id='title'><center>Sharp-Tailed Grouse</center></h3>	
		</p>			
	</div>
</div>
<div class='row row-centered'>
	<div class='col-sm-4'>
		<div class='well well-sm' id='well1'>
			<h3 id='subtitle'><center>K-5 Module
			</center></h3>
				<p></p>
				<img src='images/900x293_susan.jpg'>
			<p></p>
			<p> The UND Wildlife at Home Education Elementary School Module \"Impacts of Gas and Oil on Nest Success of Sharp-tailed Grouse in Western North Dakota\" was developed to address K LS1-1, ESS 3-1, ESS 3-3, 3 LS1-1LS2-1 LS 3-2 ESS 2-1, 4 ESS 3-2, 5 LS 2-1 ESS 3-1.  Organisms can be related using food webs. Some animals eat plants for food and other animals eat the animals that eat plants. Organisms can survive only in environments in which their particular needs are met. In this module students will examine the impacts of human activity (gas and oil development) on the Sharp-tailed grouse in western North Dakota. A healthy ecosystem is one way multiple species of different types are able to meet their needs in a relatively stable web of life. Newly introduced species can damage the balance of an ecosystem. (5-LS2-1 LS2.B). </p>				
		<center><!--centers buttons-->
		<a href='publications/Grade-5-Food-Web-Module.docx'>
			<button type='button' class='btn btn-success btn-sm'>
				Lesson Plan	
			</button>
		</a>
		<a href='publications/video_key.pdf'>
			<button type='button' class='btn btn-success btn-sm'>
				Video Key
			</button>
		</a>
		<a href='http://csgrid.org/csg/wildlife_lwingate/k5_module.php'>
			<button type='button' class='btn btn-success btn-sm'>
				Student Resources
			</button>
		</a>
		</center>
		</div><!--well-->
	</div><!--col-->
	<div class='col-sm-4'>
		<div class='well well-sm' id='well2'>
			<h3 id='subtitle'><center>6-9 Module
			</center></h3>
				<p></p>
					<img src='images/900x293_hatch.jpg'>
				<p></p>
				<p>The Wildlife at Home Education Middle School Module \"Impacts of Gas and Oil on Nest Success of Sharp-tailed Grouse in Western North Dakota\" was developed to address MS-LS2-4 and LS2.A. Students construct an argument supported by empirical evidence that relates to the physical or biological components of an ecosystem that affects populations from three study sites in Western North Dakota.  These three sites represent areas of high, medium and low intensity development of gas and oil extraction.  Students use real data collected as part of a study by UND, North Dakota Game and Fish, and Brigham Young University to identify predators captured on camera. Then, they classify their data and use it to calculate nest success rates, predation rates and types, and chick survival to make inferences about the effects of gas and oil development on the population of Sharp-tailed Grouse in Western North Dakota.  MS-LS1-6 and MS-LS1-8 are also supported because the theme of obtaining, analyzing, and communicating empirical evidence is emphasized throughout the module.</p>
		<center>
		<a href='publications/MS-Module.docx'>
			<button type='button' class='btn btn-success btn-sm'>
				Lesson Plan
			</button>
		</a>
		<button type='button' class='btn btn-success btn-sm'>
			Student Resources
		</button>	
		</center>
		</div><!--well-->
		</div><!--col-->
	<div class='col-sm-4'>
		<div class='well well-sm' id='well3'>
			<h3 id='subtitle'><center>10-12 Module 
			</center></h3>
				<p></p>
			<img src='images/900x276_oil.jpg'>
				<p></p>
				<p>The UND Wildlife at Home Education High School Module \"Impacts of Gas and Oil on Nest Success of Sharp-tailed Grouse in Western North Dakota\" was developed to address HS-LS2-2 and LS2.A. Students first construct an argument supported by empirical evidence related to the physical or biological components of an ecosystem that affect populations from three study sites in Western North Dakota. These three sites represent areas of high, medium and low intensity development of gas and oil extraction. Students use real data collected as part of a study by UND, North Dakota Game and Fish, and Brigham Young University to identify predators captured on camera. Then, they classify their data and use it to calculate nest success rates, predation rates and types, and chick survival to make inferences about the effects of gas and oil development on the population of Sharp-tailed Grouse in Western North Dakota.  HS-LS2-6 and HS-LS2-8 are also supported because the theme of obtaining, analyzing, and communicating empirical evidence is emphasized throughout the module.</p>
		<center>
		<a href='publications/HS-Module.docx'>
			<button type='button' class='btn btn-success btn-sm'>
				Lesson Plan
			</button>
		</a>
		<button type='button' class='btn btn-success btn-sm'>
			Student Resources
		</button>
		</center>
		</div><!--well-->
	</div><!--col-->
</div><!--row-->
<div class='row'>
	<div class='col-sm-12'>
		<div class='well well-sm'>
			<p>If you have any suggestions or questions about the modules, feel free to drop by the <a href=\"http://volunteer.cs.und.edu/csg/forum_forum.php?id=3\">Education and Learning Forum</a>.</p>
		</div>
		</div><!--col-->		
	</div><!--row-->
</div><!--container-->
";


print_footer('Travis Desell, Susan Ellis-Felege, Lindsey Wingate and the Wildlife@Home Team', 'Travis Desell, Susan Ellis-Felege');

echo "</body></html>";

?>
