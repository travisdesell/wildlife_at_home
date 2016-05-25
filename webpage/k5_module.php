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

$css_header = "<link rel='stylesheet' href='http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.css'>";
$js_header = "<script src='http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js></script>";
$js_maps = "<script src= blahblahblah></script>";
print_header("Wildlife@Home: Teaching & Learning", "$css_header", "$js_header", "wildlife");
print_navbar("Projects: Wildlife@Home", "Wildlife@Home", "..");

echo"

<div id="mapid"></div>

";
//videos from BEL_2012
/*$wells = array();

$wells['well'][] = array('video' => "clips_grouse/BEL_2012/148.954_48.11126_102.440147", 
		"list" => array(
			array("item1" => "first", "item2" => "second"),
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.085_48.06332_102.44829", 
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.495_48.05411_102.4242", 
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.723_48.12235_102.38943", 
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.772_48.11158_102.44098", 
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.772_48.11158_102.44098",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.783_48.12148_102.39020",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.801_48.07501_102.3787",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.823_48.0599_102.39538",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/149.892_48.12108_102.39192",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2012/150.462_48.10577_102.43574",
		"list" => array(
			array("item1" => "third", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/148.203_48.13689_102.38178",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/148.222_48.11228_102.44405",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.052_48.13511_102.38803",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.554_48.11663_102.47623.",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.573_48.1223_102.3886",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.573_48.12436_102.38857",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.744_48.145075_102.39126",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.783_48.12209_102.38947",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.793_48.14372_102.38706",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.801_48.075062_102.380261",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/149.823_48.13639_102.3874",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/150.242_48.11884_102.38034",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/150.261_48.11042_102.44061",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/150.342_48.10705_102.41093",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/150.432_48.10356_102.430624",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/151.564_48.1238_102.39722",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/151.644_48.13074_102.44943",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BEL_2013/151.675_48.10591_102.44745",
		"list" => array(
			array("item1" => "BEL_2013", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/148.574_48.27494_102.05381",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/148.574_48.27494_102.05381",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/149.514_48.27065_102.05257",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/149.523_48.30938_102.13190",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/149.704_48.31793_102.21283",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/149.744_48.31460_102.13560",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/149.851_48.27302_102.07603",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.011_48.26365_102.10049",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.022_48.30297_102.13860",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.101_48.29605_102.20289",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.121_48.31679_102.21333",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.183_48.31303_102.19262",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.202_48.29144_102.14163",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.232_48.29914_102.13702",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.261_48.28890_102.13067",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.441_48.29417_102.08427",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.592_48.31908_102.20883",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.742_48.29950_102.12988",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.761_48.27290_102.06213",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/150.781_48.30264_102.13810",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/151.564_48.27240_102.05879",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);
$wells['well'][] = array('video' => "clips_grouse/BLA_2012/151.664_48.27504_102.06100",
		"list" => array(
			array("item1" => "BLA_2012", "item2" => "fourth"),
			array("item1" => "third", "item2" => "fifth"),
			array("item1" => "third", "item2" => "sixth")
		)
	);

$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/148.203_48.13689_102.38178",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/148.222_48.11228_102.44405",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.052_48.13511_102.38803",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.554_48.11663_102.47623",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.573_48.1223_102.3886",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.573_48.12436_102.38857",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.744_48.145075_102.39126",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.783_48.12209_102.38947",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.793_48.14372_102.38706",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/ 149.801_48.075062_102.380261",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/149.823_48.13639_102.3874",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/150.242_48.11884_102.38034",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/150.261_48.11042_102.44061",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/150.342_48.10705_102.41093",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/150.432_48.10356_102.430624",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/151.564_48.1238_102.39722",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/151.644_48.13074_102.44943",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/BEL_2013/151.675_48.10591_102.44745",
		"list" => array(
			array("item1" => "BLA_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);

$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/148.043_48.52878_102.49752",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/148.473_48.65523_102.43999",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/49.503_48.65188_102.43567",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/149.554_48.52755_102.49201",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/149.564_48.61229_102.43444",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/149.654_48.64902_102.44421",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/150.512_48.53613_102.42688",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/150.622_48.52777_102.45663",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/150.832_48.52606_102.47702",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/151.632_48.65865_102.42151",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
$wells['well'][] = array('video'=>"clips_grouse/LOST_2013/151.683_48.52316_102.47265",
		"list" => array(
			array("item1" => "LOST_2013", "item2" => "test"),
			array("item1" => "next", "item2" => "test2")
		)
	);
*/

$k5 = file_get_contents($cwd[__file__] . "/templates/k5.html");

$m = new mustache_engine;
echo $m->render($k5, $wells);	
echo $m->render($k5, $wells);
print_footer('travis desell, susan ellis-felege, lindsey wingate and the wildlife@home team', 'travis desell, susan ellis-felege');

echo "</body></html>";

?>
